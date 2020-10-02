<?php

namespace Umanit\SeoBundle\Routing;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Umanit\SeoBundle\Handler\Routable\RoutableInterface;
use Umanit\SeoBundle\Model\AnnotationReaderTrait;
use Umanit\SeoBundle\Model\RoutableModelInterface;
use Umanit\SeoBundle\Model\Route;

/**
 * Used to generate links from an entity which implements Umanit\SeoBundle\Model\RoutableInterface.
 */
class Canonical
{
    use AnnotationReaderTrait;

    /** @var PropertyAccessor */
    private $propAccess;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /** @var RoutableInterface */
    private $routableHandler;

    public function __construct(UrlGeneratorInterface $urlGenerator, RoutableInterface $routableHandler)
    {
        $this->urlGenerator = $urlGenerator;
        $this->routableHandler = $routableHandler;
    }

    public function path(RoutableModelInterface $entity, array $overrides = []): string
    {
        $route = $this->routableHandler->handle($entity);
        $parameters = $this->buildParams($route, $overrides);

        return $this->urlGenerator->generate($route->getName(), $parameters);
    }

    public function url(RoutableModelInterface $entity, array $overrides = []): string
    {
        $route = $this->routableHandler->handle($entity);
        $parameters = $this->buildParams($route, $overrides);

        return $this->urlGenerator->generate(
            $route->getName(),
            $parameters,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    private function buildParams(Route $route, array $overrides = []): array
    {
        $params = [];

        foreach ($route->getParameters() as $key => $value) {
            $params[$key] = $overrides[$key] ?? $value;
        }

        return $params;
    }
}
