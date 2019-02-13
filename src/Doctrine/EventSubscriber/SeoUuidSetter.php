<?php

namespace Umanit\SeoBundle\Doctrine\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Ramsey\Uuid\Uuid;
use Umanit\SeoBundle\Doctrine\Model\UrlHistorizedInterface;

/**
 * Class SeoUuidSetter
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class SeoUuidSetter implements EventSubscriber
{
    /**
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [];
    }
    //
    // /**
    //  * Generates and set the SeoUuid.
    //  *
    //  * @param LifecycleEventArgs $args
    //  *
    //  * @throws \Exception
    //  */
    // public function prePersist(LifecycleEventArgs $args): void
    // {
    //     $seoEntity = $args->getEntity();
    //     if ($seoEntity instanceof UrlHistorizedInterface && null === $seoEntity->getSeoUuid()) {
    //         $seoEntity->setSeoUuid(Uuid::uuid4());
    //     }
    // }
}
