<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\SchemaOrg;

use Spatie\SchemaOrg\BaseType;
use Umanit\SeoBundle\Handler\Schemable\Schemable;
use Umanit\SeoBundle\Model\SchemableModelInterface;

class SchemaOrgBuilder implements SchemaOrgBuilderInterface
{
    public function __construct(
        private readonly Schemable $schemableHandler,
    ) {
    }

    public function buildSchema(SchemableModelInterface $entity): BaseType
    {
        return $this->schemableHandler->handle($entity);
    }
}
