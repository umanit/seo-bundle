<?php

namespace Umanit\Bundle\TreeBundle\Model;

use Umanit\SeoBundle\Doctrine\Entity\SeoMetadata;

/**
 * Class HasSeoMetadataInterface
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
interface HasSeoMetadataInterface
{
    /**
     * Get the value of Seo Metadata.
     *
     * @return SeoMetadata
     */
    public function getSeoMetadata(): SeoMetadata;

    /**
     * Set the value of Seo Metadata.
     *
     * @param SeoMetadata $seoMetadata
     *
     * @return self
     */
    public function setSeoMetadata(SeoMetadata $seoMetadata): HasSeoMetadataInterface;
}
