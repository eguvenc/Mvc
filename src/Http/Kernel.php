<?php

namespace Obullo\Http;

use Psr\{
    Container\ContainerInterface as Container,
    Http\Message\ServerRequestInterface as Request,
    Http\Message\ResponseInterface as Response
};
use ReflectionClass;
use Zend\EventManager\{
    Event,
    EventManagerInterface as EventManager
};
use Obullo\Router\{
    Router
};
use Obullo\Http\{
    ControllerResolver,
    Exception\RuntimeException,
    SubRequestInterface as SubRequest
};
use Obullo\Error\ErrorTemplate;

/**
 * Kernel of micro mvc
 *
 * @copyright 2018-2019 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Kernel implements HttpKernelInterface
{
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
     */
    public function __construct(EventManager $events, Router $router, $controllerResolver)
    {
        $this->router = $router;
        $this->events = $events;
        $this->container = $controllerResolver->getContainer();
        $this->controllerResolver = $controllerResolver;
        $this->controllerResolver->setRouter($router);
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
     * Handle request & http process
     * 
     * @param Request $request request
     * 
     * @return object
     */
    public function handleRequest(Request $request) : Response
    {
        $route = $this->router->matchRequest();
        if ($route && is_callable($route->getHandler())) {
            $this->events->trigger('route.match', null, ['route' => $route]);
            $handler = $route->getHandler();
            $bundle = $this->createBundle($handler, $request);
            $this->controllerResolver->resolve($handler);
            $class = $this->controllerResolver->getClassInstance();
            $response = $class->handlePsr7Response($bundle, $request, $this);
            return $response;
        }
        $error = new ErrorTemplate;
        $error->setTranslator($this->container->get('translator'));
        $error->setView($this->container->get('view'));
        $error->setStatusCode(404);
        $error->setMessage(404);

        return $error->renderView('View::_404.phtml');
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
        
        if (false == is_callable($handler)) {
            throw new RuntimeException(
                sprintf("The sub view '%s' could not found.", $handler)
            );
        }
        $this->createBundle($handler, $request);
        $this->controllerResolver->resolve($handler);
        $response = $this->dispatch($request, $params);
        return $response;
    }

    /**
     * Create bundle  & error listeners
     * 
     * @param  string $handler handler
     * @param  object $request psr7 request
     * 
     * @return void
     */
    protected function createBundle($handler, $request)
    {
        $bundleName = strstr($handler, '\\', true);
        $bundleClass = $bundleName.'\Bundle';
        $bundle = null;
        if (! class_exists($bundleClass, false)) { // prevent bundle creation for multiple sub requests.
            $bundle = new $bundleClass;
            $bundle->setContainer($this->container);
            $bundle->setRequest($request);
            $bundle->onBootstrap();
        }
        return $bundle;
    }

    /**
     * Dispatch application process
     * 
     * @param  array   $arguments arguments
     * @param  Request $request   Psr7 Request / Sub Request 
     * 
     * @return Response
     */
    public function dispatch(Request $request, $arguments = array()) : Response
    {
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
        $response = $class->$method(...$args);
        return $response;
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