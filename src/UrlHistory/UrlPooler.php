<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\UrlHistory;

use Umanit\SeoBundle\Entity\UrlReference;
use Umanit\SeoBundle\Model\HistorizableUrlModelInterface;
use Umanit\SeoBundle\Routing\Canonical;

class UrlPooler implements UrlPoolerInterface
{
    public function __construct(
        private readonly Canonical $canonical,
        private readonly UrlPool $urlPool,
    ) {
    }

    public function processEntityUpdate(
        HistorizableUrlModelInterface $newEntity,
        HistorizableUrlModelInterface $oldEntity
    ): void {
        $oldUrl = $this->canonical->url($oldEntity);
        $newUrl = $this->canonical->url($newEntity);

        if ($oldUrl !== $newUrl) {
            $this->urlPool->add($oldUrl, $newUrl, $newEntity);
        }
    }

    public function processEntityDependency(HistorizableUrlModelInterface $dependency): bool
    {
        $urlReference = $dependency->getUrlReference();
        $newUrl = $this->canonical->url($dependency);
        $oldUrl = $urlReference instanceof UrlReference ? $urlReference->getUrl() : null;

        if ($oldUrl !== $newUrl) {
            // Add the redirection to the pool
            $this->urlPool->add($oldUrl, $newUrl, $dependency);

            if ($urlReference instanceof UrlReference) {
                $urlReference->setUrl($newUrl);
            }

            return true;
        }

        return false;
    }
}
