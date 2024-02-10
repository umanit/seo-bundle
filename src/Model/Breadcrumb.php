<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Model;

class Breadcrumb
{
    public const FORMAT_MICRODATA = 'microdata';
    public const FORMAT_JSON_LD = 'json-ld';
    public const FORMAT_RDFA = 'rdfa';
    public const FORMATS = [self::FORMAT_JSON_LD, self::FORMAT_MICRODATA, self::FORMAT_RDFA];

    /** @var string */
    private string $template;

    /** @var array<int, BreadcrumbItem> */
    private array $items = [];

    public function __construct(
        private string $format = self::FORMAT_MICRODATA,
    ) {
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
        $this->template = 'breadcrumb_' . str_replace('-', '_', $this->getFormat());
    }
}
