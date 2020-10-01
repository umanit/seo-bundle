<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Breadcrumbable;

use Umanit\SeoBundle\Model\BreadcrumbItem;

interface BreadcrumbableInterface
{
    /**
     * @param \Umanit\SeoBundle\Model\BreadcrumbableInterface $entity
     *
     * @return BreadcrumbItem[]
     */
    public function handle(\Umanit\SeoBundle\Model\BreadcrumbableInterface $entity): array;
}
