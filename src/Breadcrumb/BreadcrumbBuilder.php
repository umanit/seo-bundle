<?php

namespace Umanit\SeoBundle\Breadcrumb;

use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Umanit\SeoBundle\Doctrine\Annotation\Breadcrumb;
use Umanit\SeoBundle\Doctrine\Annotation\BreadcrumbItem;
use Umanit\SeoBundle\Model\AnnotationReaderTrait;
use Umanit\SeoBundle\Routing\Canonical;

/**
 * Class BreadcrumbBuilder
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class BreadcrumbBuilder
{
    use AnnotationReaderTrait;

    /** @var Environment */
    private $twig;

    /** @var PropertyAccessorInterface */
    private $propAccess;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /** @var Canonical */
    private $canonical;

    /** @var array */
    private $templates;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        Environment $twig,
        PropertyAccessorInterface $propAccess,
        UrlGeneratorInterface $urlGenerator,
        Canonical $canonical,
        TranslatorInterface $translator,
        array $templates
    ) {
        $this->twig = $twig;
        $this->propAccess = $propAccess;
        $this->urlGenerator = $urlGenerator;
        $this->canonical = $canonical;
        $this->templates = $templates;
        $this->translator = $translator;
    }

    /**
     * Builds a breadcrumb.
     *
     * @param object|null $entity
     * @param string      $format
     *
     * @return false|string
     * @throws \ErrorException
     * @throws \ReflectionException
     * @throws \Twig\Error\Error
     * @throws \Umanit\SeoBundle\Exception\NotBreadcrumbEntityException
     * @throws \Umanit\SeoBundle\Exception\NotSeoRouteEntityException
     */
    public function buildBreadcrumb(?object $entity, $format = null)
    {
        if (null !== $format && !\in_array(strtolower($format), Breadcrumb::FORMATS, true)) {
            throw new \ErrorException(sprintf('Invalid format "%s". Valid formats are %s', $format, implode('", "', Breadcrumb::FORMATS)));
        }

        $breadcrumbAnnotation = $this->getBreadcrumbAnnotation($entity);
        $items = [];
        $i = 0;

        foreach ($breadcrumbAnnotation->getValue() as $breadcrumbItem) {
            /** @var BreadcrumbItem $breadcrumbItem */
            $value = $breadcrumbItem->getValue();

            if (null === $value) {
                $items[$i]['url'] = $this->canonical->url($entity);
            } else {
                // Try and generate the url from a property
                try {
                    $value = $this->propAccess->getValue($entity, $value);
                    $items[$i]['url'] = $this->canonical->url($value);
                } catch (NoSuchPropertyException $e) {
                    // Try and generate a url from a route
                    $items[$i]['url'] = $this->urlGenerator->generate($value, [], UrlGeneratorInterface::ABSOLUTE_URL);
                }
            }

            // Try and generate the name from a property
            try {
                $nameValue = $this->propAccess->getValue($entity, $breadcrumbItem->getName());
                $items[$i]['name'] = $nameValue;
            } catch (NoSuchPropertyException $e) {
                $items[$i]['name'] = $this->translator->trans($breadcrumbItem->getName());
            }

            $i++;
        }

        $template = $this->templates['breadcrumb_'.str_replace('-', '_', $format ?? $breadcrumbAnnotation->getFormat())];

        return $this->twig->render($template, [
            'items' => $items,
        ]);
    }
}
