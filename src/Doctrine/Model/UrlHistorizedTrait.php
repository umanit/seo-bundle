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
