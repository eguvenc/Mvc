<?php

namespace Obullo\Http;

use Psr\{
    Container\ContainerInterface as Container,
    Http\Message\ServerRequestInterface as Request,
    Http\Message\ResponseInterface as Response
};
use Obullo\Router\{
    Router
};
use ReflectionClass;
use Zend\EventManager\Event;
use Zend\Diactoros\Response\EmptyResponse;
use Obullo\Http\Exception\RuntimeException;
use Obullo\Http\SubRequestInterface as SubRequest;

/**
 * Kernel of micro mvc
 *
 * @copyright 2019 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Kernel implements HttpKernelInterface
{
    protected $queue = array();
    protected $router;
    protected $events;
    protected $container;
    protected $controllerResolver;

    /**
     * Constructor
     * 
     * @param object $events             EventManager
     * @param object $router             Router
     * @param object $controllerResolver ControllerResolver
     * @param object $queue              Stack queue (optional)
     */
    public function __construct($events, Router $router, $controllerResolver, $queue = array())
    {
        $this->queue  = $queue;
        $this->router = $router;
        $this->events = $events;
        $this->container = $controllerResolver->getContainer();
        $this->controllerResolver = $controllerResolver;
        $this->controllerResolver->setRouter($router);
    }

    /**
     * Handle request & http process
     * 
     * @param Request $request request
     * 
     * @return object
     */
    public function handleRequest(Request $request) : Response
    {
        $params = array();
        $handler = null;
        if ($route = $this->router->matchRequest()) {
            $params  = $route->getArguments();
            $handler = $route->getHandler();
            $this->events->trigger('route.match', null, ['route' => $route]);
        }
        $response = $this->dispatch($handler, $request, $params);
        return $response;
    }

    /**
     * Handle SubRequest & hmvc process
     * 
     * @param  SubRequest $request subrequest
     * 
     * @return object
     */
    public function handleSubRequest(SubRequest $request) : Response
    {
        $this->container->setAllowOverride(true);  // allow override for every request
        $this->container->setService('subRequest', $request);
        $this->container->setAllowOverride(false); // restore default functionality

        $params  = $request->getAttribute('params');
        $handler = $request->getAttribute('handler');
        
        $response = $this->dispatch($handler, $request, $params);
        return $response;
    }

    /**
     * Create bundle event
     * 
     * @param  string $handler handler
     * @return void
     */
    protected function createBundleEvents($handler, $request)
    {
        list($bundle) = explode('\\', $handler);

        $errorListener  = '\\'.$bundle.'\Event\ErrorListener';
        $bundleListener = '\\'.$bundle.'\Event\BundleListener';

        $object = new $bundleListener;
        $object->setContainer($this->container);
        $object->attach($this->events);

        $event = new Event;
        $event->setName($bundle.'.bootstrap');
        $event->setTarget($this); 
        $this->events->triggerEvent($event); // creates bootstrap event foreach bundles.

        if (false == $request instanceof SubRequest) {  // set error listeners only for master requests.
            $object = new $errorListener;
            $object->setContainer($this->container);
            $object->attach($this->events);
        }
    }

    /**
     * Dispatch application process
     * 
     * @param  Request $request   Psr7 Request / Sub Request 
     * @param  array   $arguments arguments
     * 
     * @return Response
     */
    public function dispatch($handler, Request $request, $arguments = array()) : Response
    {
        if (is_callable($handler)) {
            $this->createBundleEvents($handler, $request);
            $this->controllerResolver->resolve($handler);
            $args = array();
            $class  = $this->controllerResolver->getClassInstance();
            $method = $this->controllerResolver->getClassMethod();

            $reflection = new ReflectionClass($class);
            $parameters = $reflection->getMethod($method)->getParameters();
            foreach ($parameters as $param) {
                $name = $param->getName();
                if (isset($arguments[$name])) {
                    $args[] = $arguments[$name];
                }
            }      
            $response = call_user_func_array(
                array(
                    $class,
                    $method
                ),
                $args
            );
            return $response;
        }
        return new EmptyResponse(404);
    }

    /**
     * Build application middlewares with dependencies
     * 
     * @return handler
     */
    public function getMiddlewares() : array
    {
        $middlewares = array();
        $instance = $this->controllerResolver->getClassInstance();
        if (is_object($instance) && $instance->middlewareManager !== null) {
            $middlewares = $instance->middlewareManager->getStack();
        }
        /**
         * Middleware order
         * 
         * 1 - Global (index.php) middlewares
         * 2 - Route middlewares
         * 3 - Controller middlewares
         */
        $queue = $this->queue;
        $queue = array_merge($queue, $this->router->getStack());
        $queue = array_merge($queue, $middlewares);
        $queue = $this->resolveDependencies($queue);
        return $queue;
    }

    /**
     * Resolve middleware dependencies
     * 
     * @param array $middlewares middlewares
     * 
     * @return array
     */
    protected function resolveDependencies(array $middlewares)
    {
        $resolvedMiddlewares = array();
        foreach ($middlewares as $data) {
            $class = $data;
            $arguments = array();
            if (is_array($data)) {
                $class = $data['class'];
                $arguments = $data['arguments'];
                $data = $class;
            }
            $resolvedMiddlewares[] = new $class(...$arguments);
        }
        return $resolvedMiddlewares;
    }

    /**
     * Emit response
     * 
     * @return void
     */
    public function send(Response $response)
    {
        if (headers_sent()) {
            throw new RuntimeException('Unable to emit response; headers already sent');
        }
        if (ob_get_level() > 0 && ob_get_length() > 0) {
            throw new RuntimeException('Output has been emitted previously; cannot emit response');
        }
        $result = $this->events->trigger('before.headers', $this, $response);
        $response = $result->last();
        $this->emitHeaders($response);
        $result = $this->events->trigger('before.emit', $this, $response);
        $response = $result->last();
        $this->emitBody($response);
        $this->events->trigger('after.emit', $this, $response);
    }

    /**
     * Emit headers
     *
     * @return void
     */
    protected function emitHeaders($response)
    {
        $statusCode = $response->getStatusCode();
        foreach ($response->getHeaders() as $header => $values) {
            $name = $header;
            foreach ($values as $value) {
                header(sprintf(
                    '%s: %s',
                    $name,
                    $value
                ), true, $statusCode);
            }
        }
    }

    /**
     * Emit body
     *
     * @return void
     */
    protected function emitBody($response)
    {
        echo $response->getBody();
    }
}