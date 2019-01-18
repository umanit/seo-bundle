<?php

namespace Umanit\SeoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('umanit_seo');
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('redirect_code')->defaultValue(301)->end()
                ->scalarNode('cache_service')->defaultValue('cache.app')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
