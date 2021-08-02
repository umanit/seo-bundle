<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Runtime\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Node;
use Twig\TwigFunction;

class RoutingExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'url',
                [RoutingRuntime::class, 'getUrl'],
                ['is_safe_callback' => [$this, 'isUrlGenerationSafe']]
            ),
            new TwigFunction(
                'path',
                [RoutingRuntime::class, 'getPath'],
                ['is_safe_callback' => [$this, 'isUrlGenerationSafe']]
            ),
        ];
    }

    /**
     * @param Node $argsNode
     *
     * @return array
     *
     * @see \Symfony\Bridge\Twig\Extension\RoutingExtension::isUrlGenerationSafe
     */
    public function isUrlGenerationSafe(Node $argsNode): array
    {
        // support named arguments
        $paramsNode = $argsNode->hasNode('parameters') ?
            $argsNode->getNode('parameters') :
            ($argsNode->hasNode('1') ? $argsNode->getNode('1') : null);

        if (
            null === $paramsNode || $paramsNode instanceof ArrayExpression && \count($paramsNode) <= 2 &&
            (!$paramsNode->hasNode('1') || $paramsNode->getNode('1') instanceof ConstantExpression)
        ) {
            return ['html'];
        }

        return [];
    }
}
