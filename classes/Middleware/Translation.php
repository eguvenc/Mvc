<?php

namespace Middleware;

use Psr\Http\{
    Message\ResponseInterface as Response,
    Message\ServerRequestInterface as Request,
    Server\MiddlewareInterface,
    Server\RequestHandlerInterface as RequestHandler
};
use Obullo\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
class Translation implements MiddlewareInterface,ContainerAwareInterface
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
    public function process(Request $request, RequestHandler $handler) : Response
    {
        $container = $this->getContainer();
        
        $router     = $container->get('router');
        $translator = $container->get('translator');

        if ($router->hasMatch()) {
            $route = $router->getMatchedRoute();
            $translator->setLocale($route->getArgument('locale'));
            $route->removeArgument('locale');
        }
        return $handler->handle($request);
    }
}