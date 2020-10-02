<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Breadcrumbable;

use Umanit\SeoBundle\Model\BreadcrumbableModelInterface;
use Umanit\SeoBundle\Model\BreadcrumbItem;

interface BreadcrumbableHandlerInterface
{
    /**
     * Does the handler supports this entity?
     *
     * @param BreadcrumbableModelInterface $entity
     *
     * @return bool
     */
    public function supports(BreadcrumbableModelInterface $entity): bool;

    /**
     * Should returns a list of BreadcrumbItem object for the entity.
     *
     * @param BreadcrumbableModelInterface $entity
     *
     * @return BreadcrumbItem[]
     */
    public function process(BreadcrumbableModelInterface $entity): array;
}
