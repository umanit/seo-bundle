<?php

namespace Umanit\SeoBundle\Model;

/**
 * Trait AutoSetterConstructorTrait
 *
 * Trait used to automatically set
 * values from annotation constructor.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
trait AutoSetterConstructorTrait
{
    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $method = 'set'.str_replace('_', '', $key);
            if (!method_exists($this, $method)) {
                throw new \BadMethodCallException(sprintf('Unknown property "%s" on annotation "%s".', $key, \get_class($this)));
            }
            $this->$method($value);
        }
    }
}