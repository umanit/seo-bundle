<?php

namespace Umanit\SeoBundle\Runtime\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Umanit\SeoBundle\Exception\NotSeoEntityException;
use Umanit\SeoBundle\Model\AnnotationReaderTrait;
use Umanit\SeoBundle\Routing\Canonical;
use Umanit\SeoBundle\Runtime\CurrentSeoEntity;
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

    /**
     * SeoExtension constructor.
     *
     * @param Canonical        $canonical
     * @param CurrentSeoEntity $currentSeoEntity
     */
    public function __construct(Canonical $canonical, CurrentSeoEntity $currentSeoEntity, SeoMetadataResolver $metadataResolver)
    {
        $this->canonical        = $canonical;
        $this->currentSeoEntity = $currentSeoEntity;
        $this->metadataResolver = $metadataResolver;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('canonical', [$this, 'canonical'], ['is_safe' => ['html']]),
            new TwigFunction('seo_metadata', [$this, 'seoMetadata'], ['is_safe' => ['html']]),
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
            return sprintf('<link rel="canonical" href="%s"/>', $this->canonical->url(
                $entity ?? $this->currentSeoEntity->get(),
                $overrides
            ));
        } catch (NotSeoEntityException $e) {
            if (null !== $entity) {
                throw $e;
            }
        }

        return '';
    }

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
}
