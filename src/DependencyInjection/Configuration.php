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
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('umanit_seo');
        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('use_url_historization')
                    ->defaultTrue()
                ->end()
                ->scalarNode('redirect_code')
                    ->defaultValue(301)
                ->end()
                ->scalarNode('cache_service')
                    ->defaultValue('cache.app')
                ->end()
                ->arrayNode('templates')
                    ->info('Defines the default templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('breadcrumb_json_ld')
                            ->defaultValue('@UmanitSeo/breadcrumb/breadcrumb.json-ld.html.twig')
                        ->end()
                        ->scalarNode('breadcrumb_microdata')
                            ->defaultValue('@UmanitSeo/breadcrumb/breadcrumb.microdata.html.twig')
                        ->end()
                        ->scalarNode('breadcrumb_rdfa')
                            ->defaultValue('@UmanitSeo/breadcrumb/breadcrumb.rdfa.html.twig')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('metadata')
                    ->info('Defines the default metadata')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('auto_inject_code_prettify')
                            ->info('Injects Google Code Prettify when rendering breadcrumb ans schema.org in FormType.')
                            ->defaultTrue()
                        ->end()
                        ->scalarNode('default_title')
                            ->defaultValue('Umanit Seo - Customize this default title to your needs.')
                        ->end()
                        ->scalarNode('title_prefix')
                            ->defaultValue('')
                        ->end()
                        ->scalarNode('title_suffix')
                            ->defaultValue('')
                        ->end()
                        ->scalarNode('default_description')
                            ->defaultValue('Umanit Seo - Customize this default description to your needs.')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
