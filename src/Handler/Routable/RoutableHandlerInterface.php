<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Routable;

use Umanit\SeoBundle\Entity\RoutableEntityInterface;
use Umanit\SeoBundle\Model\Route;

interface RoutableHandlerInterface
{
    /**
     * Does the handler supports this entity?
     *
     * @param RoutableEntityInterface $entity
     *
     * @return bool
     */
    public function supports(RoutableEntityInterface $entity): bool;

    /**
     * Should returns a Route object for the entity.
     *
     * @param RoutableEntityInterface $entity
     *
     * @return Route
     */
    public function process(RoutableEntityInterface $entity): Route;
}
