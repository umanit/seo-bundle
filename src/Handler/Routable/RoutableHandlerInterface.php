<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Routable;

use Umanit\SeoBundle\Model\RoutableInterface;
use Umanit\SeoBundle\Model\Route;

interface RoutableHandlerInterface
{
    /**
     * Does the handler supports this entity?
     *
     * @param RoutableInterface $entity
     *
     * @return bool
     */
    public function supports(RoutableInterface $entity): bool;

    /**
     * Should returns a Route object for the entity.
     *
     * @param RoutableInterface $entity
     *
     * @return Route
     */
    public function process(RoutableInterface $entity): Route;
}
