<?php

namespace Umanit\SeoBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Umanit\SeoBundle\Handler\Breadcrumbable\BreadcrumbableHandlerInterface;
use Umanit\SeoBundle\Handler\Routable\RoutableHandlerInterface;
use Umanit\SeoBundle\Handler\Schemable\SchemableHandlerInterface;
use Umanit\SeoBundle\Service\RouterAwareInterface;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class UmanitSeoExtension extends ConfigurableExtension implements PrependExtensionInterface
{
    public function loadInternal(array $configs, ContainerBuilder $container): void
    {
        // Set metadata configuration into params
        $metadata = $configs['metadata'];
        unset($metadata['form_type']);

        $container->setParameter('umanit_seo.metadata', $metadata);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $this->processServices($container, $configs);

        $container
            ->registerForAutoconfiguration(RoutableHandlerInterface::class)
            ->addTag('umanit_seo.routable.handler')
        ;
        $container
            ->registerForAutoconfiguration(BreadcrumbableHandlerInterface::class)
            ->addTag('umanit_seo.breadcrumbable.handler')
        ;
        $container
            ->registerForAutoconfiguration(SchemableHandlerInterface::class)
            ->addTag('umanit_seo.schemable.handler')
        ;
        $container
            ->registerForAutoconfiguration(RouterAwareInterface::class)
            ->addMethodCall('setRouter', [new Reference('router')])
        ;
    }

    public function prepend(ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');

        // Conditionnaly load sonata_admin.yaml
        if (isset($bundles['SonataAdminBundle'])) {
            $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load('sonata_admin.yaml');
        }
    }

    private function processServices(ContainerBuilder $container, array $config): void
    {
        if ($config['url_historization']['enabled']) {
            $urlRedirector = $container->getDefinition('umanit_seo.event_subscriber.url_redirector');

            $urlRedirector->setArgument(1, $config['url_historization']['redirect_code']);
        } else {
            $container->removeDefinition('umanit_seo.event_subscriber.url_history_writer');
            $container->removeDefinition('umanit_seo.event_subscriber.url_redirector');
        }

        $breadcrumbBuilder = $container->getDefinition('umanit_seo.breadcrumb.breadcrumb_builder');

        $breadcrumbBuilder->setArgument(3, $config['templates']);

        $seoMetadataType = $container->getDefinition('umanit_seo.form_type.seo_metadata_type');

        $seoMetadataType->setArgument(2, $config['metadata']['form_type']['inject_code_prettify']);

        $seoMetadataResolver = $container->getDefinition('umanit_seo.utils.seo_metadata_resolver');
        $metadata = $config['metadata'];
        unset($metadata['form_type']);

        $seoMetadataResolver->setArgument(2, $metadata);

        if ($config['metadata']['form_type']['add_seo_metadata_type']) {
            $formTypeExtension = $container->getDefinition('umanit_seo.form_extension.form_type');

            $formTypeExtension->setArgument(0, $config['metadata']['form_type']['class_fqcn']);
        } else {
            $container->removeDefinition('umanit_seo.form_extension.form_type');
        }
    }
}
