<?php

namespace Umanit\SeoBundle\Model;

use Umanit\SeoBundle\Entity\SeoMetadata;

interface HasSeoMetadataInterface
{
    public function getSeoMetadata(): ?SeoMetadata;

    public function setSeoMetadata(SeoMetadata $seoMetadata): HasSeoMetadataInterface;
}
