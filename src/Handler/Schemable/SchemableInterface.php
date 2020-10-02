<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Schemable;

use Spatie\SchemaOrg\BaseType;
use Umanit\SeoBundle\Model\SchemableModelInterface;

interface SchemableInterface
{
    public function handle(SchemableModelInterface $entity): BaseType;
}
