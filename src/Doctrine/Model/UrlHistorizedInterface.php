<?php

namespace Umanit\SeoBundle\Doctrine\Model;

/**
 * Interface UrlHistorizedInterface
 */
interface UrlHistorizedInterface
{
    public function getSeoUuid(): ?string;

    public function setSeoUuid(string $uuid): UrlHistorizedInterface;
}
