<?php

namespace Middleware;

use Psr\Http\{
    Message\ResponseInterface,
    Message\ServerRequestInterface as Request,
    Server\MiddlewareInterface,
    Server\RequestHandlerInterface as RequestHandler
};
use Obullo\Router\Router;
use Zend\EventManager\{
    Event,
    EventManager
};
class HttpMethod implements MiddlewareInterface
{
    /**
     * Constructor
     * 
     * @param Router       $router router
     * @param EventManager $events events
     */
    public function __construct(Router $router, EventManager $events)
    {
        $this->router = $router;
        $this->events = $events;
    }

    /**
     * Process request
     *
     * @param ServerRequestInterface  $request  request
     * @param RequestHandlerInterface $handler
     *
     * @return object ResponseInterface
     */
    public function process(Request $request, RequestHandler $handler) : ResponseInterface
    {
        if ($this->router->hasMatch()) {
            $methods = $this->router->getMatchedRoute()
                ->getMethods();

            if (! in_array($request->getMethod(), $methods)) {
                $event = new Event;
                $event->setName('dissallowed.method');
                $event->setParam('methods', $methods);
                $message = $this->events->triggerEvent($event)->last();

                return $handler->process(
                    new Error('405',$message,['Allow' => implode(', ', $methods)])
                );
            }
            $event = new Event;
            $event->setName('allowed.method');
            $event->setParam('methods', $methods);
            $this->events->triggerEvent($event);
        }
        return $handler->handle($request);
    }
}