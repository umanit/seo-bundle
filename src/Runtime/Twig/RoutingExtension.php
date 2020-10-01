<?php

namespace Umanit\SeoBundle\Runtime\Twig;

use Symfony\Bridge\Twig\Extension\RoutingExtension as BaseRoutingExtension;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Umanit\SeoBundle\Exception\NotSeoRouteEntityException;
use Umanit\SeoBundle\Routing\Canonical;

class RoutingExtension extends AbstractExtension
{
    /** @var BaseRoutingExtension */
    private $decorated;

    /** @var Canonical */
    private $canonical;

    public function __construct(BaseRoutingExtension $decorated, Canonical $canonical)
    {
        $this->decorated = $decorated;
        $this->canonical = $canonical;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('url', [$this, 'getUrl'], ['is_safe_callback' => [$this->decorated, 'isUrlGenerationSafe']]),
            new TwigFunction('path', [$this, 'getPath'], ['is_safe_callback' => [$this->decorated, 'isUrlGenerationSafe']]),
        ];
    }

    public function getPath($name, $parameters = [], $relative = false): ?string
    {
        try {
            return $this->canonical->path($name, $parameters);
        } catch (NotSeoRouteEntityException $e) {
            return $this->decorated->getPath($name, $parameters, $relative);
        }
    }

    public function getUrl($name, $parameters = [], $schemeRelative = false): ?string
    {
        try {
            return $this->canonical->url($name, $parameters);
        } catch (NotSeoRouteEntityException $e) {
            return $this->decorated->getPath($name, $parameters, $schemeRelative);
        }
    }
}
