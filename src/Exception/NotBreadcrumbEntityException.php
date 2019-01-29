<?php

namespace Umanit\SeoBundle\Exception;

use Umanit\SeoBundle\Doctrine\Annotation\Breadcrumb;

/**
 * Class NotBreadcrumbEntityException
 *
 * Exception thrown when trying to do Breadcrumb related
 * operations on an entity not annotated as @Breadcrumb().
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class NotBreadcrumbEntityException extends \ErrorException
{
}
