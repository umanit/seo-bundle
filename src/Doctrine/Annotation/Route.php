<?php

namespace Umanit\SeoBundle\Doctrine\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;
use Umanit\SeoBundle\Model\AutoSetterConstructorTrait;

/**
 * @Annotation
 * @Target("CLASS")
 *
 * Annotation class for @Route().
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class Route
{
    use AutoSetterConstructorTrait;

    /**
     * @var string
     * @Required()
     */
    private $routeName;

    /**
     * @var array<RouteParameter>
     */
    private $routeParameters = [];

    /**
     * @return string
     */
    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    /**
     * @param string $routeName
     *
     * @return self
     */
    public function setRouteName(?string $routeName): self
    {
        $this->routeName = $routeName;

        return $this;
    }

    /**
     * @return array<RouteParameter>
     */
    public function getRouteParameters(): ?array
    {
        return $this->routeParameters;
    }

    /**
     * @param array $routeParameters
     *
     * @return self
     */
    public function setRouteParameters(?array $routeParameters): self
    {
        $this->routeParameters = $routeParameters;

        return $this;
    }
}
