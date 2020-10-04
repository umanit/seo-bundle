<?php

declare(strict_types=1);

namespace TestApp\Entity\Breadcrumbable;

use Umanit\SeoBundle\Model\BreadcrumbableModelInterface;
use Umanit\SeoBundle\Model\RoutableModelInterface;

class WithHandlerMultipleLevelsEntity implements BreadcrumbableModelInterface, RoutableModelInterface
{
}
