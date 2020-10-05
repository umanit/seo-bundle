<?php

namespace Umanit\SeoBundle\Routing;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Umanit\SeoBundle\Handler\Routable\RoutableInterface;
use Umanit\SeoBundle\Model\RoutableModelInterface;
use Umanit\SeoBundle\Model\Route;
use Umanit\SeoBundle\Service\RouterAwareInterface;
use Umanit\SeoBundle\Service\RouterAwareTrait;

class Canonical implements RouterAwareInterface
{
    use RouterAwareTrait;

    /** @var RoutableInterface */
    private $routableHandler;

    public function __construct(RoutableInterface $routableHandler)
    {
        $this->routableHandler = $routableHandler;
    }

    public function path(RoutableModelInterface $entity, array $overrides = []): string
    {
        $route = $this->routableHandler->handle($entity);
        $parameters = $this->buildParams($route, $overrides);

        return $this->router->generate($route->getName(), $parameters);
    }

    public function url(RoutableModelInterface $entity, array $overrides = []): string
    {
        $route = $this->routableHandler->handle($entity);
        $parameters = $this->buildParams($route, $overrides);

        return $this->router->generate(
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
