<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Doctrine\Model;

use Doctrine\ORM\Mapping as ORM;
use Umanit\SeoBundle\Entity\SeoMetadata;
use Umanit\SeoBundle\Model\HasSeoMetadataInterface;

trait SeoMetadataTrait
{
    /**
     * @var SeoMetadata
     *
     * @ORM\Embedded(class="Umanit\SeoBundle\Entity\SeoMetadata", columnPrefix="seo_meta_")
     */
    protected $seoMetadata;

    /**
     * Get the value of Seo Metadata.
     *
     * @return SeoMetadata
     */
    public function getSeoMetadata(): ?SeoMetadata
    {
        return $this->seoMetadata;
    }

    /**
     * Set the value of Seo Metadata.
     *
     * @param SeoMetadata $seoMetadata
     *
     * @return HasSeoMetadataInterface
     */
    public function setSeoMetadata(SeoMetadata $seoMetadata): HasSeoMetadataInterface
    {
        $this->seoMetadata = $seoMetadata;

        return $this;
    }
}
