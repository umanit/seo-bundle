<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Runtime\Twig;

use Symfony\Bridge\Twig\Extension\RoutingExtension as BaseRoutingExtension;
use Twig\Extension\RuntimeExtensionInterface;
use Umanit\SeoBundle\Model\RoutableModelInterface;
use Umanit\SeoBundle\Routing\Canonical;

class RoutingRuntime implements RuntimeExtensionInterface
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

    public function getPath($name, $parameters = [], $relative = false): ?string
    {
        if ($name instanceof RoutableModelInterface) {
            return $this->canonical->path($name, $parameters);
        }

        if (!\is_string($name)) {
            return null;
        }

        return $this->decorated->getPath($name, $parameters, $relative);
    }

    public function getUrl($name, $parameters = [], $schemeRelative = false): ?string
    {
        if ($name instanceof RoutableModelInterface) {
            return $this->canonical->url($name, $parameters);
        }

        if (!\is_string($name)) {
            return null;
        }

        return $this->decorated->getUrl($name, $parameters, $schemeRelative);
    }
}
