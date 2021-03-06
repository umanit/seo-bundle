<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Routing;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Umanit\SeoBundle\Handler\Routable\RoutableInterface;
use Umanit\SeoBundle\Model\RoutableModelInterface;
use Umanit\SeoBundle\Model\Route;

class Canonical
{
    /** @var RouterInterface */
    private $router;

    /** @var RoutableInterface */
    private $routableHandler;

    public function __construct(RouterInterface $router, RoutableInterface $routableHandler)
    {
        $this->router = $router;
        $this->routableHandler = $routableHandler;
    }

    public function path(RoutableModelInterface $entity, array $parameters = []): string
    {
        $route = $this->routableHandler->handle($entity);
        $parameters = $this->buildParams($route, $parameters);

        return $this->router->generate($route->getName(), $parameters);
    }

    public function url(RoutableModelInterface $entity, array $parameters = []): string
    {
        $route = $this->routableHandler->handle($entity);
        $parameters = $this->buildParams($route, $parameters);

        return $this->router->generate(
            $route->getName(),
            $parameters,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    private function buildParams(Route $route, array $parameters = []): array
    {
        foreach ($route->getParameters() as $key => $value) {
            $parameters[$key] = $parameters[$key] ?? $value;
        }

        return $parameters;
    }
}
