<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Breadcrumbable;

use Umanit\SeoBundle\Model\Breadcrumb;
use Umanit\SeoBundle\Model\BreadcrumbableModelInterface;

interface BreadcrumbableHandlerInterface
{
    /**
     * Does the handler supports this entity?
     */
    public function supports(BreadcrumbableModelInterface $entity): bool;

    /**
     * Should return a Breadcrumb object for the entity.
     */
    public function process(BreadcrumbableModelInterface $entity): Breadcrumb;
}
