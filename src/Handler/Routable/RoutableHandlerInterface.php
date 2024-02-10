<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Routable;

use Umanit\SeoBundle\Model\RoutableModelInterface;
use Umanit\SeoBundle\Model\Route;

interface RoutableHandlerInterface
{
    /**
     * Does the handler supports this entity?
     */
    public function supports(RoutableModelInterface $entity): bool;

    /**
     * Should return a Route object for the entity.
     */
    public function process(RoutableModelInterface $entity): Route;
}
