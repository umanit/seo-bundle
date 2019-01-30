<?php

namespace Umanit\SeoBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Umanit\SeoBundle\UrlHistory\UrlPool;

/**
 * Class UrlRedirector
 *
 * Redirects old urls to new ones.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class UrlRedirector implements EventSubscriberInterface
{
    /** @var UrlPool */
    private $pool;

    /** @var int */
    private $httpRedirectCode;

    public static function getSubscribedEvents()
    {
        return [KernelEvents::EXCEPTION => ['onKernelException']];
    }

    /**
     * UrlRedirector constructor.
     *
     * @param UrlPool $pool
     * @param int     $httpRedirectCode
     */
    public function __construct(UrlPool $pool, int $httpRedirectCode = 301)
    {
        $this->pool             = $pool;
        $this->httpRedirectCode = $httpRedirectCode;
    }

    /**
     * Redirects an old url to a new one.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        if (!$event->getException() instanceof NotFoundHttpException) {
            return;
        }

        $path           = $event->getRequest()->getPathInfo();
        $locale         = $event->getRequest()->getLocale();
        $urlHistoryItem = $this->pool->get($path, $locale);

        // Check that url is in pool
        if (null !== $urlHistoryItem) {
            $event->setResponse(new RedirectResponse($urlHistoryItem->getNewPath(), $this->httpRedirectCode));
        }
    }

}
