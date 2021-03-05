<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Runtime\Twig;

use ErrorException;
use Twig\Extension\RuntimeExtensionInterface;
use Umanit\SeoBundle\Breadcrumb\BreadcrumbBuilder;
use Umanit\SeoBundle\Model\BreadcrumbableModelInterface;
use Umanit\SeoBundle\Model\RoutableModelInterface;
use Umanit\SeoBundle\Model\SchemableModelInterface;
use Umanit\SeoBundle\Routing\Canonical;
use Umanit\SeoBundle\Runtime\CurrentSeoEntity;
use Umanit\SeoBundle\SchemaOrg\SchemaOrgBuilderInterface;
use Umanit\SeoBundle\Utils\SeoMetadataResolver;

class SeoRuntime implements RuntimeExtensionInterface
{
    /** @var Canonical */
    private $canonical;

    /** @var CurrentSeoEntity */
    private $currentSeoEntity;

    /** @var SeoMetadataResolver */
    private $metadataResolver;

    /** @var SchemaOrgBuilderInterface */
    private $schemaOrgBuilder;

    /** @var BreadcrumbBuilder */
    private $breadcrumbBuilder;

    public function __construct(
        Canonical $canonical,
        CurrentSeoEntity $currentSeoEntity,
        SeoMetadataResolver $metadataResolver,
        SchemaOrgBuilderInterface $schemaOrgBuilder,
        BreadcrumbBuilder $breadcrumbBuilder
    ) {

        $this->canonical = $canonical;
        $this->currentSeoEntity = $currentSeoEntity;
        $this->metadataResolver = $metadataResolver;
        $this->schemaOrgBuilder = $schemaOrgBuilder;
        $this->breadcrumbBuilder = $breadcrumbBuilder;
    }

    /**
     * Generates and displays the page title.
     *
     * @param object|null $entity
     *
     * @return string
     */
    public function title(?object $entity = null): string
    {
        return $this->metadataResolver->metaTitle($entity ?? $this->currentSeoEntity->get());
    }

    /**
     * Generates and returns the canonical url.
     *
     * @param object|null $entity
     * @param array       $parameters
     *
     * @return string
     */
    public function canonical(?object $entity = null, array $parameters = []): string
    {
        $entity = $entity ?? $this->currentSeoEntity->get();

        if (!$entity instanceof RoutableModelInterface) {
            return '';
        }

        return sprintf('<link rel="canonical" href="%s" />', $this->canonical->url($entity, $parameters));
    }

    /**
     * Generates and displays the seo metadata.
     *
     * @param object|null $entity
     *
     * @return string
     */
    public function metadata(?object $entity = null): string
    {
        $entity = $entity ?? $this->currentSeoEntity->get();

        return strtr(<<<HTML
<meta name="title" content="%title%" />
<meta name="description" content="%description%" />
HTML
            , [
                '%title%'       => $this->metadataResolver->metaTitle($entity),
                '%description%' => $this->metadataResolver->metaDescription($entity),
            ]);
    }

    /**
     * Display the seo schema org from an entity or the current one.
     *
     * @param object|null $entity
     *
     * @return string
     */
    public function schemaOrg(?object $entity = null): string
    {
        $entity = $entity ?? $this->currentSeoEntity->get();

        if (!$entity instanceof SchemableModelInterface) {
            return '';
        }

        return strtr(
            <<<HTML
<script type="application/ld+json">%json%</script>
HTML
            , [
            '%json%' => json_encode(
                $this->schemaOrgBuilder->buildSchema($entity)->toArray(),
                JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
            ),
        ]);
    }

    /**
     * Display the seo schema org from an entity or the current one.
     *
     * @param object|null $entity
     * @param null        $format
     *
     * @return string
     * @throws ErrorException
     */
    public function breadcrumb(?object $entity = null, $format = null): string
    {
        $entity = $entity ?? $this->currentSeoEntity->get();

        if (!$entity instanceof BreadcrumbableModelInterface) {
            return '';
        }

        return $this->breadcrumbBuilder->buildBreadcrumb($entity, $format);
    }
}
