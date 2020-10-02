<?php

namespace Umanit\SeoBundle\Utils;

use Umanit\SeoBundle\Model\HasSeoMetadataInterface;
use Umanit\SeoBundle\Utils\EntityParser\Excerpt;
use Umanit\SeoBundle\Utils\EntityParser\Title;

/**
 * Resolves Seo metadata from an entity.
 */
class SeoMetadataResolver
{
    /** @var Title */
    private $title;

    /** @var Excerpt */
    private $description;

    /** @var array */
    private $metadataConfig;

    public function __construct(Title $title, Excerpt $excerpt, array $metadataConfig)
    {
        $this->title = $title;
        $this->description = $excerpt;
        $this->metadataConfig = $metadataConfig;
    }

    /**
     * Returns the meta title of an entity.
     *
     * @param object|null $entity
     * @param bool        $includePrefix
     * @param bool        $includeSuffix
     *
     * @return string
     */
    public function metaTitle(?object $entity, bool $includePrefix = true, bool $includeSuffix = true): string
    {
        $title = $this->meta($entity, 'title');

        if ($includePrefix) {
            $title = $this->metadataConfig['title_prefix'].$title;
        }

        if ($includeSuffix) {
            $title .= $this->metadataConfig['title_suffix'];
        }

        return $title;
    }

    /**
     * Returns the meta description of an entity.
     *
     * @param object|null $entity
     *
     * @return string
     */
    public function metaDescription(?object $entity): string
    {
        return $this->meta($entity, 'description');
    }

    /**
     * @param object|null $entity
     * @param string      $metatype
     *
     * @return string string
     */
    private function meta(?object $entity, string $metatype): string
    {
        if (null === $entity || !\is_object($entity)) {
            return $this->metadataConfig['default_'.$metatype];
        }

        if (
            $entity instanceof HasSeoMetadataInterface &&
            null !== $entity->getSeoMetadata() &&
            null !== $entity->getSeoMetadata()->{'get'.ucfirst($metatype)}()
        ) {
            return $entity->getSeoMetadata()->{'get'.ucfirst($metatype)}();
        }

        // Otherwise, deduct the appropriate field
        return $this->$metatype->fromEntity($entity) ?? $this->metadataConfig['default_'.$metatype];
    }
}
