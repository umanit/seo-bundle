<?php

namespace Umanit\SeoBundle\UrlHistory;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class UrlPool
 *
 * Pool of historized URLs.
 * A simple wrapper to any CacheItemPoolInterface.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class UrlPool
{
    private const         CACHE_KEY      = 'umanit.seo.url_history.';
    private const         CACHE_DURATION = 'P1M'; // One month

    /** @var CacheItemPoolInterface */
    private $pool;

    /** @var EntityManagerInterface */
    private $em;

    /** @var PropertyAccessor */
    private $propAccess;

    /**
     * {@inheritdoc}
     * UrlPool constructor.
     *
     * @param CacheItemPoolInterface $pool
     */
    public function __construct(CacheItemPoolInterface $pool, EntityManagerInterface $em)
    {
        $this->pool       = $pool;
        $this->em         = $em;
        $this->propAccess = new PropertyAccessor();
    }

    /**
     * Add a set to the history.
     *
     * @param string $oldPath
     * @param string $newPath
     * @param        $entity
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function add(string $oldPath, string $newPath, $entity): void
    {
        // Find the primary key
        $class           = get_class($entity);
        $meta            = $this->em->getClassMetadata(get_class($entity));
        $identifierField = $meta->getSingleIdentifierFieldName();
        $identifierValue = $this->propAccess->getValue($entity, $identifierField);

        $cacheItem = $this->pool->getItem($this->formatCacheKey($oldPath));

        // Add the value to the cache item
        $cacheItem->set([
            'old_path' => $oldPath,
            'new_path' => $newPath,
            'entity'   => $class.';'.$identifierValue,
        ]);
        $cacheItem->expiresAfter(new \DateInterval(static::CACHE_DURATION));
        $this->pool->save($cacheItem);
    }

    /**
     * Checks that the path is saved in the pool.
     *
     * @param string $path
     *
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function has(string $path): bool
    {
        $cacheItem = $this->pool->getItem($this->formatCacheKey($path));

        return null !== $cacheItem->get();
    }

    /**
     * Return an item in the cache
     *
     * @param string $path
     *
     * @return mixed|null
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function get(string $path)
    {
        if (false === $this->has($path)) {
            return null;
        }

        return $this->pool->getItem($this->formatCacheKey($path))->get();
    }

    /**
     * Formats the cache item key from a path.
     *
     * @param string $path
     *
     * @return string
     */
    private function formatCacheKey(string $path): string
    {
        return static::CACHE_KEY.str_replace('/', '-', $path);
    }
}
