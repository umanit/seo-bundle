<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Routable;

use Umanit\SeoBundle\Model\RoutableModelInterface;
use Umanit\SeoBundle\Model\Route;

class Routable implements RoutableInterface
{
    public function __construct(
        /** @var RoutableHandlerInterface[] */
        private readonly iterable $handlers
    ) {
    }

    public function handle(RoutableModelInterface $entity): Route
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($entity)) {
                return $handler->process($entity);
            }
        }

        throw new \LogicException(sprintf('Can not determine the route of the entity %s', $entity::class));
    }
}
