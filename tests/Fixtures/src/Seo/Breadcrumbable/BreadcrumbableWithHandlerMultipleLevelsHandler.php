<?php

declare(strict_types=1);

namespace TestApp\Seo\Breadcrumbable;

use TestApp\Entity\Breadcrumbable\WithHandlerMultipleLevelsEntity;
use Umanit\SeoBundle\Handler\Breadcrumbable\BreadcrumbableHandlerInterface;
use Umanit\SeoBundle\Model\Breadcrumb;
use Umanit\SeoBundle\Model\BreadcrumbableModelInterface;
use Umanit\SeoBundle\Model\BreadcrumbItem;

class BreadcrumbableWithHandlerMultipleLevelsHandler implements BreadcrumbableHandlerInterface
{
    public function supports(BreadcrumbableModelInterface $entity): bool
    {
        return $entity instanceof WithHandlerMultipleLevelsEntity;
    }

    public function process(BreadcrumbableModelInterface $entity): Breadcrumb
    {
        $breadcrumb = new Breadcrumb();
        $breadcrumb->addItem(new BreadcrumbItem('Foo'));
        $breadcrumb->addItem(new BreadcrumbItem('Bar'));
        $breadcrumb->addItem(new BreadcrumbItem('Baz'));

        return $breadcrumb;
    }
}
