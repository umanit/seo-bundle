<?php

namespace Umanit\SeoBundle\Breadcrumb;

use Twig\Environment;
use Umanit\SeoBundle\Handler\Breadcrumbable\BreadcrumbableInterface;
use Umanit\SeoBundle\Model\Breadcrumb;
use Umanit\SeoBundle\Model\BreadcrumbableModelInterface;
use Umanit\SeoBundle\Model\RoutableModelInterface;
use Umanit\SeoBundle\Routing\Canonical;

class BreadcrumbBuilder
{
    /** @var Environment */
    private $twig;

    /** @var Canonical */
    private $canonical;

    /** @var BreadcrumbableInterface */
    private $breadcrumbableHandler;

    /** @var array */
    private $templates;

    public function __construct(
        Environment $twig,
        Canonical $canonical,
        BreadcrumbableInterface $breadcrumbableHandler,
        array $templates
    ) {
        $this->twig = $twig;
        $this->canonical = $canonical;
        $this->breadcrumbableHandler = $breadcrumbableHandler;
        $this->templates = $templates;
    }

    public function buildBreadcrumb(BreadcrumbableModelInterface $entity, string $format = null): string
    {
        if (null !== $format && !\in_array(strtolower($format), Breadcrumb::FORMATS, true)) {
            throw new \ErrorException(sprintf(
                'Invalid format "%s". Valid formats are %s',
                $format,
                implode(', ', Breadcrumb::FORMATS)
            ));
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
