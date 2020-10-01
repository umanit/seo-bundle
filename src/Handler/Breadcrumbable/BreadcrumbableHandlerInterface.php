<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Breadcrumbable;

use Umanit\SeoBundle\Model\BreadcrumbableInterface;
use Umanit\SeoBundle\Model\BreadcrumbItem;

interface BreadcrumbableHandlerInterface
{
    /**
     * Does the handler supports this entity?
     *
     * @param BreadcrumbableInterface $entity
     *
     * @return bool
     */
    public function supports(BreadcrumbableInterface $entity): bool;

    /**
     * Should returns a list of BreadcrumbItem object for the entity.
     *
     * @param BreadcrumbableInterface $entity
     *
     * @return BreadcrumbItem[]
     */
    public function process(BreadcrumbableInterface $entity): array;
}
