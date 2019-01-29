<?php

namespace Umanit\SeoBundle\Doctrine\Annotation;

use Umanit\SeoBundle\Model\AutoSetterConstructorTrait;

/**
 * @Annotation
 * @Target("CLASS")
 *
 * Annotation class for @Breadcrumb().
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class Breadcrumb
{
    use AutoSetterConstructorTrait;

    public const
        FORMAT_MICRODATA = 'microdata',
        FORMAT_JSON_LD = 'json-ld',
        FORMAT_RDFA = 'rdfa';

    public const FORMATS = [self::FORMAT_JSON_LD, self::FORMAT_MICRODATA, self::FORMAT_RDFA];

    /**
     * @var array<BreadcrumbItem>
     *
     * @Required
     */
    private $value;

    /**
     * Template used to render the breadcrumb.
     * Default is %umanit_seo.breadcrumb_template%.
     *
     * @var string
     * @Required()
     */
    private $template;

    /**
     * The format to display the breadcrumb as
     * defined by https://schema.org/BreadcrumbList
     *
     * @Enum(\Umanit\SeoBundle\Doctrine\Annotation::FORMATS)
     */
    private $format = 'microdata';

    /**
     * @return array
     */
    public function getValue(): ?array
    {
        return $this->value;
    }

    /**
     * @param array $value
     *
     * @return self
     */
    public function setValue(?array $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @param string $template
     *
     * @return self
     */
    public function setTemplate(?string $template): self
    {
        $this->template = strtolower($template);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param mixed $format
     *
     * @return self
     */
    public function setFormat($format): self
    {
        $this->format = $format;

        return $this;
    }
}
