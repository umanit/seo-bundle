<?php

declare(strict_types=1);

namespace Umanit\SeoBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Umanit\SeoBundle\DependencyInjection\Compiler\UrlHistoryWriterPass;

class UmanitSeoBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new UrlHistoryWriterPass());
    }
}
