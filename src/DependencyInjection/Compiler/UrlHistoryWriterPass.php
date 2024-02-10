<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\DependencyInjection\Compiler;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Umanit\SeoBundle\DependencyInjection\Configuration;

class UrlHistoryWriterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('umanit_seo.event_subscriber.url_history_writer')) {
            return;
        }

        try {
            $configs = $container->getExtensionConfig('umanit_seo');
            $configuration = new Configuration();
            $config = $this->processConfiguration($configuration, $configs);

            $cacheService = $container->getDefinition($config['url_historization']['cache_service']);
            $urlHistoryWriter = $container->getDefinition('umanit_seo.event_subscriber.url_history_writer');

            $urlHistoryWriter->setArgument(3, $cacheService);
        } catch (\Throwable $throwable) {
            throw new \RuntimeException(
                sprintf(
                    'Can not build UmanIT SEO - UrlHistoryWriter: %s',
                    $throwable->getMessage()
                ), $throwable->getCode(), $throwable
            );
        }
    }

    private function processConfiguration(ConfigurationInterface $configuration, array $configs): array
    {
        return (new Processor())->processConfiguration($configuration, $configs);
    }
}
