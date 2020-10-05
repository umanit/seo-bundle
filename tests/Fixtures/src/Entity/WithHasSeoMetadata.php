<?php

declare(strict_types=1);

namespace TestApp\Entity;

use Umanit\SeoBundle\Doctrine\Model\HistorizableUrlTrait;
use Umanit\SeoBundle\Doctrine\Model\SeoMetadataTrait;
use Umanit\SeoBundle\Model\HasSeoMetadataInterface;
use Umanit\SeoBundle\Model\HistorizableUrlModelInterface;

class WithHasSeoMetadata implements HasSeoMetadataInterface, HistorizableUrlModelInterface
{
    use SeoMetadataTrait;
    use HistorizableUrlTrait;
}
