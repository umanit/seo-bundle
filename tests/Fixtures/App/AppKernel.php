<?php

namespace Umanit\SeoBundle\Tests\App;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Umanit\SeoBundle\UmanitSeoBundle;

/**
 * The kernel used in the application of most functional tests.
 */
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new UmanitSeoBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config.yaml');
    }


    /**
     * @return string
     */
    public function getCacheDir()
    {
        return __DIR__.'/../../../build/cache/'.$this->getEnvironment();
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return __DIR__.'/../../../build/kernel_logs/'.$this->getEnvironment();
    }
}
