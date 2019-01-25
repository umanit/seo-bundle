<?php

namespace Umanit\Bundle\TreeBundle\Model;

use Umanit\SeoBundle\Doctrine\Entity\SeoMetadata;
use Doctrine\ORM\Mapping as ORM;

/**
 * Contains data usable for Seo metadata.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
trait SeoMetadataTrait
{
    /**
     * @var SeoMetadata
     *
     * @ORM\Embedded(class="Umanit\Bundle\TreeBundle\Entity\SeoMetadata", columnPrefix="seo_meta_")
     */
    protected $seoMetadata;

    /**
     * Get the value of Seo Metadata.
     *
     * @return SeoMetadata
     */
    public function getSeoMetadata(): SeoMetadata
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
