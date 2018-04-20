<?php

namespace Obullo\Mvc;

use Psr\{
    Container\ContainerInterface as Container,
    Http\Message\ServerRequestInterface as Request,
    Http\Message\ResponseInterface as Response
};
use Obullo\Mvc\Dependency\Resolver;
use Obullo\Mvc\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
use Obullo\Stack\Builder as Stack;
use Obullo\Mvc\Middleware\{
    SendResponse,
    ErrorMiddlewareInterface
};
use Obullo\Router\{
    RequestContext,
    RouteCollection,
    Builder,
    Router
};
use ReflectionClass;
use RuntimeException;
use Zend\EventManager\EventManagerInterface;

/**
 * Mvc application
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Application implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $router;
    protected $events;
    protected $dispatcher;

    /**
     * Constructor
     * 
     * @param Container $container zend service manager
     * @param array     $listeners event listeners
     */
    public function __construct(Container $container, array $listeners = [])
    {
        $this->events = $container->get('events');
        $this->container = $container;
        $this->createListeners($listeners);
    }

    /**
     * Boot
     * 
     * @param array $listeners event listeners
     * 
     * @return void
     */
    protected function createListeners(array $listeners = [])
    {
        $events    = $this->events;
        $container = $this->getContainer();
        foreach ($listeners as $listener) {
            $object = new $listener;
            if ($object instanceof ContainerAwareInterface) {
                $object->setContainer($container);
            }
            $object->attach($events);
        }
    }

    /**
     * Start
     * 
     * @param  array  $listeners event listeners
     * @return void
     */
    public function start(array $listeners = [])
    {
        $container = $this->getContainer();
        $events    = $this->events;

        $events->trigger('session.start');

        $router = $this->createRouter($events);
        
        $this->dispatcher = new Dispatcher($router);
        $this->dispatcher->setContainer($container);
        $this->dispatcher->dispatch();
    }

    /**
     * Create event manager
     * 
     * @param EventManagerInterface $events events
     * 
     * @return router object
     */
    protected function createRouter(EventManagerInterface $events)
    {
        $container = $this->getContainer();
        $events    = $this->events;

        $context = new RequestContext;
        $context->fromRequest($container->get('request'));

        $result = $events->trigger('route.types', $this);

        $collection = new RouteCollection(array(
            'types' => $result->last()
        ));
        $collection->setContext($context);
        $builder = new Builder($collection);

        $routes = $container->get('loader')
            ->loadConfigFile('routes.yaml');
        
        $args = ['builder' => $builder, 'routes' => $routes];
        $result = $events->trigger('route.builder', $this, $args);

        $router = new Router($collection);
        if ($route = $router->matchRequest()) {
            $events->trigger('route.match', $this, $route);
        }
        $container->setService('router', $router);
        return $this->router = $router;
    }

    /**
     * Returns to router
     * 
     * @return object
     */
    public function getRouter() : Router
    {
        return $this->router;
    }

    /**
     * Returns to app dispatcher
     * 
     * @return object
     */
    public function getDispatcher() : Dispatcher
    {
        return $this->dispatcher;
    }

    /**
     * Merge application queue
     * 
     * @param array  $queue user queue
     * 
     * @return array
     */
    public function mergeQueue(array $queue) : array
    {
        $appQueue = $this->getQueue();
        if (! empty($appQueue)) {
            $queue = array_merge($queue, $appQueue);
        }
        return $queue;
    }

    /**
     * Start application process
     * 
     * @param array $queue stack queue
     * @param Request $request request
     * 
     * @return void
     */
    public function process(array $queue, Request $request) : Response
    {
        $stack = new Stack;
        $queue[] = new SendResponse($this);
        foreach ($queue as $value) {
            if ($value instanceof ContainerAwareInterface) {
                $value->setContainer($this->getContainer());
            }
            $stack = $stack->withMiddleware($value);
        }
        return $stack->process($request);
    }

    /**
     * Build route middlewares with dependencies
     * 
     * @return handler
     */
    public function getQueue() : array
    {
        $container = $this->getContainer();
        $middlewares = array();
        if ($container->has('middleware')) {
            $middlewares = $container->get('middleware')
                ->getStack();
        }
        $middlewares = array_merge($this->router->getStack(), $middlewares);
        return $this->resolveMiddlewares($middlewares);
    }

    /**
     * Resolve middlewares
     * 
     * @param array $middlewares middlewares
     * 
     * @return array
     */
    protected function resolveMiddlewares(array $middlewares)
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
            $resolver = new Resolver($reflection);
            $resolver->setArguments($arguments);
            $resolver->setContainer($this->getContainer());
            $args = array();
            if ($reflection->hasMethod('__construct')) {
                $args = $resolver->resolve('__construct');
            }
            $object = $reflection->newInstanceArgs($args);
            $resolvedMiddlewares[] = $object;
        }
        return $resolvedMiddlewares;
    }

    /**
     * Handle application process
     * 
     * @param Request $request Psr7 Request
     * 
     * @return null|Response
     */
    public function handle(Request $request)
    {
        $response = null;
        if ($this->dispatcher->getClassIsCallable()) {
            $class  = $this->dispatcher->getClassInstance();
            $method = $this->dispatcher->getClassMethod();
            $reflection = new ReflectionClass($class);
            $resolver = new Resolver($reflection);
            $resolver->setContainer($this->getContainer());
            $resolver->setArguments($this->router->getMatchedRoute()->getArguments());
            $injectedParameters = $resolver->resolve($method);
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
     * Emit response
     * 
     * @return void
     */
    public function sendResponse(Response $response)
    {
        if (headers_sent()) {
            throw new RuntimeException('Unable to emit response; headers already sent');
        }
        if (ob_get_level() > 0 && ob_get_length() > 0) {
            throw new RuntimeException('Output has been emitted previously; cannot emit response');
        }
        $this->events->trigger('before.headers', $this, $response);
        $this->emitHeaders($response);
        $this->events->trigger('before.emit', $this, $response);
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

    /**
     * Terminate application
     * 
     * @return void
     */
    public function terminate() {}
}