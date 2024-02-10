<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Service;

use Symfony\Component\Routing\RouterInterface;

trait RouterAwareTrait
{
    private RouterInterface $router;

    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }
}
