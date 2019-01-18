<?php

namespace Umanit\SeoBundle\Routing;

use Umanit\SeoBundle\Model\AnnotationReaderTrait;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Umanit\SeoBundle\Doctrine\Annotation\RouteParameter;
use Umanit\SeoBundle\Doctrine\Annotation\Seo;
use Umanit\SeoBundle\Exception\NotSeoEntityException;

/**
 * Class Canonical
 *
 * Used to generate links from
 * an entity annotated by @Seo().
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class Canonical
{
    use AnnotationReaderTrait;

    /** @var PropertyAccessor */
    private $propAccess;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /**
     * Canonical constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->propAccess   = new PropertyAccessor();
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Returns a canonical path.
     *
     * @param object $entity    An entity annotated by @Seo()
     * @param array  $overrides An associative array used to override field values if needed.
     *
     * @return string
     * @throws NotSeoEntityException
     */
    public function path($entity, array $overrides = []): string
    {
        /** @var Seo $seo */
        $seo = $this->getSeoAnnotation($entity);

        $params = $this->buildParams($seo, $entity, $overrides);

        return $this->urlGenerator->generate($seo->getRouteName(), $params);
    }

    /**
     * Returns a canonical url.
     *
     * @param object $entity    An entity annotated by @Seo()
     * @param array  $overrides An associative array used to override field values if needed.
     *
     * @return string
     * @throws NotSeoEntityException
     */
    public function url($entity, array $overrides = []): string
    {
        /** @var Seo $seo */
        $seo = $this->getSeoAnnotation($entity);

        $params = $this->buildParams($seo, $entity, $overrides);

        return $this->urlGenerator->generate($seo->getRouteName(), $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * Builds the route params.
     *
     * @param Seo    $seo
     * @param object $entity    An entity annotated by @Seo()
     * @param array  $overrides An associative array used to override field values if needed.
     *
     * @return array
     */
    private function buildParams(Seo $seo, $entity, array $overrides = []): array
    {
        $params = [];
        foreach ($seo->getRouteParameters() as $routeParam) {
            /** @var RouteParameter $routeParam */
            $propName                            = $routeParam->getProperty();
            $params[$routeParam->getParameter()] =
                $overrides[$propName] ?? $this->propAccess->getValue($entity, $propName);
        }

        return $params;
    }
}
