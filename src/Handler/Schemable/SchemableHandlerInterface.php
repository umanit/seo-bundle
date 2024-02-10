<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Schemable;

use Spatie\SchemaOrg\BaseType;
use Umanit\SeoBundle\Model\SchemableModelInterface;

interface SchemableHandlerInterface
{
    /**
     * Does the handler supports this entity?
     */
    public function supports(SchemableModelInterface $entity): bool;

    /**
     * Should return a BaseType object for the entity.
     */
    public function process(SchemableModelInterface $entity): BaseType;
}
