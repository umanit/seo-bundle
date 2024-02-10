<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Handler\Schemable;

use Spatie\SchemaOrg\BaseType;
use Umanit\SeoBundle\Model\SchemableModelInterface;

class Schemable implements SchemableInterface
{
    public function __construct(
        /** @var SchemableHandlerInterface[] */
        private readonly iterable $handlers
    ) {
    }

    public function handle(SchemableModelInterface $entity): BaseType
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($entity)) {
                return $handler->process($entity);
            }
        }

        throw new \LogicException(sprintf('Can not determine the schema.org of the entity %s', $entity::class));
    }
}
