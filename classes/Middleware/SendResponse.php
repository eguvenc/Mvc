<?php

namespace Middleware;

use Psr\Http\{
    Server\MiddlewareInterface,
    Server\RequestHandlerInterface as RequestHandler,
    Message\ResponseInterface as Response,
    Message\ServerRequestInterface as Request
};
use Obullo\Http\Kernel;

class SendResponse implements MiddlewareInterface
{
    protected $kernel;

    /**
     * Constructor
     * 
     * @param Kernel $kernel kernel
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
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
        $router = $this->kernel->getRouter();
        $route  = $router->getMatchedRoute();

        $response = $this->kernel->dispatch($request, $route->getArguments());
        return $response;
    }
}