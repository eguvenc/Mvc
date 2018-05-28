<?php

namespace Obullo\Mvc\Middleware;

use Psr\Http\{
    Message\ResponseInterface,
    Message\ServerRequestInterface as Request,
    Server\MiddlewareInterface,
    Server\RequestHandlerInterface as RequestHandler
};
use Obullo\Mvc\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
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
                
                $events->trigger('http.method.notAllowed', null, $methods);
                $result = $events->trigger('http.method.notAllowed.message', null, $methods);
                $message = $result->last();

                $errorMiddleware = new Error(
                    '405',
                    $message,
                    ['Allow' => implode(', ', $methods)]
                );
                $errorMiddleware->setContainer($container);
                
                return $handler->process($errorMiddleware);
            } else {
                $events->trigger('http.method.allowed', null, $methods);
            }
        }
        return $handler->handle($request);
    }
}