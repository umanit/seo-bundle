<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Tests\Handler\Breadcrumbable;

use PHPUnit\Framework\TestCase;
use TestApp\Entity\Breadcrumbable\WithHandlerEntity;
use Umanit\SeoBundle\Handler\Breadcrumbable\Breadcrumbable;

class BreadcrumbableTest extends TestCase
{
    public function testThrowLogicExceptionIfEntityNotSupported()
    {
        $breadcrumbable = new Breadcrumbable([]);

        $this->expectException('LogicException');

        $breadcrumbable->handle(new WithHandlerEntity());
    }
}
