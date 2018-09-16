<?php

namespace Obullo\Http;

use Psr\{
    Container\ContainerInterface as Container,
    Http\Message\ServerRequestInterface as Request,
    Http\Message\ResponseInterface as Response
};
use Obullo\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
use Obullo\Router\{
    Router
};
use ReflectionClass;
use RuntimeException;

/**
 * Kernel
 *
 * @copyright 2018 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Kernel
{
    use ContainerAwareTrait;

    protected $stack;
    protected $router;
    protected $events;
    protected $argumentResolver;
    protected $controllerResolver;

    /**
     * Constructor
     * 
     * @param object $events             EventManager
     * @param object $router             Router
     * @param object $controllerResolver ControllerResolver
     * @param object $stack              Stack
     * @param object $argumentResolver   ArgumentResolver
     */
    public function __construct($events, Router $router, $controllerResolver, $stack, $argumentResolver)
    {
        $this->stack = $stack;
        $this->router = $router;
        $this->events = $events;
        $this->argumentResolver = $argumentResolver;
        $this->controllerResolver = $controllerResolver;
    }

    /**
     * Start application process
     * 
     * @param Request $request request
     * 
     * @return void
     */
    public function handle(Request $request) : Response
    {
        $container = $this->getContainer();

        if ($route = $this->router->matchRequest()) {
            $this->events->trigger('route.match', $this, ['route' => $route]);
        }
        $container->setService('router', $this->router);

        $this->controllerResolver->setRouter($this->router);
        $this->controllerResolver->setArgumentResolver($this->argumentResolver);
        $this->controllerResolver->setContainer($container);
        $this->controllerResolver->dispatch();

        $queue = $this->getQueue();  // Merge application queue
        if (! empty($queue)) {
            foreach ($queue as $value) {
                $this->stack = $this->stack->withMiddleware($value);
            }
        }
        $this->stack = $this->stack->withMiddleware(new SendResponse($this));

        return $this->stack->process($request);
    }

    /**
     * Dispatch application process from SendResponse middleware
     * 
     * @param Request $request Psr7 Request
     * 
     * @return null|Response
     */
    public function dispatch(Request $request)
    {
        $response = null;
        if ($this->controllerResolver->getClassIsCallable()) {

            $class  = $this->controllerResolver->getClassInstance();
            $method = $this->controllerResolver->getClassMethod();

            $reflection = new ReflectionClass($class);
            $this->argumentResolver->setReflectionClass($reflection);
            $this->argumentResolver->setContainer($this->getContainer());
            $this->argumentResolver->setArguments($this->router->getMatchedRoute()->getArguments());

            $injectedParameters = $this->argumentResolver->resolve($method);
            $response = call_user_func_array(
                array(
                    $class,
                    $method
                ),
                $injectedParameters
            );
        }
        return $response;
    }

    /**
     * Build route middlewares with dependencies
     * 
     * @return handler
     */
    protected function getQueue() : array
    {
        $container = $this->getContainer();
        $middlewares = array();
        if ($container->has('middleware')) {
            $middlewares = $container->get('middleware')
                ->getStack();
        }
        $middlewares = array_merge($this->router->getStack(), $middlewares);
        return $this->resolveMiddlewareArguments($middlewares);
    }

    /**
     * Resolve middlewares
     * 
     * @param array $middlewares middlewares
     * 
     * @return array
     */
    protected function resolveMiddlewareArguments(array $middlewares)
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
            $reflection = new ReflectionClass($class);
            $this->argumentResolver->clear();
            $this->argumentResolver->setReflectionClass($reflection);
            $this->argumentResolver->setArguments($arguments);
            $this->argumentResolver->setContainer($this->getContainer());
            $args = array();
            if ($reflection->hasMethod('__construct')) {
                $args = $this->argumentResolver->resolve('__construct');
            }
            $object = $reflection->newInstanceArgs($args);
            $resolvedMiddlewares[] = $object;
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
        $container = $this->getContainer();
        if ($container->has('cookie')) {
            foreach ($container->get('cookie')->toArray() as $name => $cookie) {
                setcookie(
                    $name,
                    $cookie['value'],
                    $cookie['expire'],
                    $cookie['path'],
                    $cookie['domain'],
                    $cookie['secure'],
                    $cookie['httpOnly']
                );   
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