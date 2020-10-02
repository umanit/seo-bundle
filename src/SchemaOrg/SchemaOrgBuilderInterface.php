<?php

namespace Umanit\SeoBundle\SchemaOrg;

use Spatie\SchemaOrg\BaseType;
use Umanit\SeoBundle\Model\SchemableModelInterface;

interface SchemaOrgBuilderInterface
{
    /**
     * Builds the schema.org
     *
     * @param SchemableModelInterface $entity
     *
     * @return BaseType
     */
    public function buildSchema(SchemableModelInterface $entity): BaseType;
}
