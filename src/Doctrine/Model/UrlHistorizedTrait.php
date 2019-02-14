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
     * @var UrlRef
     * @ORM\OneToOne(targetEntity="Umanit\SeoBundle\Entity\UrlRef", cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="seo_url_reference", nullable=false)
     */
    protected $urlRef;

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
