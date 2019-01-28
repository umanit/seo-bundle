<?php

namespace Umanit\SeoBundle\SchemaOrg;

use Spatie\SchemaOrg\BaseType;

/**
 * Class SchemaBuilderInterface
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
interface SchemaBuilderInterface
{
    /**
     * Builds the schema.org
     *
     * @param object $entity
     *
     * @return BaseType
     */
    public function buildSchema(object $entity): BaseType;
}
