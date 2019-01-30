<?php

namespace Umanit\SeoBundle\Doctrine\EventSubscriber;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Proxy\Proxy;
use Umanit\SeoBundle\Doctrine\Annotation\RouteParameter;
use Umanit\SeoBundle\Model\AnnotationReaderTrait;
use Umanit\SeoBundle\UrlHistory\UrlPool;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Umanit\SeoBundle\Doctrine\Annotation\Route;
use Umanit\SeoBundle\Exception\NotSeoRouteEntityException;
use Umanit\SeoBundle\Routing\Canonical;

/**
 * Class UrlHistoryWriter
 *
 * Writes the url history log on update
 * of an entity annotated @Route().
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class UrlHistoryWriter implements EventSubscriber
{
    use AnnotationReaderTrait;

    /** @var UrlPool */
    private $urlPool;

    /** @var Canonical */
    private $urlBuilder;

    /**
     * UrlHistoryWriter constructor.
     *
     * @param UrlPool   $urlPool
     * @param Canonical $urlBuilder
     */
    public function __construct(UrlPool $urlPool, Canonical $urlBuilder)
    {
        $this->urlPool    = $urlPool;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     *
     * @return array|string[]
     */
    public function getSubscribedEvents()
    {
        return [Events::preUpdate, Events::postRemove, Events::postFlush];
    }

    /**
     * Before updating an entity.
     *
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity    = $args->getEntity();
        $changeSet = $args->getEntityChangeSet();

        try {
            $seoAnnotation = $this->getSeoRouteAnnotation($entity);
            // Build the new path
            $newPath = $this->urlBuilder->path($entity);
            // Get old values
            array_walk(
                $changeSet,
                function (array &$changedFieldValue, string $changedFieldKey) use ($changeSet, $seoAnnotation) {
                    foreach ($seoAnnotation->getRouteParameters() as $routeParameter) {
                        /** @var RouteParameter $routeParameter */
                        if ($routeParameter->getProperty() === $changedFieldKey) {
                            $changedFieldValue = $changedFieldValue[0];

                            return true;
                        }
                    }

                    unset($changeSet[$changedFieldKey]);

                    return false;
                }
            );
            // Build the old path
            $oldPath = $this->urlBuilder->path($entity, $changeSet);

            // Add the redirection to the pool
            $this->urlPool->add($oldPath, $newPath, $entity);
        } catch (NotSeoRouteEntityException $e) {
            // Do nothing
        }

        // Generate history for entities associated to this very entity.
        // Loop through all existing entities
        $metas = $args->getEntityManager()->getMetadataFactory()->getAllMetadata();
        foreach ($metas as $meta) {
            $class           = $meta->getName();
            $reflectionClass = new \ReflectionClass($class);
            foreach ($reflectionClass->getProperties() as $property) {
                $reader = new AnnotationReader();
                /** @var ManyToOne $manyToOne */
                $manyToOne = $reader->getPropertyAnnotation($property, ManyToOne::class);
                // Search if an attribute is a ManyToOne associated to the current updated entity class
                if (null === $manyToOne || $manyToOne->targetEntity !== $this->getClass($entity)) {
                    continue;
                }
                // If so, fetch all entities
                $entities = $args->getEntityManager()->getRepository($class)->findBy([$property->name => $entity]);
                foreach ($entities as $subEntity) {
                    // Build the new path
                    $subNewPath = $this->urlBuilder->path($subEntity);
                    // Get old values
                    $subChangeSet = [];
                    foreach ($changeSet as $changeFieldKey => $changedFieldValue) {
                        $subChangeSet[$property->name.'.'.$changeFieldKey] = $changedFieldValue;
                    }
                    $subOldPath = $this->urlBuilder->path($subEntity, $subChangeSet);

                    // Add it to the pool
                    $this->urlPool->add($subOldPath, $subNewPath, $subEntity);
                }
            }

        }
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        $this->urlPool->flush();
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        // TODO
    }

    /**
     * Returns the entity class.
     *
     * @param object $entity
     *
     * @return string
     */
    private function getClass(object $entity): string
    {
        return ($entity instanceof Proxy)
            ? get_parent_class($entity)
            : get_class($entity);
    }
}
