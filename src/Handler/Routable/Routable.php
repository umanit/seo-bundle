<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Routable;

use Umanit\SeoBundle\Model\RoutableInterface as ModelRoutableInterface;
use Umanit\SeoBundle\Model\Route;

class Routable implements RoutableInterface
{
    /** @var RoutableHandlerInterface[] */
    private $handlers;

    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }

    public function handle(ModelRoutableInterface $entity): Route
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($entity)) {
                return $handler->process($entity);
            }
        }

        throw new \LogicException(sprintf('Can not determine the route of the entity %s', $entity));
    }
}
