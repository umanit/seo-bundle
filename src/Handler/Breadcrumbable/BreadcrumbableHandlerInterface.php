<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Breadcrumbable;

use Umanit\SeoBundle\Model\Breadcrumb;
use Umanit\SeoBundle\Model\BreadcrumbableModelInterface;

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
     * Should returns a Breadcrumb object for the entity.
     *
     * @param BreadcrumbableModelInterface $entity
     *
     * @return Breadcrumb
     */
    public function process(BreadcrumbableModelInterface $entity): Breadcrumb;
}
