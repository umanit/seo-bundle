<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Breadcrumbable;

use Umanit\SeoBundle\Model\BreadcrumbableModelInterface;
use Umanit\SeoBundle\Model\BreadcrumbItem;

interface BreadcrumbableInterface
{
    /**
     * @param BreadcrumbableModelInterface $entity
     *
     * @return BreadcrumbItem[]
     */
    public function handle(BreadcrumbableModelInterface $entity): array;
}
