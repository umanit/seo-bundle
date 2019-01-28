<?php

namespace Umanit\SeoBundle\Exception;

use Umanit\SeoBundle\Doctrine\Annotation\Route;

/**
 * Class NotSeoRouteEntityException
 *
 * Exception thrown when trying to do SEO related
 * operations on an entity not annotated as @Route().
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class NotSeoRouteEntityException extends \ErrorException
{
}
