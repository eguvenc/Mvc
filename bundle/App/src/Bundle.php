<?php

namespace App;

use Obullo\Http\HttpBundle;
use Obullo\Stack\Builder as Stack;

class Bundle extends HttpBundle
{
    public function onStack(Stack $stack) : Stack
    {
        $stack = $stack->withMiddleware($this->container->get('Middleware\HttpMethod'));
        return $stack;
    }

	public function onBootstrap()
    {
        $this->setErrorHandler();

        // Configure container to auto wire
        //  
        $routes = $this->container->get('config')->routes;
        foreach ($routes as $route) {
            $controller = strstr($route->handler, '::', true);
            if (strstr($route->handler, __NAMESPACE__.'\Controller')) { // set current bundle routes
                $factories[$controller] = '\\'.__NAMESPACE__.'\LazyControllerFactory';
            }
        }
        $this->container->configure(
            [
                'factories' => $factories
            ]
        );
    }

    protected function setErrorHandler()
    {
        $this->container->setFactory('App\ErrorHandler', 'Service\ErrorHandlerFactory');
        $this->container->build('App\ErrorHandler');
    }
}