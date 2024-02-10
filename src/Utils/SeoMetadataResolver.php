<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Utils;

use Umanit\SeoBundle\Entity\SeoMetadata;
use Umanit\SeoBundle\Model\HasSeoMetadataInterface;
use Umanit\SeoBundle\Utils\EntityParser\EntityParserInterface;

/**
 * Resolves Seo metadata from an entity.
 */
class SeoMetadataResolver
{
    public function __construct(
        private readonly EntityParserInterface $title,
        private readonly EntityParserInterface $description,
        private readonly array $metadataConfig,
    ) {
    }

    /**
     * Returns the meta title of an entity.
     */
    public function metaTitle(?object $entity, bool $includePrefix = true, bool $includeSuffix = true): string
    {
        $title = $this->meta($entity, 'title');

        if ($includePrefix) {
            $title = $this->metadataConfig['title_prefix'] . $title;
        }

        if ($includeSuffix) {
            $title .= $this->metadataConfig['title_suffix'];
        }

        return $title;
    }

    /**
     * Returns the meta description of an entity.
     */
    public function metaDescription(?object $entity): string
    {
        return $this->meta($entity, 'description');
    }

    private function meta(?object $entity, string $metatype): string
    {
        if (!\is_object($entity)) {
            return $this->metadataConfig['default_' . $metatype];
        }

        if (
            $entity instanceof HasSeoMetadataInterface
            && $entity->getSeoMetadata() instanceof SeoMetadata
            && null !== $entity->getSeoMetadata()->{'get' . ucfirst($metatype)}()
        ) {
            return $entity->getSeoMetadata()->{'get' . ucfirst($metatype)}();
        }

        // Otherwise, deduct the appropriate field
        return $this->$metatype->fromEntity($entity) ?? $this->metadataConfig['default_' . $metatype];
    }
}
