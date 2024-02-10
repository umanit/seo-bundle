<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Doctrine\EventSubscriber;

use Doctrine\ORM\Event\PostLoadEventArgs;
use Symfony\Component\HttpFoundation\RequestStack;
use Umanit\SeoBundle\Model\RoutableModelInterface;
use Umanit\SeoBundle\Routing\Canonical;
use Umanit\SeoBundle\Runtime\CurrentSeoEntity;

/**
 * Matches each entity loaded against the current request to resolve the requested Seo entity.
 */
class CurrentEntityResolver
{
    public function __construct(
        private readonly CurrentSeoEntity $currentSeoEntity,
        private readonly Canonical $canonical,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function postLoad(PostLoadEventArgs $args): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return;
        }

        $entity = $args->getObject();

        if (!$entity instanceof RoutableModelInterface) {
            return;
        }

        if (null !== $this->currentSeoEntity->get()) {
            return;
        }

        if ($this->canonical->path($entity) !== $request->getPathInfo()) {
            return;
        }

        $this->currentSeoEntity->set($entity);
    }
}
