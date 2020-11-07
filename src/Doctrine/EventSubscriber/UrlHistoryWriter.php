<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Doctrine\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Proxy\Proxy;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
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
use Umanit\SeoBundle\UrlHistory\UrlPool;

class UrlHistoryWriter implements EventSubscriber
{
    public const ENTITY_DEPENDENCY_CACHE_KEY = 'seo.entity_dependencies';

    /** @var UrlPool */
    private $urlPool;

    /** @var Routable */
    private $routableHandler;

    /** @var Canonical */
    private $urlBuilder;

    /** @var CacheInterface */
    private $cache;

    /** @var string */
    private $defaultLocale;

    public function __construct(
        UrlPool $urlPool,
        Routable $routableHandler,
        Canonical $urlBuilder,
        CacheInterface $cache,
        string $defaultLocale
    ) {
        $this->urlPool = $urlPool;
        $this->routableHandler = $routableHandler;
        $this->urlBuilder = $urlBuilder;
        $this->cache = $cache;
        $this->defaultLocale = $defaultLocale;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
            Events::preUpdate,
            Events::onFlush,
            Events::prePersist,
            Events::postFlush,
        ];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        $reflectionEntity = $args->getClassMetadata()->getReflectionClass();

        if (!$reflectionEntity->implementsInterface(RoutableModelInterface::class)) {
            return;
        }

        // Loops through each entity associations
        foreach ($args->getClassMetadata()->getAssociationMappings() as $fieldName => $mappingData) {
            if (!\array_key_exists('targetEntity', $mappingData)) {
                continue;
            }

            // Add the related entity to the cache if it's implements HistorizableUrlModelInterface
            try {
                $targetEntityClass = $mappingData['targetEntity'];
                $targetReflectionEntity = new \ReflectionClass($targetEntityClass);
            } catch (\Throwable $e) {
                continue;
            }

            if (!$targetReflectionEntity->implementsInterface(HistorizableUrlModelInterface::class)) {
                continue;
            }

            $this->cache->get(
                $this->getCacheKey($mappingData['sourceEntity']),
                static function (ItemInterface $item) use ($targetEntityClass) {
                    $cacheValue = $item->get() ?? [];

                    return array_unique(array_merge(
                        $cacheValue,
                        [$targetEntityClass]
                    ));
                },
                INF // We use INF to forces immediate expiration.
            );
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getEntity();

        if (!$entity instanceof HistorizableUrlModelInterface) {
            return;
        }

        // Build the new path
        $newPath = $this->urlBuilder->url($entity);
        $route = $this->routableHandler->handle($entity);
        $changeSet = [];

        // Get old values
        foreach (array_keys($route->getParameters()) as $routeParameter) {
            if ($args->hasChangedField($routeParameter)) {
                $changeSet[$routeParameter] = $args->getOldValue($routeParameter);
            }
        }

        // Build the old path
        $oldPath = $this->urlBuilder->url($this->getOldEntity($entity, $changeSet));

        // Add the redirection to the pool
        $this->urlPool->add($oldPath, $newPath, $entity);
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if (!$entity instanceof HistorizableUrlModelInterface) {
            return;
        }

        if (null === $entity->getUrlReference()) {
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
     * @param OnFlushEventArgs $args
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        // Process all objects being inserted, using scheduled insertions instead of prePersist in case if record will
        // be changed before flushing this will ensure correct result. No additional overhead is encountered.
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if (!$entity instanceof HistorizableUrlModelInterface) {
                continue;
            }

            $urlReference = $entity->getUrlReference();

            if (null === $urlReference) {
                continue;
            }

            $urlReference->setUrl($this->urlBuilder->url($entity));
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

                if (null === $urlReference) {
                    continue;
                }

                $newUrl = $this->urlBuilder->url($entity);

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
            $dependencies = $this->cache->get($this->getCacheKey($entity), static function (ItemInterface $item) {
                return [];
            });

            foreach ($dependencies as $dependantEntityClass) {
                // Fetches the current url of the entities
                $query = $args
                    ->getEntityManager()->createQueryBuilder()
                    ->select('dependant', 'url_ref')
                    ->from($dependantEntityClass, 'dependant')
                    ->innerJoin('dependant.urlReference', 'url_ref')
                ;

                // Regenerate route for all entities if different
                foreach ($query->getQuery()->getResult() as $dependantEntity) {
                    $urlReference = $dependantEntity->getUrlReference();
                    $newUrl = $this->urlBuilder->url($dependantEntity);
                    $oldUrl = $urlReference->getUrl();

                    if ($newUrl !== $oldUrl) {
                        // Add the redirection to the pool
                        $this->urlPool->add($oldUrl, $newUrl, $dependantEntity);
                        $urlReference->setUrl($newUrl);
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

    public function postFlush(PostFlushEventArgs $args): void
    {
        $this->urlPool->flush();
    }

    private function getClass(object $object): string
    {
        return $object instanceof Proxy ? get_parent_class($object) : \get_class($object);
    }

    /**
     * @param object|string $entity
     *
     * @return string
     */
    private function getCacheKey($entity): string
    {
        $entityClass = \is_object($entity) ? $this->getClass($entity) : $entity;

        return self::ENTITY_DEPENDENCY_CACHE_KEY.'.'.str_replace('\\', '-', $entityClass);
    }

    private function getOldEntity(RoutableModelInterface $entity, array $oldValues): RoutableModelInterface
    {
        $oldEntity = clone $entity;
        $oldEntityReflection = new \ReflectionClass($oldEntity);

        foreach ($oldValues as $key => $value) {
            try {
                $property = $oldEntityReflection->getProperty($key);
            } catch (\ReflectionException $e) {
                continue;
            }

            $property->setAccessible(true);
            $property->setValue($oldEntity, $value);
        }

        return $oldEntity;
    }
}
