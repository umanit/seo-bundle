<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Tests\Handler\Schemable;

use PHPUnit\Framework\TestCase;
use TestApp\Entity\Schemable\WithHandlerEntity;
use Umanit\SeoBundle\Handler\Schemable\Schemable;

class SchemableTest extends TestCase
{
    public function testThrowLogicExceptionIfEntityNotSupported()
    {
        $schemable = new Schemable([]);

        $this->expectException('LogicException');

        $schemable->handle(new WithHandlerEntity());
    }
}
