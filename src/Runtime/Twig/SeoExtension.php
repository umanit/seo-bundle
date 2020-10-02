<?php

namespace Umanit\SeoBundle\Runtime\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Umanit\SeoBundle\Breadcrumb\BreadcrumbBuilder;
use Umanit\SeoBundle\Model\BreadcrumbableModelInterface;
use Umanit\SeoBundle\Model\RoutableModelInterface;
use Umanit\SeoBundle\Model\SchemableModelInterface;
use Umanit\SeoBundle\Routing\Canonical;
use Umanit\SeoBundle\Runtime\CurrentSeoEntity;
use Umanit\SeoBundle\SchemaOrg\SchemaOrgBuilderInterface;
use Umanit\SeoBundle\Utils\SeoMetadataResolver;

class SeoExtension extends AbstractExtension
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

    public function getFunctions(): array
    {
        return [
            new TwigFunction('seo_title', [$this, 'title']),
            new TwigFunction('seo_canonical', [$this, 'canonical'], ['is_safe' => ['html']]),
            new TwigFunction('seo_metadata', [$this, 'metadata'], ['is_safe' => ['html']]),
            new TwigFunction('seo_schema_org', [$this, 'schemaOrg'], ['is_safe' => ['html', 'javascript']]),
            new TwigFunction('seo_breadcrumb', [$this, 'breadcrumb'], ['is_safe' => ['html', 'javascript']]),
        ];
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
     * @param array|null  $overrides
     *
     * @return string
     */
    public function canonical(?object $entity = null, array $overrides = []): string
    {
        if (
            (null !== $entity && !$entity instanceof RoutableModelInterface) ||
            (null === $entity && null === $this->currentSeoEntity->get())
        ) {
            return '';
        }

        return sprintf(
            '<link rel="canonical" href="%s"/>',
            $this->canonical->url(
                $entity ?? $this->currentSeoEntity->get(),
                $overrides
            )
        );
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
        if (
            (null !== $entity && !$entity instanceof SchemableModelInterface) ||
            (null === $entity && null === $this->currentSeoEntity->get())
        ) {
            return '';
        }

        return strtr(
            <<<HTML
<script type="application/ld+json">%json%</script>
HTML
            , [
            '%json%' => json_encode($this->schemaOrgBuilder->buildSchema(
                $entity ?? $this->currentSeoEntity->get()
            )->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ]);
    }

    /**
     * Display the seo schema org from an entity or the current one.
     *
     * @param object|null $entity
     * @param null        $format
     *
     * @return string
     * @throws \ErrorException
     */
    public function breadcrumb(?object $entity = null, $format = null): string
    {
        if (
            (null !== $entity && !$entity instanceof BreadcrumbableModelInterface) ||
            (null === $entity && null === $this->currentSeoEntity->get())
        ) {
            return '';
        }

        return $this->breadcrumbBuilder->buildBreadcrumb(
            $entity ?? $this->currentSeoEntity->get(),
            $format
        );
    }
}
