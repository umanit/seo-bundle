<?php

namespace Umanit\SeoBundle\Doctrine\Model;

use Umanit\SeoBundle\Entity\UrlRef;

/**
 * Interface UrlHistorizedInterface
 */
interface UrlHistorizedInterface
{
    public function getSeoUuid(): ?string;

    public function setSeoUuid(string $uuid): UrlHistorizedInterface;

    public function getUrlRef(): ?UrlRef;

    public function setUrlRef(?UrlRef $urlRef): UrlHistorizedInterface;
}
