<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\UrlHistory;

use Umanit\SeoBundle\Model\HistorizableUrlModelInterface;
use Umanit\SeoBundle\Routing\Canonical;

class UrlPooler implements UrlPoolerInterface
{
    /** @var Canonical */
    private $canonical;

    /** @var UrlPool */
    private $urlPool;

    public function __construct(Canonical $canonical, UrlPool $urlPool)
    {
        $this->canonical = $canonical;
        $this->urlPool = $urlPool;
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

    public function processEntityDependency(HistorizableUrlModelInterface $dependency): bool {
        $urlReference = $dependency->getUrlReference();
        $newUrl = $this->canonical->url($dependency);
        $oldUrl = null !== $urlReference ? $urlReference->getUrl() : null;

        if ($oldUrl !== $newUrl) {
            // Add the redirection to the pool
            $this->urlPool->add($oldUrl, $newUrl, $dependency);

            if (null !== $urlReference) {
                $urlReference->setUrl($newUrl);
            }

            return true;
        }

        return false;
    }

    public function flush(): void
    {
        $this->urlPool->flush();
    }
}
