<?php

namespace Umanit\SeoBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Umanit\SeoBundle\UrlHistory\UrlPool;

class UrlRedirector implements EventSubscriberInterface
{
    /** @var UrlPool */
    private $pool;

    /** @var int */
    private $httpRedirectCode;

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => ['onKernelException']];
    }

    public function __construct(UrlPool $pool, int $httpRedirectCode)
    {
        $this->pool = $pool;
        $this->httpRedirectCode = $httpRedirectCode;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = 50000 > Kernel::VERSION_ID ? $event->getException() : $event->getThrowable();

        if (!$exception instanceof NotFoundHttpException) {
            return;
        }

        $urlHistoryItem = $this->pool->get($event->getRequest()->getUri(), $event->getRequest()->getLocale());

        // Check that url is in pool
        if (null !== $urlHistoryItem) {
            $event->setResponse(new RedirectResponse($urlHistoryItem->getNewPath(), $this->httpRedirectCode));
        }
    }
}
