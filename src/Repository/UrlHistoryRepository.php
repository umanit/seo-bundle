<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Umanit\SeoBundle\UrlHistory\UrlPoolerInterface;

class UrlHistoryRepository extends ServiceEntityRepository
{
    /**
     * @return array<int, UrlPoolerInterface>
     */
    public function findBySeoUuid(string $seoUuid): array
    {
        return $this->findBy(['seoUuid' => $seoUuid], ['id' => 'ASC']);
    }
}
