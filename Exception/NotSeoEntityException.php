<?php

namespace Umanit\SeoBundle\Exception;

use Umanit\SeoBundle\Doctrine\Annotation\Seo;

/**
 * Class NotSeoEntityException
 *
 * Exception thrown when trying to do SEO related
 * operations on an entity not annotated as @Seo().
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class NotSeoEntityException extends \Exception
{
}