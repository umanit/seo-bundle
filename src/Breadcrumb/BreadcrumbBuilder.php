<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Breadcrumb;

use Twig\Environment;
use Umanit\SeoBundle\Handler\Breadcrumbable\BreadcrumbableInterface;
use Umanit\SeoBundle\Model\Breadcrumb;
use Umanit\SeoBundle\Model\BreadcrumbableModelInterface;
use Umanit\SeoBundle\Model\RoutableModelInterface;
use Umanit\SeoBundle\Routing\Canonical;

class BreadcrumbBuilder
{
    public function __construct(
        private readonly Environment $twig,
        private readonly Canonical $canonical,
        private readonly BreadcrumbableInterface $breadcrumbableHandler,
        private readonly array $templates,
    ) {
    }

    public function buildBreadcrumb(BreadcrumbableModelInterface $entity, string $format = null): string
    {
        if (null !== $format && !\in_array(strtolower($format), Breadcrumb::FORMATS, true)) {
            throw new \ErrorException(
                sprintf(
                    'Invalid format "%s". Valid formats are %s',
                    $format,
                    implode(', ', Breadcrumb::FORMATS)
                )
            );
        }

        $breadcrumb = $this->breadcrumbableHandler->handle($entity);
        $items = [];

        if (null !== $format) {
            $breadcrumb->setFormat($format);
        }

        foreach ($breadcrumb->getItems() as $breadcrumbItem) {
            $url = $breadcrumbItem->getUrl();

            $item = ['name' => $breadcrumbItem->getLabel()];

            if (null !== $url) {
                $item['url'] = $url;
            } elseif ($entity instanceof RoutableModelInterface) {
                $item['url'] = $this->canonical->url($entity);
            }

            $items[] = $item;
        }

        $template = $this->templates[$breadcrumb->getTemplate()];

        return $this->twig->render($template, [
            'items' => $items,
        ]);
    }
}
