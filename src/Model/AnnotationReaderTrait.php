<?php

namespace Umanit\SeoBundle\Model;

use Doctrine\ORM\Proxy\Proxy;
use Umanit\SeoBundle\Doctrine\Annotation\Breadcrumb;
use Umanit\SeoBundle\Doctrine\Annotation\SchemaOrgBuilder;
use Umanit\SeoBundle\Doctrine\Annotation\Route;
use Doctrine\Common\Annotations\Reader as AnnotationsReader;
use Umanit\SeoBundle\Exception\NotBreadcrumbEntityException;
use Umanit\SeoBundle\Exception\NotSchemaOrgEntityException;
use Umanit\SeoBundle\Exception\NotSeoRouteEntityException;

/**
 * Trait AnnotationReaderTrait
 *
 * Helpful method to make use of
 * Doctrine\Common\Annotations\Reader
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
trait AnnotationReaderTrait
{
    /** @var AnnotationsReader */
    private $annotationsReader;

    /**
     * Service Call
     *
     * @param AnnotationsReader $annotationsReader
     */
    public function setAnnotationReader(AnnotationsReader $annotationsReader): void
    {
        $this->annotationsReader = $annotationsReader;
    }

    /**
     * Returns the @Route() annotation of an entity.
     *
     * @param $entity
     *
     * @return Route
     * @throws NotSeoRouteEntityException
     * @throws \ReflectionException
     */
    public function getSeoRouteAnnotation($entity): Route
    {
        if (!is_object($entity)) {
            throw new NotSeoRouteEntityException(sprintf('Value "%s" is a scalar, it cannot be annotated by Seo\Route(). Cannot use Seo routing features from it.', (string) $entity));
        }

        $route = $this->annotationsReader->getClassAnnotation(
            new \ReflectionClass($this->getClass($entity)),
            Route::class
        );

        if (null === $route) {
            throw new NotSeoRouteEntityException(sprintf('Entity of type "%s" is not annotated by Seo\Route(). Cannot use Seo routing features from it.', $this->getClass($entity)));
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $route;
    }

    /**
     * Returns the @SchemaOrg() annotation of an entity.
     *
     * @param $entity
     *
     * @return SchemaOrgBuilder
     * @throws NotSchemaOrgEntityException
     * @throws \ReflectionException
     */
    public function getSchemaOrgAnnotation($entity): SchemaOrgBuilder
    {
        if (!is_object($entity)) {
            throw new NotSchemaOrgEntityException(sprintf('Value "%s" is a scalar, it cannot be annotated by SchemaOrg(). Cannot use generate schema from it.', (string) $entity));
        }

        $schemaOrgBuilder = $this->annotationsReader->getClassAnnotation(
            new \ReflectionClass($this->getClass($entity)),
            SchemaOrgBuilder::class
        );

        if (null === $schemaOrgBuilder) {
            throw new NotSchemaOrgEntityException(sprintf('Entity of type "%s" is not annotated by SchemaOrg(). Cannot generate schema from it.', get_class($entity)));
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $schemaOrgBuilder;
    }

    /**
     * Returns the @Breadcrumb() annotation of an entity.
     *
     * @param $entity
     *
     * @return Breadcrumb
     * @throws NotBreadcrumbEntityException
     * @throws \ReflectionException
     */
    public function getBreadcrumbAnnotation($entity): Breadcrumb
    {
        if (!is_object($entity)) {
            throw new NotBreadcrumbEntityException(sprintf('Value "%s" is a scalar, it cannot be annotated by Breadcrumb(). Cannot generate breadcrumb from it.', (string) $entity));
        }

        $breadcrumb = $this->annotationsReader->getClassAnnotation(
            new \ReflectionClass($this->getClass($entity)),
            Breadcrumb::class
        );

        if (null === $breadcrumb) {
            throw new NotBreadcrumbEntityException(sprintf('Entity of type "%s" is not annotated by Breadcrumb(). Cannot generate breadcrumb from it.', get_class($entity)));
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $breadcrumb;
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
