<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Routable;

use Umanit\SeoBundle\Model\RoutableModelInterface;
use Umanit\SeoBundle\Model\Route;

interface RoutableHandlerInterface
{
    /**
     * Does the handler supports this entity?
     *
     * @param RoutableModelInterface $entity
     *
     * @return bool
     */
    public function supports(RoutableModelInterface $entity): bool;

    /**
     * Should returns a Route object for the entity.
     *
     * @param RoutableModelInterface $entity
     *
     * @return Route
     */
    public function process(RoutableModelInterface $entity): Route;
}
