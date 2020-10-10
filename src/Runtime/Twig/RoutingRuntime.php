<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Runtime\Twig;

use Symfony\Bridge\Twig\Extension\RoutingExtension as BaseRoutingExtension;
use Twig\Extension\RuntimeExtensionInterface;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Node;
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

        return $this->decorated->getPath($name, $parameters, $schemeRelative);
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
