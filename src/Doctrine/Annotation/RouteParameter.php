<?php

namespace Umanit\SeoBundle\Doctrine\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Umanit\SeoBundle\Model\AutoSetterConstructorTrait;

/**
 * @Annotation
 *
 * Annotation class for @RouteParameter().
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class RouteParameter
{
    use AutoSetterConstructorTrait;

    /**
     * Route parameter name.
     * eg: in "/blog/{slug}", the parameter name is 'slug'.
     *
     * @var string
     * @Required()
     */
    private $parameter;

    /**
     * Entity property name or expression.
     * eg: "slug" for $blog->slug
     *
     * @var string
     * @Required()
     */
    private $property;

    /**
     * @return string
     */
    public function getParameter(): ?string
    {
        return $this->parameter;
    }

    /**
     * @param string $parameter
     *
     * @return self
     */
    public function setParameter(?string $parameter): self
    {
        $this->parameter = $parameter;

        return $this;
    }

    /**
     * @return string
     */
    public function getProperty(): ?string
    {
        return $this->property;
    }

    /**
     * @param string $property
     *
     * @return self
     */
    public function setProperty(?string $property): self
    {
        $this->property = $property;

        return $this;
    }
}