<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Runtime\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RoutingExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'url',
                [RoutingRuntime::class, 'getUrl'],
                ['is_safe_callback' => [RoutingRuntime::class, 'isUrlGenerationSafe']]
            ),
            new TwigFunction(
                'path',
                [RoutingRuntime::class, 'getPath'],
                ['is_safe_callback' => [RoutingRuntime::class, 'isUrlGenerationSafe']]
            ),
        ];
    }
}
