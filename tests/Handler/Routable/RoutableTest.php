<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Tests\Handler\Routable;

use PHPUnit\Framework\TestCase;
use TestApp\Entity\Routable\WithHandlerEntity;
use Umanit\SeoBundle\Handler\Routable\Routable;

class RoutableTest extends TestCase
{
    public function testThrowLogicExceptionIfEntityNotSupported(): void
    {
        $routable = new Routable([]);

        $this->expectException('LogicException');

        $routable->handle(new WithHandlerEntity());
    }
}
