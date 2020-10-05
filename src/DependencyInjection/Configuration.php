<?php

namespace Umanit\SeoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Umanit\SeoBundle\Form\Type\SeoMetadataType;

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
                ->arrayNode('url_historization')
                    ->info('Historize URLs of entities which implements HistorizableUrlModelInterface')
                    ->addDefaultsIfNotSet()
                    ->canBeDisabled()
                    ->children()
                        ->scalarNode('redirect_code')
                            ->info('Redirect code used by UrlRedirectorSubscriber')
                            ->defaultValue(301)
                        ->end()
                        ->scalarNode('cache_service')
                            ->info('Cache service used to store entities dependencies. **MUST** implements \Symfony\Contracts\Cache\CacheInterface')
                            ->defaultValue('cache.app')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('templates')
                    ->info('Defines the default templates used to render breadcrumbs')
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
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('form_type')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('add_seo_metadata_type')
                                    ->info('Automaticaly add a SeoMetadataType on FormType which handled an entity which implements HasSeoMetadataInterface')
                                    ->defaultTrue()
                                ->end()
                                ->scalarNode('class_fqcn')
                                    ->info('FQCN of the FormType used to renders SEO Metadata fields')
                                    ->defaultValue(SeoMetadataType::class)
                                ->end()
                                ->booleanNode('inject_code_prettify')
                                    ->info('Injects Google Code Prettify when rendering breadcrumb and schema.org in FormType.')
                                    ->defaultTrue()
                                ->end()
                            ->end()
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
