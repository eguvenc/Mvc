<?php

namespace Obullo\Mvc;

use Psr\{
    Container\ContainerInterface as Container,
    Http\Message\ServerRequestInterface as Request,
    Http\Message\ResponseInterface as Response
};
use Obullo\Mvc\Dependency\Resolver;
use Obullo\Mvc\{
    Container\ContainerAwareTrait,
    Container\ContainerAwareInterface
};
use ReflectionClass;
use RuntimeException;

/**
 * Mvc application
 *
 * @copyright 2018 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Application implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private $stack;
    private $module;

    /**
     * Constructor
     * 
     * @param object $module
     */
    public function __construct(HttpModule $module)
    {
        $this->module = $module;
        $this->setContainer($module->getContainer());
    }

    /**
     * Returns to module object
     * 
     * @return object
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Returns to env
     * 
     * @return string
     */
    public function getEnv() : string
    {
        return $this->module->getEnv();
    }

    /**
     * Process request
     *
     * @param Request $request request
     * 
     * @return void
     */
    public function process(Request $request)
    {
        $handler = $this->getMiddleware();
        return $handler->process($request);
    }
    
    /**
     * Build route middlewares with dependencies
     * 
     * @return handler
     */
    public function build() : array
    {
        $container = $this->getContainer();
        $appMiddlewares = array();
        $controllerStack = array();
        $routerStack = $container->get('router')->getStack();
        if ($container->has('middleware')) {
            $controllerStack = $container->get('middleware')
                ->getStack();
        }
        $appMiddlewares = array_merge($routerStack, $controllerStack);
        return $this->resolveMiddlewares($appMiddlewares);
    }

    /**
     * Resolve middlewares
     * 
     * @param array $appMiddlewares middlewares
     * 
     * @return array
     */
    protected function resolveMiddlewares(array $appMiddlewares)
    {
        $middlewares = array();
        foreach ($appMiddlewares as $data) {
            $class = $data;
            $arguments = array();
            if (is_array($data)) {
                $class = $data['class'];
                $arguments = $data['arguments'];
            }
            $reflection = new ReflectionClass($class);
            $resolver = new Resolver($reflection);
            $resolver->setArguments($arguments);
            $resolver->setContainer($this->getContainer());
            $args = array();
            if ($reflection->hasMethod('__construct')) {
                $args = $resolver->resolve('__construct');
            }
            $middlewares[] = $reflection->newInstanceArgs($args);
        }
        return $middlewares;
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
        $container = $this->getContainer();
        $container->setService('request', $request);
        $response = null;
        if ($this->module->getClassIsCallable()) {
            $class  = $this->module->getClassInstance();
            $method = $this->module->getClassMethod();
            $reflection = new ReflectionClass($class);
            $resolver = new Resolver($reflection);
            $resolver->setContainer($container);
            $resolver->setArguments($this->module->getRouteArguments());
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
    public function emit(Response $response)
    {
        if (headers_sent()) {
            throw new RuntimeException('Unable to emit response; headers already sent');
        }
        if (ob_get_level() > 0 && ob_get_length() > 0) {
            throw new RuntimeException('Output has been emitted previously; cannot emit response');
        }
        $this->emitHeaders($response);
        $this->emitBody($response);
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