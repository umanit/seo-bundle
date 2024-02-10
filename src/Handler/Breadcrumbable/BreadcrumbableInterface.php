<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Breadcrumbable;

use Umanit\SeoBundle\Model\Breadcrumb;
use Umanit\SeoBundle\Model\BreadcrumbableModelInterface;

interface BreadcrumbableInterface
{
    public function handle(BreadcrumbableModelInterface $entity): Breadcrumb;
}
