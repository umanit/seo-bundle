<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Service;

use Symfony\Component\Routing\RouterInterface;

interface RouterAwareInterface
{
    public function setRouter(RouterInterface $router): void;
}
