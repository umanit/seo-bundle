<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\UrlHistory;

use Doctrine\ORM\EntityManagerInterface;
use Umanit\SeoBundle\Entity\UrlHistory;
use Umanit\SeoBundle\Handler\Routable\RoutableInterface;
use Umanit\SeoBundle\Model\HistorizableUrlModelInterface;
use Umanit\SeoBundle\Repository\UrlHistoryRepositoryInterface;

class UrlPool
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UrlHistoryRepositoryInterface $urlHistoryRepository,
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
        $urlHistory = $this->urlHistoryRepository
            ->findOneByOldPathAndSeoUuid($oldPath, $entity->getUrlReference()->getSeoUuid(), $locale)
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
        return $this->urlHistoryRepository->findOneByOldPath($path, $locale);
    }

    private function resolveRouteFromEntity(HistorizableUrlModelInterface $entity): string
    {
        return $this->routableHandler->handle($entity)->getName();
    }
}
