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
    /** @var EntityManagerInterface */
    private $em;

    /** @var RoutableInterface */
    private $routableHandler;

    /** @var string */
    private $defaultLocale;

    /** @var UrlHistory[] */
    private $items = [];

    public function __construct(EntityManagerInterface $em, RoutableInterface $routableHandler, string $defaultLocale)
    {
        $this->em = $em;
        $this->routableHandler = $routableHandler;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Add a set to the history.
     *
     * @param string                        $oldPath
     * @param string                        $newPath
     * @param HistorizableUrlModelInterface $entity
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

        $this->items[] = $urlHistory;
    }

    /**
     * Gets an item from db.
     *
     * @param string      $path
     * @param string|null $locale
     *
     * @return UrlHistory|null
     */
    public function get(string $path, string $locale = null): ?UrlHistory
    {
        return $this->getUrlHistoryRepository()->findOneBy(['oldPath' => $path, 'locale' => $locale]);
    }

    /**
     * Flushes the cached items.
     */
    public function flush(): void
    {
        if (!empty($this->items)) {
            foreach ($this->items as $item) {
                $this->em->persist($item);
            }

            $this->items = [];

            $this->em->flush();
        }
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
