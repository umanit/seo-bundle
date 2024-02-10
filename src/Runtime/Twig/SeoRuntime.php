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
    public function __construct(
        private readonly Canonical $canonical,
        private readonly CurrentSeoEntity $currentSeoEntity,
        private readonly SeoMetadataResolver $metadataResolver,
        private readonly SchemaOrgBuilderInterface $schemaOrgBuilder,
        private readonly BreadcrumbBuilder $breadcrumbBuilder,
    ) {
    }

    /**
     * Generates and displays the page title.
     */
    public function title(?object $entity = null): string
    {
        return $this->metadataResolver->metaTitle($entity ?? $this->currentSeoEntity->get());
    }

    /**
     * Generates and returns the canonical url.
     */
    public function canonical(?object $entity = null, array $parameters = []): string
    {
        $entity ??= $this->currentSeoEntity->get();

        if (!$entity instanceof RoutableModelInterface) {
            return '';
        }

        return sprintf('<link rel="canonical" href="%s" />', $this->canonical->url($entity, $parameters));
    }

    /**
     * Generates and displays the seo metadata.
     */
    public function metadata(?object $entity = null): string
    {
        $entity ??= $this->currentSeoEntity->get();

        return strtr(
            <<<HTML
<meta name="title" content="%title%" />
<meta name="description" content="%description%" />
HTML
            ,
            [
                '%title%' => $this->metadataResolver->metaTitle($entity),
                '%description%' => $this->metadataResolver->metaDescription($entity),
            ]
        );
    }

    /**
     * Display the seo schema org from an entity or the current one.
     */
    public function schemaOrg(?object $entity = null): string
    {
        $entity ??= $this->currentSeoEntity->get();

        if (!$entity instanceof SchemableModelInterface) {
            return '';
        }

        return strtr(
            <<<HTML
<script type="application/ld+json">%json%</script>
HTML
            ,
            [
                '%json%' => json_encode(
                    $this->schemaOrgBuilder->buildSchema($entity)->toArray(),
                    JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
                ),
            ]
        );
    }

    /**
     * Display the seo schema org from an entity or the current one.
     *
     * @throws ErrorException
     */
    public function breadcrumb(?object $entity = null, ?string $format = null): string
    {
        $entity ??= $this->currentSeoEntity->get();

        if (!$entity instanceof BreadcrumbableModelInterface) {
            return '';
        }

        return $this->breadcrumbBuilder->buildBreadcrumb($entity, $format);
    }
}
