<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Routable;

use Umanit\SeoBundle\Entity\RoutableEntityInterface;
use Umanit\SeoBundle\Model\Route;

class Routable
{
    /** @var SeoCapableHandlerInterface[] */
    private $handlers;

    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }

    public function getRoute(RoutableEntityInterface $entity): Route
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($entity)) {
                return $handler->process($entity);
            }
        }

        throw new \LogicException(sprintf('Can not determine the route of the entity %s', $entity));
    }
}
