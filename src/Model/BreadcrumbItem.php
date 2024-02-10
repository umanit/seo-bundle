<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Model;

class BreadcrumbItem
{
    public function __construct(
        private readonly string $label,
        private ?string $url = null,
    ) {
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }
}
