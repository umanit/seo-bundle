<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Doctrine\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\HttpFoundation\RequestStack;
use Umanit\SeoBundle\Model\RoutableModelInterface;
use Umanit\SeoBundle\Routing\Canonical;
use Umanit\SeoBundle\Runtime\CurrentSeoEntity;

/**
 * Matches each entity loaded against the current request to resolve the requested Seo entity.
 */
class CurrentEntityResolver implements EventSubscriber
{
    public function __construct(
        private readonly CurrentSeoEntity $currentSeoEntity,
        private readonly Canonical $canonical,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [Events::postLoad];
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return;
        }

        $entity = $args->getEntity();

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
