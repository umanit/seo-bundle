<?php

namespace Umanit\SeoBundle\Exception;

use Umanit\SeoBundle\Doctrine\Annotation\SchemaOrgBuilder;

/**
 * Class NotSchemaOrgEntityException
 *
 * Exception thrown when trying to generate a json
 * schema from an entity not annotated by @SchemaOrgBuilder().
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class NotSchemaOrgEntityException extends \ErrorException
{
}
