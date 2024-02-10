<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Doctrine\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Common\Proxy\Proxy;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Psr\Cache\InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Umanit\SeoBundle\Entity\UrlReference;
use Umanit\SeoBundle\Handler\Routable\Routable;
use Umanit\SeoBundle\Model\HistorizableUrlModelInterface;
use Umanit\SeoBundle\Model\RoutableModelInterface;
use Umanit\SeoBundle\Routing\Canonical;
use Umanit\SeoBundle\UrlHistory\UrlPoolerInterface;

#[AsDoctrineListener(Events::loadClassMetadata)]
#[AsDoctrineListener(Events::preUpdate)]
#[AsDoctrineListener(Events::onFlush)]
#[AsDoctrineListener(Events::prePersist)]
class UrlHistoryWriter
{
    public const ENTITY_DEPENDENCY_CACHE_KEY = 'seo.entity_dependencies';

    public function __construct(
        private readonly UrlPoolerInterface $urlPooler,
        private readonly Routable $routableHandler,
        private readonly Canonical $canonical,
        private readonly CacheInterface $cache,
        private readonly string $defaultLocale,
    ) {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        $reflectionEntity = $args->getClassMetadata()->getReflectionClass();

        if (
            null === $reflectionEntity ||
            $reflectionEntity->isAbstract() ||
            !$reflectionEntity->implementsInterface(RoutableModelInterface::class)
        ) {
            return;
        }

        // Loops through each entity associations
        foreach ($args->getClassMetadata()->getAssociationMappings() as $mappingData) {
            if (!\array_key_exists('targetEntity', $mappingData)) {
                continue;
            }

            // Add the related entity to the cache if it's implements HistorizableUrlModelInterface
            try {
                $targetEntityClass = $mappingData['targetEntity'];
                $targetReflectionEntity = new \ReflectionClass($targetEntityClass);
            } catch (\Throwable) {
                continue;
            }

            if (!$targetReflectionEntity->implementsInterface(HistorizableUrlModelInterface::class)) {
                continue;
            }

            $this->cache->get(
                $this->getCacheKey($reflectionEntity->name),
                static function (ItemInterface $item) use ($targetEntityClass): array {
                    $cacheValue = $item->get() ?? [];

                    return array_unique(
                        array_merge(
                            $cacheValue,
                            [$targetEntityClass]
                        )
                    );
                },
                INF // We use INF to forces immediate expiration.
            );
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof HistorizableUrlModelInterface) {
            return;
        }

        // Get old values
        $route = $this->routableHandler->handle($entity);
        $changeSet = [];

        foreach (array_keys($route->getParameters()) as $routeParameter) {
            if ($args->hasChangedField($routeParameter)) {
                $changeSet[$routeParameter] = $args->getOldValue($routeParameter);
            }
        }

        // Process the entity update
        $this->urlPooler->processEntityUpdate($entity, $this->getOldEntity($entity, $changeSet));
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof HistorizableUrlModelInterface) {
            return;
        }

        if (!$entity->getUrlReference() instanceof UrlReference) {
            // Associate a fresh UrlReference to an entity
            $urlReference = (new UrlReference())
                ->setSeoUuid(Uuid::uuid4()->toString())
                ->setLocale(
                    method_exists($entity, 'getLocale') ?
                        $entity->getLocale() :
                        $this->defaultLocale
                )
                ->setRoute($this->routableHandler->handle($entity)->getName())
            ;

            $entity->setUrlReference($urlReference);
        }
    }

    /**
     * We use on flush rather than prePersist and postUpdate so the history works with DoctrineExtensions Sluggable.
     *
     * @throws InvalidArgumentException
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        // Process all objects being inserted, using scheduled insertions instead of prePersist in case if record will
        // be changed before flushing this will ensure correct result. No additional overhead is encountered.
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if (!$entity instanceof HistorizableUrlModelInterface) {
                continue;
            }

            $urlReference = $entity->getUrlReference();

            if (!$urlReference instanceof UrlReference) {
                continue;
            }

            $urlReference->setUrl($this->canonical->url($entity));
            $uow->recomputeSingleEntityChangeSet($em->getClassMetadata($this->getClass($urlReference)), $urlReference);
            $uow->persist($urlReference);
        }

        // We use onFlush and not preUpdate event to let other event listeners be nested together
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!$entity instanceof RoutableModelInterface) {
                continue;
            }

            if ($entity instanceof HistorizableUrlModelInterface) {
                $urlReference = $entity->getUrlReference();

                if (!$urlReference instanceof UrlReference) {
                    continue;
                }

                $newUrl = $this->canonical->url($entity);

                if ($urlReference->getUrl() !== $newUrl) {
                    $urlReference->setUrl($newUrl);
                    $uow->recomputeSingleEntityChangeSet(
                        $em->getClassMetadata($this->getClass($urlReference)),
                        $urlReference
                    );
                    $uow->persist($urlReference);
                }
            }

            // Look inside the cache if any dependent entity has to be historised
            $dependencies = $this->cache->get($this->getCacheKey($entity), static fn(ItemInterface $item): array => []);

            foreach ($dependencies as $dependantEntityClass) {
                // Fetches the current url of the entities
                $query = $args
                    ->getObjectManager()->createQueryBuilder()
                    ->select('dependant', 'url_ref')
                    ->from($dependantEntityClass, 'dependant')
                    ->innerJoin('dependant.urlReference', 'url_ref')
                ;

                // Regenerate route for all entities if different
                foreach ($query->getQuery()->getResult() as $dependantEntity) {
                    if ($this->urlPooler->processEntityDependency($dependantEntity)) {
                        $urlReference = $dependantEntity->getUrlReference();
                        $uow->recomputeSingleEntityChangeSet(
                            $em->getClassMetadata($this->getClass($urlReference)),
                            $urlReference
                        );
                        $uow->persist($urlReference);
                    }
                }
            }
        }
    }

    private function getClass(object $object): string
    {
        return $object instanceof Proxy ? get_parent_class($object) : $object::class;
    }

    private function getCacheKey(object|string $entity): string
    {
        $entityClass = \is_object($entity) ? $this->getClass($entity) : $entity;

        return self::ENTITY_DEPENDENCY_CACHE_KEY . '.' . str_replace('\\', '-', $entityClass);
    }

    private function getOldEntity(
        HistorizableUrlModelInterface $entity,
        array $oldValues
    ): HistorizableUrlModelInterface {
        $oldEntity = clone $entity;
        $oldEntityReflection = new \ReflectionClass($oldEntity);

        foreach ($oldValues as $key => $value) {
            try {
                $property = $oldEntityReflection->getProperty($key);
            } catch (\ReflectionException) {
                continue;
            }

            $property->setValue($oldEntity, $value);
        }

        return $oldEntity;
    }
}
