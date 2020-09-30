<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Breadcrumbable;

use Umanit\SeoBundle\Entity\BreadcrumbableEntityInterface;
use Umanit\SeoBundle\Model\BreadcrumbItem;

interface BreadcrumbableHandlerInterface
{
    /**
     * Does the handler supports this entity?
     *
     * @param BreadcrumbableEntityInterface $entity
     *
     * @return bool
     */
    public function supports(BreadcrumbableEntityInterface $entity): bool;

    /**
     * Should returns a list of BreadcrumbItem object for the entity.
     *
     * @param BreadcrumbableEntityInterface $entity
     *
     * @return BreadcrumbItem[]
     */
    public function process(BreadcrumbableEntityInterface $entity): array;
}
