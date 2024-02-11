<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Repository;

use Umanit\SeoBundle\Entity\UrlHistory;

interface UrlHistoryRepositoryInterface
{
    /**
     * @return array<int, UrlHistory>
     */
    public function findBySeoUuid(string $seoUuid): array;

    public function findOneByOldPath(string $oldPath, string $locale): ?UrlHistory;

    public function findOneByOldPathAndSeoUuid(string $oldPath, string $seoUuid, string $locale): ?UrlHistory;
}
