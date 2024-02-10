<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\UrlHistory;

use Umanit\SeoBundle\Model\HistorizableUrlModelInterface;

interface UrlPoolerInterface
{
    /**
     * Process the update of an entity to historize URLs changes.
     */
    public function processEntityUpdate(
        HistorizableUrlModelInterface $newEntity,
        HistorizableUrlModelInterface $oldEntity
    ): void;

    /**
     * Process the dependency of an updated entity to historize URLs changes.
     */
    public function processEntityDependency(HistorizableUrlModelInterface $dependency): bool;
}
