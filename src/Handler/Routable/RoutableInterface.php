<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Routable;

use Umanit\SeoBundle\Model\RoutableModelInterface;
use Umanit\SeoBundle\Model\Route;

interface RoutableInterface
{
    public function handle(RoutableModelInterface $entity): Route;
}
