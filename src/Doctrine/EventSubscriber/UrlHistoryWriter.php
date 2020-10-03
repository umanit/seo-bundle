<?php

namespace Umanit\SeoBundle\Doctrine\EventSubscriber;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Proxy\Proxy;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Annotation;
use Ramsey\Uuid\Uuid;
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

    /** @var Reader */
    private $annotationsReader;

    /** @var string */
    private $defaultLocale;

    /** @var bool */
    private $useUrlHistorization;

    private static $cache;

    public function __construct(
        UrlPool $urlPool,
        Routable $routableHandler,
        Canonical $urlBuilder,
        Reader $annotationsReader,
        string $defaultLocale,
        bool $useUrlHistorization
    ) {
        $this->urlPool = $urlPool;
        $this->routableHandler = $routableHandler;
        $this->urlBuilder = $urlBuilder;
        $this->annotationsReader = $annotationsReader;
        $this->defaultLocale = $defaultLocale;
        $this->useUrlHistorization = $useUrlHistorization;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::preUpdate,
            Events::onFlush,
            Events::prePersist,
            Events::postFlush,
            Events::postLoad,
        ];
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        if (!$this->useUrlHistorization) {
            return;
        }

        $entity = $args->getEntity();

        if (!$entity instanceof RoutableModelInterface) {
            return;
        }

        $route = $this->routableHandler->handle($entity);
        $reflectionEntity = new \ReflectionClass($entity);

        // Loops through each route parameters
        foreach (array_keys($route->getParameters()) as $routeParameter) {
            try {
                $reflectionProperty = $reflectionEntity->getProperty($routeParameter);
            } catch (\ReflectionException $e) {
                continue;
            }

            $annotations = $this->annotationsReader->getPropertyAnnotations($reflectionProperty);

            /** @var Annotation $annotation */
            foreach ($annotations as $annotation) {
                if (!property_exists($annotation, 'targetEntity')) {
                    continue;
                }

                // Add the related entity to the cache if it's a mapping annotation
                $cacheKey = $this->getCacheKey($entity);
                $cacheValue = self::$cache[$cacheKey] ?? [];
                self::$cache[$cacheKey] = array_unique(array_merge(
                    $cacheValue,
                    [$annotation->targetEntity]
                ));
            }
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        if (!$this->useUrlHistorization) {
            return;
        }

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
        if (!$this->useUrlHistorization) {
            return;
        }

        $entity = $args->getEntity();

        if (!$entity instanceof HistorizableUrlModelInterface) {
            return;
        }

        if (null === $entity->getUrlReference()) {
            // Associate a fresh UrlReference to an entity
            $urlReference = (new UrlReference())
                ->setSeoUuid(Uuid::uuid4())
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
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        if (!$this->useUrlHistorization) {
            return;
        }

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
            foreach (self::$cache[$this->getCacheKey($entity)] ?? [] as $dependantEntityClass) {
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
        if (!$this->useUrlHistorization) {
            return;
        }

        $this->urlPool->flush();
    }

    private function getClass(object $object): string
    {
        return $object instanceof Proxy ? get_parent_class($object) : \get_class($object);
    }

    private function getCacheKey(object $entity): string
    {
        return self::ENTITY_DEPENDENCY_CACHE_KEY.'.'.$this->getClass($entity);
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
