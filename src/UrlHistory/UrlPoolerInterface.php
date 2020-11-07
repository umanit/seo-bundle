<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\UrlHistory;

use Umanit\SeoBundle\Model\HistorizableUrlModelInterface;

interface UrlPoolerInterface
{
    /**
     * Process the update of an entity to historize URLs changes.
     *
     * @param HistorizableUrlModelInterface $newEntity
     * @param HistorizableUrlModelInterface $oldEntity
     */
    public function processEntityUpdate(
        HistorizableUrlModelInterface $newEntity,
        HistorizableUrlModelInterface $oldEntity
    ): void;

    /**
     * Process the dependency of an updated entity to historize URLs changes.
     *
     * @param HistorizableUrlModelInterface $entity
     * @param HistorizableUrlModelInterface $dependency
     *
     * @return bool
     */
    public function processEntityDependency(
        HistorizableUrlModelInterface $entity,
        HistorizableUrlModelInterface $dependency
    ): bool;

    /**
     * Flush the pool.
     */
    public function flush(): void;
}
