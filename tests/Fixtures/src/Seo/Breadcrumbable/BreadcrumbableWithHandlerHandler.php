<?php

declare(strict_types=1);

namespace TestApp\Seo\Breadcrumbable;

use TestApp\Entity\Breadcrumbable\WithHandlerEntity;
use Umanit\SeoBundle\Handler\Breadcrumbable\BreadcrumbableHandlerInterface;
use Umanit\SeoBundle\Model\Breadcrumb;
use Umanit\SeoBundle\Model\BreadcrumbableModelInterface;
use Umanit\SeoBundle\Model\BreadcrumbItem;

class BreadcrumbableWithHandlerHandler implements BreadcrumbableHandlerInterface
{
    public function supports(BreadcrumbableModelInterface $entity): bool
    {
        return $entity instanceof WithHandlerEntity;
    }

    public function process(BreadcrumbableModelInterface $entity): Breadcrumb
    {
        $breadcrumb = new Breadcrumb();
        $breadcrumb->addItem(new BreadcrumbItem('Hello World!'));

        return $breadcrumb;
    }
}
