<?php

namespace Middleware;

use Psr\Http\{
    Server\MiddlewareInterface,
    Server\RequestHandlerInterface as RequestHandler,
    Message\ResponseInterface as Response,
    Message\ServerRequestInterface as Request
};
use Interop\Container\ContainerInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Obullo\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
use Middleware\Error;

class SendResponse implements MiddlewareInterface,ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $response;

    /**
     * Constructor
     * 
     * @param null|object $response Psr7 Response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Process request
     *
     * @param ServerRequestInterface  $request  request
     * @param RequestHandlerInterface $handler
     *
     * @return object ResponseInterface
     */
    public function process(Request $request, RequestHandler $handler) : Response
    {
        $container = $this->getContainer();
        $router = $container->get('router');

        if ($this->response instanceof EmptyResponse) {
            return $handler->process(new Error('404'));
        }
        return $this->response;
    }
}