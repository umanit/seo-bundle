<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Umanit\SeoBundle\Entity\UrlHistory;

class UrlHistoryRepository extends ServiceEntityRepository implements UrlHistoryRepositoryInterface
{
    public function findBySeoUuid(string $seoUuid): array
    {
        return $this->findBy(['seoUuid' => $seoUuid], ['id' => 'ASC']);
    }

    public function findOneByOldPath(string $oldPath, string $locale): ?UrlHistory
    {
        return $this->findOneBy(['oldPath' => $oldPath, 'locale' => $locale]);
    }

    public function findOneByOldPathAndSeoUuid(string $oldPath, string $seoUuid, string $locale): ?UrlHistory
    {
        return $this->findOneBy(['oldPath' => $oldPath, 'seoUuid' => $seoUuid, 'locale' => $locale]);
    }
}
