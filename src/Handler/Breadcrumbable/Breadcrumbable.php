<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Breadcrumbable;

use Umanit\SeoBundle\Model\BreadcrumbableInterface as ModelBreadcrumbableInterface;

class Breadcrumbable implements BreadcrumbableInterface
{
    /** @var BreadcrumbableHandlerInterface[] */
    private $handlers;

    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }

    public function handle(ModelBreadcrumbableInterface $entity): array
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($entity)) {
                return $handler->process($entity);
            }
        }

        throw new \LogicException(sprintf(
            'Can not determine the breadcrumb items of the entity %s',
            \get_class($entity)
        ));
    }
}
