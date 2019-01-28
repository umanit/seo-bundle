<?php

namespace Umanit\SeoBundle\Runtime\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Umanit\SeoBundle\Exception\NotSchemaOrgEntityException;
use Umanit\SeoBundle\Exception\NotSeoRouteEntityException;
use Umanit\SeoBundle\Model\AnnotationReaderTrait;
use Umanit\SeoBundle\Routing\Canonical;
use Umanit\SeoBundle\Runtime\CurrentSeoEntity;
use Umanit\SeoBundle\SchemaOrg\SchemaOrgResolver;
use Umanit\SeoBundle\Utils\SeoMetadataResolver;

/**
 * Class SeoExtension
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class SeoExtension extends AbstractExtension
{
    use AnnotationReaderTrait;

    /** @var Canonical */
    private $canonical;

    /** @var CurrentSeoEntity */
    private $currentSeoEntity;

    /** @var SeoMetadataResolver */
    private $metadataResolver;

    /** @var SchemaOrgResolver */
    private $schemaOrgResolver;

    /**
     * SeoExtension constructor.
     *
     * @param Canonical           $canonical
     * @param CurrentSeoEntity    $currentSeoEntity
     * @param SeoMetadataResolver $metadataResolver
     * @param SchemaOrgResolver   $schemaOrgResolver
     */
    public function __construct(
        Canonical $canonical,
        CurrentSeoEntity $currentSeoEntity,
        SeoMetadataResolver $metadataResolver,
        SchemaOrgResolver $schemaOrgResolver
    ) {
        $this->canonical         = $canonical;
        $this->currentSeoEntity  = $currentSeoEntity;
        $this->metadataResolver  = $metadataResolver;
        $this->schemaOrgResolver = $schemaOrgResolver;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('canonical', [$this, 'canonical'], ['is_safe' => ['html']]),
            new TwigFunction('seo_metadata', [$this, 'seoMetadata'], ['is_safe' => ['html']]),
            new TwigFunction('seo_schema_org', [$this, 'seoSchemaOrg'], ['is_safe' => ['html', 'javascript']]),
        ];
    }

    /**
     * Generates and returns the canonical url.
     *
     * @param object|null $entity
     * @param array|null  $overrides
     *
     * @return string
     */
    public function canonical(?object $entity = null, array $overrides = []): string
    {
        try {
            /** @noinspection HtmlUnknownTarget */
            return sprintf('<link rel="canonical" href="%s"/>', $this->canonical->url(
                $entity ?? $this->currentSeoEntity->get(),
                $overrides
            ));
        } catch (NotSeoRouteEntityException $e) {
            if (null !== $entity) {
                throw $e;
            }
        }

        return '';
    }

    /**
     * Generates and displays the seo metadata.
     *
     * @param object|null $entity
     *
     * @return string
     */
    public function seoMetadata(?object $entity = null)
    {
        return strtr(<<<HTML
<meta name="title" content="%title%" />
<meta name="description" content="%description%" />
HTML
            , [
                '%title%'       => $this->metadataResolver->metaTitle($entity ?? $this->currentSeoEntity->get()),
                '%description%' => $this->metadataResolver->metaDescription($entity ?? $this->currentSeoEntity->get()),
            ]);
    }

    /**
     * Display the seo schema org from an entity or the current one.
     *
     * @param object|null $entity
     *
     * @return string
     * @throws NotSchemaOrgEntityException
     * @throws \ReflectionException
     */
    public function seoSchemaOrg(?object $entity = null): string
    {
        try {
            return $this->schemaOrgResolver->getSchemaBuilder(
                $entity ?? $this->currentSeoEntity->get()
            )->toScript()
                ;
        } catch (NotSchemaOrgEntityException $e) {
            if (null !== $entity) {
                throw $e;
            }
        }

        return '<script>console.error("Unable generate microdata schema.")</script>';
    }
}
