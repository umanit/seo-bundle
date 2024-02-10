<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Doctrine\Model;

use Doctrine\ORM\Mapping as ORM;
use Umanit\SeoBundle\Entity\SeoMetadata;
use Umanit\SeoBundle\Model\HasSeoMetadataInterface;

trait SeoMetadataTrait
{
    /**
     * @ORM\Embedded(class="Umanit\SeoBundle\Entity\SeoMetadata", columnPrefix="seo_meta_")
     */
    #[ORM\Embedded(class: SeoMetadata::class, columnPrefix: 'seo_meta_')]
    protected ?SeoMetadata $seoMetadata = null;

    public function getSeoMetadata(): ?SeoMetadata
    {
        return $this->seoMetadata;
    }

    public function setSeoMetadata(?SeoMetadata $seoMetadata): HasSeoMetadataInterface
    {
        $this->seoMetadata = $seoMetadata;

        return $this;
    }
}
