<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Runtime\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SeoExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('seo_title', [SeoRuntime::class, 'title']),
            new TwigFunction('seo_canonical', [SeoRuntime::class, 'canonical'], ['is_safe' => ['html']]),
            new TwigFunction('seo_metadata', [SeoRuntime::class, 'metadata'], ['is_safe' => ['html']]),
            new TwigFunction(
                'seo_schema_org',
                [SeoRuntime::class, 'schemaOrg'],
                ['is_safe' => ['html', 'javascript']]
            ),
            new TwigFunction(
                'seo_breadcrumb',
                [SeoRuntime::class, 'breadcrumb'],
                ['is_safe' => ['html', 'javascript']]
            ),
        ];
    }
}
