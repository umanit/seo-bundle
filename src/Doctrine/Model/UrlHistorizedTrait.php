<?php

namespace Umanit\SeoBundle\Doctrine\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait UrlHistorizedTrait
 */
trait UrlHistorizedTrait
{
    /**
     * @var string
     *
     * @ORM\Column(type="guid", unique=true, length=36)
     */
    protected $seoUuid;

    public function getSeoUuid(): ?string
    {
        return $this->seoUuid;
    }

    public function setSeoUuid(string $seoUuid): UrlHistorizedInterface
    {
        $this->seoUuid = $seoUuid;

        return $this;
    }
}
