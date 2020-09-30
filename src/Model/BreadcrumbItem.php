<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Model;

class BreadcrumbItem
{
    /** @var string */
    private $label;

    /** @var string|null */
    private $url;

    public function __construct(string $label, string $url = null)
    {
        $this->label = $label;
        $this->url = $url;
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
