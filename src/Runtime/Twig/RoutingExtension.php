<?php

namespace Umanit\SeoBundle\Runtime\Twig;

use Symfony\Bridge\Twig\Extension\RoutingExtension as BaseRoutingExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Umanit\SeoBundle\Exception\NotSeoRouteEntityException;
use Umanit\SeoBundle\Routing\Canonical;

/**
 * Class RoutingExtension
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class RoutingExtension extends BaseRoutingExtension
{
    /** @var Canonical */
    private $canonical;

    public function __construct(UrlGeneratorInterface $generator, Canonical $canonical)
    {
        $this->canonical = $canonical;
        parent::__construct($generator);
    }

    public function getPath($name, $parameters = [], $relative = false)
    {
        try {
            return $this->canonical->path($name, $parameters);
        } catch (NotSeoRouteEntityException $e) {
            return parent::getPath($name, $parameters, $relative);
        }
    }

    public function getUrl($name, $parameters = [], $schemeRelative = false)
    {
        try {
            return $this->canonical->url($name, $parameters);
        } catch (NotSeoRouteEntityException $e) {
            return parent::getPath($name, $parameters, $schemeRelative);
        }
    }

}
