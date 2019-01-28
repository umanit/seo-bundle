<?php

namespace Umanit\SeoBundle\Doctrine\Annotation;

use Umanit\SeoBundle\Model\AutoSetterConstructorTrait;

/**
 * @Annotation
 * @Target("CLASS")
 *
 * Annotation class for @SchemaOrgBuilder().
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class SchemaOrgBuilder
{
    use AutoSetterConstructorTrait;

    /**
     * Schema builder service id or entity method.
     *
     * @var string
     * @Required()
     */
    private $value;

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
}
