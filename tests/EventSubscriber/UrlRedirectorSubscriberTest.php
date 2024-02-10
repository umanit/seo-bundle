<?php

declare(strict_types=1);

namespace Umanit\SeoBundle\Tests\EventSubscriber;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Umanit\SeoBundle\EventSubscriber\UrlRedirectorSubscriber;
use Umanit\SeoBundle\UrlHistory\UrlPool;

class UrlRedirectorSubscriberTest extends TestCase
{
    public function testIsCalledOnKernelException(): void
    {
        $urlPool = $this->getMockBuilder(UrlPool::class)->disableOriginalConstructor()->getMock();
        $subscriber = new UrlRedirectorSubscriber($urlPool, 301);

        $kernel = $this->getMockBuilder(KernelInterface::class)->getMock();
        $event = new ExceptionEvent($kernel, new Request(), 1, new NotFoundHttpException());

        $dispatcher = new EventDispatcher();
        $traceableEventDispatcher = new TraceableEventDispatcher(
            $dispatcher,
            new Stopwatch()
        );
        $traceableEventDispatcher->addSubscriber($subscriber);
        $traceableEventDispatcher->dispatch($event, KernelEvents::EXCEPTION);

        self::assertCount(1, $traceableEventDispatcher->getCalledListeners());
        self::assertEmpty($traceableEventDispatcher->getNotCalledListeners());
    }

    public function testIsNotCalledOnOtherKernelEvents(): void
    {
        $urlPool = $this->getMockBuilder(UrlPool::class)->disableOriginalConstructor()->getMock();
        $subscriber = new UrlRedirectorSubscriber($urlPool, 301);

        $kernel = $this->getMockBuilder(KernelInterface::class)->getMock();
        $requestEvent = new RequestEvent($kernel, new Request(), 1);
        $controllerEvent = new ControllerEvent($kernel, static function (): void {
        }, new Request(), 1);
        $controllerArgumentsEvent = new ControllerArgumentsEvent($kernel, static function (): void {
        }, [], new Request(), 1);
        $viewEvent = new ViewEvent($kernel, new Request(), 1, null);
        $responseEvent = new ResponseEvent($kernel, new Request(), 1, new Response());
        $finishRequestEvent = new FinishRequestEvent($kernel, new Request(), 1);
        $terminateEvent = new TerminateEvent($kernel, new Request(), new Response());

        $dispatcher = new EventDispatcher();
        $traceableEventDispatcher = new TraceableEventDispatcher(
            $dispatcher,
            new Stopwatch()
        );
        $traceableEventDispatcher->addSubscriber($subscriber);
        $traceableEventDispatcher->dispatch($requestEvent, KernelEvents::REQUEST);
        $traceableEventDispatcher->dispatch($controllerEvent, KernelEvents::CONTROLLER);
        $traceableEventDispatcher->dispatch($controllerArgumentsEvent, KernelEvents::CONTROLLER_ARGUMENTS);
        $traceableEventDispatcher->dispatch($viewEvent, KernelEvents::VIEW);
        $traceableEventDispatcher->dispatch($responseEvent, KernelEvents::RESPONSE);
        $traceableEventDispatcher->dispatch($finishRequestEvent, KernelEvents::FINISH_REQUEST);
        $traceableEventDispatcher->dispatch($terminateEvent, KernelEvents::TERMINATE);

        self::assertEmpty($traceableEventDispatcher->getCalledListeners());
        self::assertCount(1, $traceableEventDispatcher->getNotCalledListeners());
    }
}
