<?php

namespace Umanit\SeoBundle\Runtime;

use Umanit\SeoBundle\Doctrine\Annotation\Route;

/**
 * Class CurrentSeoEntity
 *
 * Represents the currently requested
 * Seo @Route() entity on the current route.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class CurrentSeoEntity
{
    /** @var object|null */
    private $value;

    /**
     * @return object|null
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * @param $value
     */
    public function set($value): void
    {
        $this->value = $value;
    }
}
