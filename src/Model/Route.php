<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Model;

class Route
{
    public function __construct(
        private readonly string $name,
        private array $parameters = [],
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }
}
