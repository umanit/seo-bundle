<?php

namespace Umanit\SeoBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Umanit\SeoBundle\Tests\App\AppKernel;

class WebTestCase extends BaseWebTestCase
{
    protected static function getKernelClass()
    {
        return AppKernel::class;
    }
}
