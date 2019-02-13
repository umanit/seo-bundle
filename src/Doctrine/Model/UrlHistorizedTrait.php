<?php

namespace Umanit\SeoBundle\Doctrine\Model;

use Doctrine\ORM\Mapping as ORM;
use Umanit\SeoBundle\Entity\UrlRef;

/**
 * Trait UrlHistorizedTrait
 */
trait UrlHistorizedTrait
{
    /**
     * @var string
     *
     * @ORM\Column(name="seo_uuid", type="guid", unique=true, length=36)
     */
    protected $seoUuid;

    /**
     * @var UrlRef
     * @ORM\OneToOne(targetEntity="Umanit\SeoBundle\Entity\UrlRef", cascade={"persist", "remove"})
     */
    protected $urlRef;

    public function getSeoUuid(): ?string
    {
        return $this->seoUuid;
    }

    public function setSeoUuid(string $seoUuid): UrlHistorizedInterface
    {
        $this->seoUuid = $seoUuid;

        return $this;
    }

    public function getUrlRef(): ?UrlRef
    {
        return $this->urlRef;
    }

    public function setUrlRef(?UrlRef $urlRef): UrlHistorizedInterface
    {
        $this->urlRef = $urlRef;

        return $this;
    }
}
