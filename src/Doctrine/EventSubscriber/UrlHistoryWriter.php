<?php

namespace Umanit\SeoBundle\Doctrine\EventSubscriber;

use Umanit\SeoBundle\Doctrine\Annotation\RouteParameter;
use Umanit\SeoBundle\Model\AnnotationReaderTrait;
use Umanit\SeoBundle\UrlHistory\UrlPool;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Umanit\SeoBundle\Doctrine\Annotation\Seo;
use Umanit\SeoBundle\Exception\NotSeoEntityException;
use Umanit\SeoBundle\Routing\Canonical;

/**
 * Class UrlHistoryWriter
 *
 * Writes the url history log on update
 * of an entity annotated @Seo().
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
        return [Events::preUpdate, Events::postRemove];
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
            $seoAnnotation = $this->getSeoAnnotation($entity);
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
        } catch (NotSeoEntityException $e) {
            return;
        }

    }

    public function postRemove(LifecycleEventArgs $args)
    {
        // TODO
    }
}
