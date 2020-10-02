<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Schemable;

use Spatie\SchemaOrg\BaseType;
use Umanit\SeoBundle\Model\SchemableModelInterface;

interface SchemableHandlerInterface
{
    /**
     * Does the handler supports this entity?
     *
     * @param SchemableModelInterface $entity
     *
     * @return bool
     */
    public function supports(SchemableModelInterface $entity): bool;

    /**
     * Should returns a BaseType object for the entity.
     *
     * @param SchemableModelInterface $entity
     *
     * @return BaseType
     */
    public function process(SchemableModelInterface $entity): BaseType;
}
