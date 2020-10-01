<?php

namespace Umanit\SeoBundle\Runtime\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Umanit\SeoBundle\Breadcrumb\BreadcrumbBuilder;
use Umanit\SeoBundle\Exception\NotBreadcrumbEntityException;
use Umanit\SeoBundle\Exception\NotSchemaOrgEntityException;
use Umanit\SeoBundle\Exception\NotSeoRouteEntityException;
use Umanit\SeoBundle\Model\RoutableInterface;
use Umanit\SeoBundle\Routing\Canonical;
use Umanit\SeoBundle\Runtime\CurrentSeoEntity;
use Umanit\SeoBundle\SchemaOrg\SchemaOrgResolver;
use Umanit\SeoBundle\Utils\SeoMetadataResolver;

class SeoExtension extends AbstractExtension
{
    /** @var Canonical */
    private $canonical;

    /** @var CurrentSeoEntity */
    private $currentSeoEntity;

    /** @var SeoMetadataResolver */
    private $metadataResolver;

    /** @var SchemaOrgResolver */
    private $schemaOrgResolver;

    /** @var BreadcrumbBuilder */
    private $breadcrumbBuilder;

    public function __construct(
        Canonical $canonical,
        CurrentSeoEntity $currentSeoEntity,
        SeoMetadataResolver $metadataResolver,
        SchemaOrgResolver $schemaOrgResolver,
        BreadcrumbBuilder $breadcrumbBuilder
    ) {

        $this->canonical = $canonical;
        $this->currentSeoEntity = $currentSeoEntity;
        $this->metadataResolver = $metadataResolver;
        $this->schemaOrgResolver = $schemaOrgResolver;
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
        if (null !== $entity && !$entity instanceof RoutableInterface) {
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
     * @throws \ReflectionException
     */
    public function schemaOrg(?object $entity = null): string
    {
        try {
            return strtr(
                <<<HTML
<script type="application/ld+json">%json%</script>
HTML
                , [
                '%json%' => json_encode($this->schemaOrgResolver->getSchemaBuilder(
                    $entity ?? $this->currentSeoEntity->get()
                )->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            ]);

        } catch (NotSchemaOrgEntityException $e) {
            // Do nothing
        }

        return '';
    }

    /**
     * Display the seo schema org from an entity or the current one.
     *
     * @param object|null $entity
     * @param null        $format
     *
     * @return string
     * @throws NotSeoRouteEntityException
     * @throws \ErrorException
     * @throws \ReflectionException
     * @throws \Twig\Error\Error
     */
    public function breadcrumb(?object $entity = null, $format = null): string
    {
        try {
            return $this->breadcrumbBuilder->buildBreadcrumb(
                $entity ?? $this->currentSeoEntity->get(),
                $format
            );
        } catch (NotBreadcrumbEntityException $e) {
            // Do nothing
        }

        return '';
    }
}
