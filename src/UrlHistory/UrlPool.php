<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\UrlHistory;

use Doctrine\ORM\EntityManagerInterface;
use Umanit\SeoBundle\Entity\UrlHistory;
use Umanit\SeoBundle\Handler\Routable\RoutableInterface;
use Umanit\SeoBundle\Model\HistorizableUrlModelInterface;
use Umanit\SeoBundle\Repository\UrlHistoryRepository;

class UrlPool
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RoutableInterface $routableHandler,
        private readonly string $defaultLocale,
    ) {
    }

    /**
     * Add a set to the history.
     */
    public function add(string $oldPath, string $newPath, HistorizableUrlModelInterface $entity): void
    {
        $locale = method_exists($entity, 'getLocale') ? $entity->getLocale() : $this->defaultLocale;
        $urlHistory = $this
            ->getUrlHistoryRepository()
            ->findOneBy([
                'oldPath' => $oldPath,
                'locale'  => $locale,
                'seoUuid' => $entity->getUrlReference()->getSeoUuid(),
            ])
        ;

        if (null === $urlHistory) {
            $urlHistory = (new UrlHistory())
                ->setLocale($locale)
                ->setNewPath($newPath)
                ->setOldPath($oldPath)
                ->setRoute($this->resolveRouteFromEntity($entity))
                ->setSeoUuid($entity->getUrlReference()->getSeoUuid())
            ;
        }

        $urlHistory->setNewPath($newPath);

        $this->em->persist($urlHistory);
        $this->em->flush();
    }

    /**
     * Gets an item from db.
     */
    public function get(string $path, ?string $locale = null): ?UrlHistory
    {
        return $this->getUrlHistoryRepository()->findOneBy(['oldPath' => $path, 'locale' => $locale]);
    }

    private function getUrlHistoryRepository(): UrlHistoryRepository
    {
        return $this->em->getRepository(UrlHistory::class);
    }

    private function resolveRouteFromEntity(HistorizableUrlModelInterface $entity): string
    {
        return $this->routableHandler->handle($entity)->getName();
    }
}
