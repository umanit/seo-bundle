<?php

namespace Umanit\SeoBundle\Doctrine\Annotation;

use Umanit\SeoBundle\Model\AutoSetterConstructorTrait;

/**
 * @Annotation
 *
 * Annotation class for @BreadcrumbItem().
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class BreadcrumbItem
{
    use AutoSetterConstructorTrait;

    /**
     * Value can be either a route name or, the
     * path to a child entity already annotated
     * with @Seo\Route. If no value is passed, a
     * link to the current entity is generated.
     *
     * @var string
     */
    private $value;

    /**
     * The name can be either a plain
     * string or the path to an attribute.
     *
     * @var string
     */
    private $name;

    /**
     * @return string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
