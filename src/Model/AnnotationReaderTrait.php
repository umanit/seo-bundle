<?php

namespace Umanit\SeoBundle\Model;

use Umanit\SeoBundle\Doctrine\Annotation\Seo;
use Doctrine\Common\Annotations\Reader as AnnotationsReader;
use Umanit\SeoBundle\Exception\NotSeoEntityException;

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
     * Returns the @Seo() annotation of an entity.
     *
     * @param      $entity
     *
     * @return Seo
     * @throws NotSeoEntityException
     * @throws \ReflectionException
     */
    public function getSeoAnnotation($entity): Seo
    {
        if (!is_object($entity)) {
            throw new NotSeoEntityException(sprintf('A scalar cannot be annotated by Seo(). Cannot use SEO features from it.'));
        }
        $seo = $this->annotationsReader->getClassAnnotation(
            new \ReflectionClass(get_class($entity)),
            Seo::class
        );

        if (null === $seo) {
            throw new NotSeoEntityException(sprintf('Entity of type "%s" is not annotated by Seo(). Cannot use SEO features from it.', get_class($entity)));
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $seo;
    }
}
