<?php

namespace Umanit\SeoBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Umanit\SeoBundle\UrlHistory\UrlPool;

/**
 * Redirects old urls to new ones.
 */
class UrlRedirector implements EventSubscriberInterface
{
    /** @var UrlPool */
    private $pool;

    /** @var bool */
    private $useUrlHistorization;

    /** @var int */
    private $httpRedirectCode;

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => ['onKernelException']];
    }

    public function __construct(UrlPool $pool, bool $useUrlHistorization, int $httpRedirectCode = 301)
    {
        $this->pool = $pool;
        $this->useUrlHistorization = $useUrlHistorization;
        $this->httpRedirectCode = $httpRedirectCode;
    }

    /**
     * Redirects an old url to a new one.
     *
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$this->useUrlHistorization || !$event->getThrowable() instanceof NotFoundHttpException) {
            return;
        }

        $path = $event->getRequest()->getUri();
        $locale = $event->getRequest()->getLocale();
        $urlHistoryItem = $this->pool->get($path, $locale);

        // Check that url is in pool
        if (null !== $urlHistoryItem) {
            $event->setResponse(new RedirectResponse($urlHistoryItem->getNewPath(), $this->httpRedirectCode));
        }
    }
}
