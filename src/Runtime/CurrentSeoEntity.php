<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Runtime;

use Umanit\SeoBundle\Model\RoutableModelInterface;

class CurrentSeoEntity
{
    /** @var RoutableModelInterface|null */
    private $value;

    public function get(): ?RoutableModelInterface
    {
        return $this->value;
    }

    public function set(RoutableModelInterface $value): void
    {
        $this->value = $value;
    }
}
