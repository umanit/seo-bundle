<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Breadcrumbable;

use Umanit\SeoBundle\Model\Breadcrumb;
use Umanit\SeoBundle\Model\BreadcrumbableModelInterface;

class Breadcrumbable implements BreadcrumbableInterface
{
    public function __construct(
        /** @var BreadcrumbableHandlerInterface[] */
        private readonly iterable $handlers
    ) {
    }

    public function handle(BreadcrumbableModelInterface $entity): Breadcrumb
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($entity)) {
                return $handler->process($entity);
            }
        }

        throw new \LogicException(sprintf('Can not determine the breadcrumb of the entity %s', $entity::class));
    }
}
