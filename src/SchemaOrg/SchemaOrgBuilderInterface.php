<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\SchemaOrg;

use Spatie\SchemaOrg\BaseType;
use Umanit\SeoBundle\Model\SchemableModelInterface;

interface SchemaOrgBuilderInterface
{
    /**
     * Builds the schema.org
     */
    public function buildSchema(SchemableModelInterface $entity): BaseType;
}
