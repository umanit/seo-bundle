<?php

namespace Umanit\SeoBundle\Utils;

use Umanit\SeoBundle\Doctrine\Model\HasSeoMetadataInterface;
use Umanit\SeoBundle\Utils\EntityParser\Excerpt;
use Umanit\SeoBundle\Utils\EntityParser\Title;

/**
 * Class SeoMetadataResolver
 * Resolves Seo metadata from an entity.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class SeoMetadataResolver
{
    /** @var Title */
    private $title;

    /** @var Excerpt */
    private $description;

    /** @var array */
    private $metadataConfig;

    /**
     * SeoMetadataResolver constructor.
     *
     * @param Title   $title
     * @param Excerpt $excerpt
     * @param array   $metadataConfig
     */
    public function __construct(Title $title, Excerpt $excerpt, array $metadataConfig)
    {
        $this->title          = $title;
        $this->description    = $excerpt;
        $this->metadataConfig = $metadataConfig;
    }

    /**
     * Returns the meta title of an entity.
     *
     * @param object|null $entity
     *
     * @return string
     */
    public function metaTitle(?object $entity)
    {
        return $this->meta($entity, 'title');
    }

    /**
     * Returns the meta description of an entity.
     *
     * @param object|null $entity
     *
     * @return string
     */
    public function metaDescription(?object $entity)
    {
        return $this->meta($entity, 'description');
    }

    /**
     * @internal Returns a meta data field.
     *
     * @param object|null $entity
     * @param string      $metatype
     *
     * @return string string
     */
    private function meta(?object $entity, string $metatype): string
    {
        if (null === $entity || false === is_object($entity)) {
            return $this->metadataConfig['default_'.$metatype];
        }

        if ($entity instanceof HasSeoMetadataInterface) {
            // Look if the attribute is null
            if (null !== $entity->getSeoMetadata()->{'get'.ucfirst($metatype)}()) {
                return $entity->getSeoMetadata()->{'get'.ucfirst($metatype)}();
            }
        }

        // Otherwise, deduct the appropriate field
        return $this->$metatype->fromEntity($entity) ?? $this->metadataConfig['default_'.$metatype];
    }
}
