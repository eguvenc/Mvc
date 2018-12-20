<?php

namespace Obullo\Http;

use Middleware\SendResponse;
use Psr\Http\{
    Message\ResponseInterface as Response,
    Message\ServerRequestInterface as Request
};
use Obullo\Stack\Builder as Stack;

/**
 * Middleware Controller
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class MiddlewareController extends Controller
{
    protected $middlewareManager;

    /**
     * Handler psr7 response
     * 
     * @param  Bundle  $bundle  object
     * @param  Request $request request
     * @param  Kernel  $kernel  http kernel
     * @return void
     */
    public function handlePsr7Response($bundle, Request $request, Kernel $kernel) : Response
    {
        $stack = new Stack($this->getContainer());
        if (method_exists($bundle, 'onStack')) {
            $stack = $bundle->onStack($stack);
        }
        foreach ($this->getPsr15Middlewares() as $value) {
            $stack = $stack->withMiddleware($value);
        }
        $stack = $stack->withMiddleware(new SendResponse($kernel));
        $response = $stack->process($request);
        return $response;
    }

    /**
     * Returns to new middleware manager
     * 
     * @return object
     */
    protected function getMiddlewareManager()
    {
        $this->middlewareManager = new MiddlewareManager($this);
        return $this->middlewareManager;
    }

    /**
     * Build application middlewares with dependencies
     * 
     * @return handler
     */
    protected function getPsr15Middlewares() : array
    {
        $middlewares = array();
        if ($this->middlewareManager !== null) {
            $middlewares = $this->middlewareManager->getStack();
        }
        /**
         * Middleware order
         * 
         * 1 - Global (index.php) middlewares
         * 2 - Route middlewares
         * 3 - Controller middlewares
         */
        $queue = $this->container->get('router')->getStack();
        $queue = array_merge($queue, $middlewares);
        $queue = $this->resolvePsr15Dependencies($queue);
        return $queue;
    }

    /**
     * Resolve middleware dependencies
     * 
     * @param array $middlewares middlewares
     * 
     * @return array
     */
    protected function resolvePsr15Dependencies(array $middlewares)
    {
        $resolvedMiddlewares = array();
        foreach ($middlewares as $data) {
            $class = $data;
            $options = array();
            if (is_array($data)) {
                $class = $data['class'];
                $options = $data['arguments'];
            }
            $resolvedMiddlewares[] = $this->container->build($class, $options);
        }
        return $resolvedMiddlewares;
    }
}