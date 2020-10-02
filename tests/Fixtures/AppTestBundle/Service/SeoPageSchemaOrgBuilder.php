<?php

namespace AppTestBundle\Service;

use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\Schema;
use Umanit\SeoBundle\SchemaOrg\SchemaOrgBuilderInterface;

/**
 * Class SeoPageSchemaOrgBuilder
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class SeoPageSchemaOrgBuilder implements SchemaOrgBuilderInterface
{
    public function buildSchema(object $entity): BaseType
    {
        return
            Schema::localBusiness()
                  ->name('Test')
                  ->email('test@umanit.fr')
                  ->contactPoint(Schema::contactPoint()->areaServed('Worldwide'))
            ;
    }
}
