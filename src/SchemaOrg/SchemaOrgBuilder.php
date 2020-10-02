<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\SchemaOrg;

use Spatie\SchemaOrg\BaseType;
use Umanit\SeoBundle\Handler\Schemable\Schemable;
use Umanit\SeoBundle\Model\SchemableModelInterface;

class SchemaOrgBuilder implements SchemaOrgBuilderInterface
{
    /** @var Schemable */
    private $schemableHandler;

    public function __construct(Schemable $schemableHandler)
    {
        $this->schemableHandler = $schemableHandler;
    }

    public function buildSchema(SchemableModelInterface $entity): BaseType
    {
        return $this->schemableHandler->handle($entity);
    }
}
