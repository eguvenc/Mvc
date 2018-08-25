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
use App\Middleware\Error;

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
        return $handler->process(new Error('404'));
    }
}