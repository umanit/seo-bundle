<?php

namespace Umanit\SeoBundle\Doctrine\EventSubscriber;

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
use Umanit\SeoBundle\Doctrine\Annotation\Route;
use Umanit\SeoBundle\Doctrine\Annotation\RouteParameter;
use Umanit\SeoBundle\Doctrine\Model\UrlHistorizedInterface;
use Umanit\SeoBundle\Entity\UrlRef;
use Umanit\SeoBundle\Exception\NotSeoRouteEntityException;
use Umanit\SeoBundle\Model\AnnotationReaderTrait;
use Umanit\SeoBundle\Routing\Canonical;
use Umanit\SeoBundle\UrlHistory\UrlPool;

/**
 * Class UrlHistoryWriter
 *
 * Writes the url history log on update
 * of an entity annotated @Route().
 */
class UrlHistoryWriter implements EventSubscriber
{
    public const ENTITY_DEPENDENCY_CACHE_KEY = 'seo.entity_dependencies';

    use AnnotationReaderTrait;

    /** @var UrlPool */
    private $urlPool;

    /** @var Canonical */
    private $urlBuilder;

    /** @var CacheInterface */
    private $cache;

    /** @var string */
    private $defaultLocale;

    public function __construct(UrlPool $urlPool, Canonical $urlBuilder, CacheInterface $cache, string $defaultLocale)
    {
        $this->urlPool = $urlPool;
        $this->urlBuilder = $urlBuilder;
        $this->cache = $cache;
        $this->defaultLocale = $defaultLocale;
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
     * @throws \ReflectionException
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        if (null === $args->getClassMetadata()->getReflectionClass()) {
            return;
        }

        $route = $this->annotationsReader->getClassAnnotation(
            $args->getClassMetadata()->getReflectionClass(),
            Route::class
        );

        if (!$route instanceof Route) {
            return;
        }

        foreach ($route->getRouteParameters() as $routeParameter) {
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
        $route = $this->annotationsReader->getClassAnnotation(
            new \ReflectionClass($child),
            Route::class
        );

        if (!$route instanceof Route) {
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
     * Before updating an entity.
     *
     * @param PreUpdateEventArgs $args
     *
     * @throws \ReflectionException
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getEntity();
        $changeSet = $args->getEntityChangeSet();

        if (!$entity instanceof UrlHistorizedInterface) {
            return;
        }

        try {
            $seoAnnotation = $this->getSeoRouteAnnotation($entity);

            // Build the new path
            $newPath = $this->urlBuilder->url($entity);

            // Get old values
            foreach ($changeSet as $changedFieldKey => $changedFieldValue) {
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
        } catch (NotSeoRouteEntityException $e) {
            // Do nothing
        }
    }

    /**
     * On prePersist, associate a fresh UrlRef to an entity.
     *
     * @param LifecycleEventArgs $args
     *
     * @throws \ReflectionException
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if (!$entity instanceof UrlHistorizedInterface) {
            return;
        }

        try {
            if (null === $entity->getUrlRef()) {
                $route = $this->getSeoRouteAnnotation($entity);
                $urlRef = (new UrlRef())
                    ->setSeoUuid(Uuid::uuid4())
                    ->setLocale(method_exists($entity, 'getLocale') ? $entity->getLocale() : $this->defaultLocale)
                    ->setRoute($route->getRouteName())
                ;

                $entity->setUrlRef($urlRef);
            }
        } catch (NotSeoRouteEntityException $e) {
            // Do nothing
        }
    }

    /**
     * We use on flush rather than prePersist
     * and postUpdate so the history works
     * with DoctrineExtensions Sluggable.
     *
     * @param OnFlushEventArgs $args
     *
     * @return void
     * @throws NotSeoRouteEntityException
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \ReflectionException
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        // process all objects being inserted, using scheduled insertions instead
        // of prePersist in case if record will be changed before flushing this will
        // ensure correct result. No additional overhead is encountered
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if (!$entity instanceof UrlHistorizedInterface) {
                continue;
            }

            try {
                /** @var UrlRef $urlRef */
                $urlRef = $entity->getUrlRef();

                if (null === $urlRef) {
                    continue;
                }

                $urlRef->setUrl($this->urlBuilder->url($entity));
                $uow->recomputeSingleEntityChangeSet($em->getClassMetadata($this->getClass($urlRef)), $urlRef);
                $uow->persist($urlRef);
            } catch (NotSeoRouteEntityException $e) {
                // Do nothing
            }
        }

        // we use onFlush and not preUpdate event to let other
        // event listeners be nested together
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof UrlHistorizedInterface) {
                try {
                    /** @var UrlRef $urlRef */
                    $urlRef = $entity->getUrlRef();

                    if (null === $urlRef) {
                        continue;
                    }

                    $newUrl = $this->urlBuilder->url($entity);

                    if ($urlRef->getUrl() !== $newUrl) {
                        $urlRef->setUrl($newUrl);
                        $uow->recomputeSingleEntityChangeSet($em->getClassMetadata($this->getClass($urlRef)), $urlRef);
                        $uow->persist($urlRef);
                    }
                } catch (NotSeoRouteEntityException $e) {
                    // Do nothing
                }
            }

            // Look inside the cache if any dependent entity has to be historised
            $cache = $this->cache->get(self::ENTITY_DEPENDENCY_CACHE_KEY, []);

            if (!\array_key_exists($this->getClass($entity), $cache)) {
                continue;
            }

            foreach ($cache[$this->getClass($entity)] as $dependantEntityClass) {
                // Fetches the current url of the entities
                $query = $args
                    ->getEntityManager()->createQueryBuilder()
                    ->select('dependant', 'url_ref')
                    ->from($dependantEntityClass, 'dependant')
                    ->innerJoin('dependant.urlRef', 'url_ref')
                ;

                // Regenerate route for all entities if different
                foreach ($query->getQuery()->getResult() as $dependantEntity) {
                    $urlRef = $dependantEntity->getUrlRef();
                    $newUrl = $this->urlBuilder->url($dependantEntity);
                    $oldUrl = $urlRef->getUrl();

                    if ($newUrl !== $oldUrl) {
                        // Add the redirection to the pool
                        $this->urlPool->add($oldUrl, $newUrl, $dependantEntity);
                        $urlRef->setUrl($newUrl);
                        $uow->recomputeSingleEntityChangeSet($em->getClassMetadata($this->getClass($urlRef)), $urlRef);
                        $uow->persist($urlRef);
                    }
                }
            }
        }
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        $this->urlPool->flush();
    }

    /**
     * When the property of a route parameter is
     * the path to a property of a child entity,
     * explodes the property and returns an
     * array
     *
     * @param RouteParameter $routeParameter
     *
     * @return false|string[]
     */
    private function getPropertyAsArray(RouteParameter $routeParameter)
    {
        return explode('.', $routeParameter->getProperty());
    }

    /**
     * Tries to get a value from a reflection class, and calls
     * itself recursively to test the parent reflection class
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
}
