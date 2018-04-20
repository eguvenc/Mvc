<?php

namespace Obullo\Mvc\Middleware;

use Psr\Http\{
    Server\MiddlewareInterface,
    Server\RequestHandlerInterface as RequestHandler,
    Message\ResponseInterface,
    Message\ServerRequestInterface as Request
};
use Interop\Container\ContainerInterface;
use Obullo\Mvc\Application;
use Obullo\Mvc\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
class SendResponse implements MiddlewareInterface,ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Constructor
     * 
     * @param Application $application application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
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
        $container = $this->getContainer();

        $router = $container->get('router');
        $events = $container->get('events');

        $response = null;
        if ($router->hasMatch()) {
            $response = $this->application->handle($request);
            if ($response instanceof ResponseInterface) {
                return $response;
            }
        }
        $result = $events->trigger('error.404',null,['request' => $request, 'response' => $response]);
        $errorMiddleware = $result->last();
        $errorMiddleware->setContainer($container);
        
        return $handler->process($errorMiddleware);
    }
}