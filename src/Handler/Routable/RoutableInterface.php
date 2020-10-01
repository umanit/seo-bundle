<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Routable;

use Umanit\SeoBundle\Model\Route;

interface RoutableInterface
{
    public function handle(\Umanit\SeoBundle\Model\RoutableInterface $entity): Route;
}
