<?php

namespace Umanit\SeoBundle\Doctrine\EventSubscriber;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
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

    /** @var Reader */
    private $annotationsReader;

    /** @var string */
    private $defaultLocale;

    /** @var bool */
    private $useUrlHistorization;

    public function __construct(
        UrlPool $urlPool,
        Routable $routableHandler,
        Canonical $urlBuilder,
        CacheInterface $cache,
        Reader $annotationsReader,
        string $defaultLocale,
        bool $useUrlHistorization
    ) {
        $this->urlPool = $urlPool;
        $this->routableHandler = $routableHandler;
        $this->urlBuilder = $urlBuilder;
        $this->cache = $cache;
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
            Events::loadClassMetadata,
        ];
    }

    /**
     * Creates the cache used to match entities.
     *
     * @param LoadClassMetadataEventArgs $args
     *
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \ReflectionException
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        return;

        if (!$this->useUrlHistorization || null === $args->getClassMetadata()->getReflectionClass()) {
            return;
        }

        $entity = $args->getClassMetadata()->getReflectionClass();

        if (!$entity instanceof RoutableModelInterface) {
            return;
        }

        $route = $this->routableHandler->handle($entity);

        foreach ($route->getParameters() as $routeParameter) {
            $pathItems = $this->getPropertyAsArray($routeParameter);

            if (\count($pathItems) > 1) {
                $this->walkRouteParameters(
                    $args->getClassMetadata()->rootEntityName,
                    $args->getClassMetadata()->rootEntityName,
                    $pathItems
                );
            }
        }
    }

    /**
     * Before updating an entity.
     *
     * @param PreUpdateEventArgs $args
     *
     * @throws \ReflectionException
     */
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
        $changeSet = $args->getEntityChangeSet();

        // Get old values
        foreach ($changeSet as $changedFieldKey => $changedFieldValue) {
            continue;

            foreach ($seoAnnotation->getRouteParameters() as $routeParameter) {
                /** @var RouteParameter $routeParameter */
                $propertyAsArray = $this->getPropertyAsArray($routeParameter);

                if (reset($propertyAsArray) === $changedFieldKey) {
                    // The old value is the first element of the array
                    $value = $changedFieldValue[0];

                    if (\is_object($value)) {
                        $reflection = new \ReflectionClass($value);

                        // If it's an objet, tries to access the value from the class
                        try {
                            $value = $this->getValueFromReflectionClass(
                                $reflection,
                                $propertyAsArray[1],
                                $value
                            );
                        } catch (\ReflectionException $e) {
                            continue;
                        }
                    }

                    $changeSet[$changedFieldKey] = $value;
                }
            }
        }

        // Build the old path
        $oldPath = $this->urlBuilder->url($entity, $changeSet);

        // Add the redirection to the pool
        $this->urlPool->add($oldPath, $newPath, $entity);
    }

    /**
     * On prePersist, associate a fresh UrlReference to an entity.
     *
     * @param LifecycleEventArgs $args
     *
     * @throws \Exception
     */
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
     * @throws \Psr\Cache\InvalidArgumentException
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
            $cache = $this->cache->get(self::ENTITY_DEPENDENCY_CACHE_KEY, static function (ItemInterface $item) {
                return [];
            });

            if (!\array_key_exists($this->getClass($entity), $cache)) {
                continue;
            }

            foreach ($cache[$this->getClass($entity)] as $dependantEntityClass) {
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

    /**
     * Walk through relations to set the cache.
     *
     * @param string $rootEntity
     * @param string $subEntity
     * @param array  $pathItems
     *
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \ReflectionException
     */
    private function walkRouteParameters(string $rootEntity, string $subEntity, array &$pathItems): void
    {
        $refl = new \ReflectionClass($subEntity);

        foreach ($pathItems as $key => $pathItem) {
            $pathItem = preg_replace('/\[.+\]/', '', $pathItem);

            try {
                $prop = $refl->getProperty($pathItem);
            } catch (\ReflectionException $e) {
                continue;
            }

            $annotations = $this->annotationsReader->getPropertyAnnotations($prop);

            foreach ($annotations as $annotation) {
                if (property_exists($annotation, 'targetEntity')) {
                    $targetEntity = $annotation->targetEntity;
                    unset($pathItems[$key]);

                    $this->addEntitiesToCache($targetEntity, $rootEntity);
                    $this->walkRouteParameters($rootEntity, $targetEntity, $pathItems);
                }
            }
        }
    }

    /**
     * Adds and association to the cache.
     *
     * @param string $parent
     * @param string $child
     *
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \ReflectionException
     */
    private function addEntitiesToCache(string $parent, string $child): void
    {
        $childClass = new \ReflectionClass($child);

        if (!$childClass instanceof RoutableModelInterface) {
            return;
        }

        $this->cache->get(
            self::ENTITY_DEPENDENCY_CACHE_KEY,
            static function (ItemInterface $item) use ($parent, $child) {
                $value = $item->get();

                if (!isset($value[$parent])) {
                    $value[$parent] = [];
                }

                if (!\in_array($child, $value[$parent], true)) {
                    $value[$parent][] = $child;
                }

                return $value;
            }
        );
    }

    /**
     * Tries to get a value from a reflection class, and calls itself recursively to test the parent reflection class
     * in case of inheritance (on one or more levels)
     *
     * @param \ReflectionClass $reflection
     * @param string           $property
     * @param object           $object
     *
     * @return mixed
     * @throws \ReflectionException
     */
    private function getValueFromReflectionClass(\ReflectionClass $reflection, string $property, object $object)
    {
        try {
            $prop = $reflection->getProperty($property);
            $prop->setAccessible(true);

            return $prop->getValue($object);
        } catch (\ReflectionException $e) {
            $parentReflectionClass = $reflection->getParentClass();

            if (false !== $parentReflectionClass) {
                return $this->getValueFromReflectionClass($parentReflectionClass, $property, $object);
            }

            throw new \ReflectionException('The property wasn\'t found');
        }
    }

    private function getClass(object $object): string
    {
        return \get_class($object);
    }
}
