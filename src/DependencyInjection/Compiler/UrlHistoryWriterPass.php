<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class UrlHistoryWriterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        try {
            $cacheService = $container->getDefinition($container->getParameter('umanit_seo.cache_service'));
            $urlHistoryWriter = $container->getDefinition('umanit_seo.event_subscriber.url_history_writer');

            $urlHistoryWriter->setArgument(4, $cacheService);
        } catch (\Throwable $e) {
            throw new \RuntimeException(sprintf(
                'Can not build UmanIT SEO - UrlHistoryWriter: %s',
                $e->getMessage()
            ));
        }
    }
}
