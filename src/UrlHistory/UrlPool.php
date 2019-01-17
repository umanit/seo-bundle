<?php

namespace App\Umanit\SeoBundle\UrlHistory;

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
    private const CACHE_KEY = 'umanit.seo.url_history';

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
    public function add(string $oldPath, string $newPath, $entity)
    {
        // Find the primary key
        $class           = get_class($entity);
        $meta            = $this->em->getClassMetadata(get_class($entity));
        $identifierField = $meta->getSingleIdentifierFieldName();
        $identifierValue = $this->propAccess->getValue($entity, $identifierField);

        $cacheItem = $this->pool->getItem(static::CACHE_KEY);
        // Add the value to the cache item
        $cacheItem->set(
            ($cacheItem->get() ?? []) + [
                [
                    'old_path' => $oldPath,
                    'new_path' => $newPath,
                    'entity'  => $class.';'.$identifierValue,
                ],
            ]
        );
    }
}