<?php

namespace Umanit\SeoBundle\SchemaOrg;

/**
 * Class SchemaBuilderArgs
 *
 * Argument passed to SchemaBuilderInterface::buildSchema
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class SchemaBuilderArgs
{
    /**
     * @var object
     *
     * The entity from which the schema will be generated.
     */
    private $entity;

    /**
     * SchemaBuilderArgs constructor.
     *
     * @param object $entity
     */
    public function __construct(object $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return object
     */
    public function getEntity(): ?object
    {
        return $this->entity;
    }
}
