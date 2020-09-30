<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Model;

class Route
{
    /** @var string */
    private $name;

    /** @var array|null */
    private $parameters;

    public function __construct(string $name, array $parameters = null)
    {
        $this->name = $name;
        $this->parameters = $parameters;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    public function setParameters(?array $parameters): void
    {
        $this->parameters = $parameters;
    }
}
