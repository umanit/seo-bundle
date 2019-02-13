<?php

namespace Umanit\SeoBundle\UrlHistory;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Umanit\SeoBundle\Doctrine\Model\UrlHistorizedInterface;
use Umanit\SeoBundle\Entity\UrlHistory;
use Umanit\SeoBundle\Model\AnnotationReaderTrait;
use Umanit\SeoBundle\Repository\UrlHistoryRepository;

/**
 * Class UrlPool
 *
 * Pool of historized URLs.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class UrlPool
{
    use AnnotationReaderTrait;

    /** @var EntityManagerInterface */
    private $em;

    /** @var string */
    private $defaultLocale;

    /** @var array<UrlHistory> */
    private $items = [];

    /**
     * UrlPool constructor.
     *
     * @param EntityManagerInterface $em
     * @param string                 $defaultLocale
     */
    public function __construct(
        EntityManagerInterface $em,
        string $defaultLocale
    ) {
        $this->em            = $em;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Add a set to the history.
     *
     * @param string $oldPath
     * @param string $newPath
     * @param        $entity
     */
    public function add(string $oldPath, string $newPath, UrlHistorizedInterface $entity): void
    {
        $urlHistory = $this->getUrlHistoryRepository()->findOneBy([
            'oldPath' => $oldPath,
            'locale'  => method_exists($entity, 'getLocale') ? $entity->getLocale() : $this->defaultLocale,
            'seoUuid' => $entity->getUrlRef()->getSeoUuid(),
        ])
        ;

        if (null === $urlHistory) {
            $urlHistory = (new UrlHistory())
                ->setLocale(method_exists($entity, 'getLocale') ? $entity->getLocale() : $this->defaultLocale)
                ->setNewPath($newPath)
                ->setOldPath($oldPath)
                ->setRoute($this->resolveRouteFromEntity($entity))
                ->setSeoUuid($entity->getUrlRef()->getSeoUuid())
            ;
        }

        $urlHistory
            ->setNewPath($newPath);

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
        /** @noinspection PhpIncompatibleReturnTypeInspection */
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

    /**
     * @return UrlHistoryRepository
     */
    private function getUrlHistoryRepository(): ObjectRepository
    {
        return $this->em->getRepository(UrlHistory::class);
    }

    /**
     * @param object $entity
     *
     * @return string|null
     * @throws \ReflectionException
     * @throws \Umanit\SeoBundle\Exception\NotSeoRouteEntityException
     */
    private function resolveRouteFromEntity(object $entity): ?string
    {
        return $this->getSeoRouteAnnotation($entity)->getRouteName();
    }
}
