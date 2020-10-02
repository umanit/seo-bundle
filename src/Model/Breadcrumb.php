<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Model;

class Breadcrumb
{
    public const FORMAT_MICRODATA = 'microdata';
    public const FORMAT_JSON_LD = 'json-ld';
    public const FORMAT_RDFA = 'rdfa';

    /** @var string */
    private $format;

    /** @var string */
    private $template;

    /** @var BreadcrumbItem[] */
    private $items;

    public function __construct(string $format = self::FORMAT_MICRODATA)
    {
        $this->format = $format;
        $this->items = [];

        $this->setTemplateFromFormat();
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;

        $this->setTemplateFromFormat();
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function addItem(BreadcrumbItem $item): void
    {
        $this->items[] = $item;
    }

    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    private function setTemplateFromFormat(): void
    {
        $this->template = 'breadcrumb_'.str_replace('-', '_', $this->getFormat());
    }
}
