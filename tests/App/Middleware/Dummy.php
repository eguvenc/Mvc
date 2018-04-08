<?php

namespace Tests\App\Middleware;

use Psr\Http\{
    Message\ResponseInterface,
    Message\ServerRequestInterface as Request,
    Server\MiddlewareInterface,
    Server\RequestHandlerInterface as RequestHandler
};
class Dummy implements MiddlewareInterface
{
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
        return $handler->handle($request);
    }
}