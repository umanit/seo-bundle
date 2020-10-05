<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Service;

use Symfony\Component\Routing\RouterInterface;

trait RouterAwareTrait
{
    /** @var RouterInterface */
    private $router;

    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }
}
