<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Breadcrumbable;

use Umanit\SeoBundle\Model\BreadcrumbableInterface;
use Umanit\SeoBundle\Model\BreadcrumbItem;

class Breadcrumbable
{
    /** @var BreadcrumbableHandlerInterface[] */
    private $handlers;

    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * @param BreadcrumbableInterface $entity
     *
     * @return BreadcrumbItem[]
     */
    public function getItems(BreadcrumbableInterface $entity): array
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($entity)) {
                return $handler->process($entity);
            }
        }

        throw new \LogicException(sprintf('Can not determine the breadcrumb items of the entity %s', $entity));
    }
}
