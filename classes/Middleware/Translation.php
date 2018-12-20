<?php

namespace Middleware;

use Psr\Http\{
    Message\ResponseInterface as Response,
    Message\ServerRequestInterface as Request,
    Server\MiddlewareInterface,
    Server\RequestHandlerInterface as RequestHandler
};
use Obullo\Router\Router;
use Zend\I18n\Translator\Translator;

class Translation implements MiddlewareInterface
{
    /**
     * Constructor
     * 
     * @param Router     $router     router
     * @param Translator $translator translator
     */
    public function __construct(Router $router, Translator $translator)
    {
        $this->router = $router;
        $this->translator = $translator;
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
        if ($this->router->hasMatch()) {
            $route = $this->router->getMatchedRoute();
            $this->translator->setLocale($route->getArgument('locale'));
            $route->removeArgument('locale');
        }
        return $handler->handle($request);
    }
}