<?php

namespace App\Middleware;

use Psr\Http\{
    Message\ResponseInterface,
    Message\ServerRequestInterface as Request,
    Server\MiddlewareInterface,
    Server\RequestHandlerInterface as RequestHandler
};
use Obullo\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
use Zend\EventManager\Event;

class HttpMethod implements MiddlewareInterface,ContainerAwareInterface
{
    use ContainerAwareTrait;

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
        $container = $this->getContainer();

        $events = $container->get('events');
        $router = $container->get('router');

        if ($router->hasMatch()) {

            $methods = $router->getMatchedRoute()
                ->getMethods();

            if (! in_array($request->getMethod(), $methods)) {
                
                $event = new Event;
                $event->setName('method.notAllowed');
                $event->setParam('methods', $methods);
                $message = $events->triggerEvent($event)->last();

                return $handler->process(
                    new Error('405',$message,['Allow' => implode(', ', $methods)])
                );
            }
            $event = new Event;
            $event->setName('method.allowed');
            $event->setParam('methods', $methods);
            $events->triggerEvent($event);
        }
        return $handler->handle($request);
    }
}