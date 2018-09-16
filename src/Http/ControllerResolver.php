<?php

namespace Obullo\Http;

use Obullo\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
use ReflectionClass;
use Obullo\Router\Router;

/**
 * Controller resolver
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class ControllerResolver implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $name;
    protected $class;
    protected $router;
    protected $method;
    protected $classInstance;
    protected $argumentResolver;
    protected $isCallable = false;

    /**
     * Constructor
     * 
     * @param Router $router router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
        $this->name = 'App';
    }

    /**
     * Constructor
     * 
     * @param ArgumentResolver $argumentResolver 
     */
    public function setArgumentResolver($argumentResolver)
    {
        $this->argumentResolver = $argumentResolver;
    }

    /**
     * Dispatch request
     * 
     * @return void
     */
    public function dispatch()
    {
        if (false == $this->router->hasMatch()) {
            return;
        }
        $handler = $this->router->getMatchedRoute()
            ->getHandler();

        if (is_callable($handler)) {
            $this->isCallable = true;
            $this->resolveHandler($handler);
        }
    }

    /**
     * Resolve handler
     * 
     * @param  string $handler handler
     * @return void
     */
    protected function resolveHandler(string $handler)
    {        
        $container = $this->getContainer();
        $explodeMethod = explode('::', $handler);
        $this->class  = $explodeMethod[0];
        $this->method = $explodeMethod[1];
        $explode  = explode('\\', $this->class);
        $this->name = (string)$explode[0];
        $reflection = new ReflectionClass($this->class);

        $container->setFactory('middleware', function(){
            return new Middleware($this);
        });
        if ($reflection->hasMethod('__construct')) {
            $this->argumentResolver->clear();
            $this->argumentResolver->setReflectionClass($reflection);
            $this->argumentResolver->setContainer($this->getContainer());

            $injectedParameters = $this->argumentResolver->resolve('__construct');
            $this->classInstance = $reflection->newInstanceWithoutConstructor();
            $this->classInstance->setContainer($container);
            
            call_user_func_array(
                array(
                    $this->classInstance,
                    '__construct'
                ),
                $injectedParameters
            );
        } else {
            $this->classInstance = $reflection->newInstanceWithoutConstructor();
            $this->classInstance->setContainer($container);
        }
    }

    /**
     * Returns to first namespace e.g. 'App'.
     * 
     * @return string
     */
    public function getFirstNamespace() : string
    {
        return $this->name;
    }

    /**
     * Returns to is callable
     * 
     * @return string
     */
    public function getClassIsCallable() : bool
    {
        return $this->isCallable;
    }

    /**
     * Returns to resolved class name
     * 
     * @return string
     */
    public function getClassName() : string
    {
        return $this->class;
    }

    /**
     * Returns to resolved class method
     * 
     * @return string
     */
    public function getClassMethod() : string
    {
        return $this->method;
    }

    /**
     * Returns to resolved controller instance
     * 
     * @return object
     */
    public function getClassInstance()
    {
        return $this->classInstance;
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
}