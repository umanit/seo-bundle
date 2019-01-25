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
                ->arrayNode('metadata')->info('Defines the default metadata')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_title')->defaultValue('Umanit Seo - Customize this default title to your needs.')->end()
                        ->scalarNode('default_description')->defaultValue('Umanit Seo - Customize this default description to your needs.')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
