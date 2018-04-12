<?php

namespace Obullo\Mvc;

use ReflectionClass;
use Obullo\Router\Router;
use Obullo\Mvc\Dependency\Resolver;
use Obullo\Mvc\Container\ContainerAwareTrait;
use Obullo\Http\Stack\StackInterface as Stack;

/**
 * Http module
 *
 * @copyright 2018 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class HttpModule
{
    use ContainerAwareTrait;

    protected $name;
    protected $class;
    protected $module;
    protected $router;
    protected $method;
    protected $methods = array();
    protected $classInstance;
    protected $isCallable = false;

    /**
     * Constructor
     * 
     * @param Router $router router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->name = 'App';
    }

    /**
     * Set stack
     * 
     * @param Stack $stack stack
     */
    public function setStack(Stack $stack)
    {
        $this->stack = $stack;
    }

    /**
     * Returns to http stack
     * 
     * @return object
     */
    public function getStack() : Stack
    {
        return $this->stack;
    }

    /**
     * Initialize to module
     * 
     * @return void
     */
    protected function init()
    {
        $container = $this->getContainer();

        if ($this->router->hasMatch() && $handler = $this->router->getMatchedRoute()
            ->getHandler()) {
            if (is_callable($handler)) {
                $this->isCallable = true;
                $explodeMethod = explode('::', $handler);
                $this->class  = $explodeMethod[0];
                $this->method = $explodeMethod[1];
                $explode  = explode('\\', $this->class);
                $this->name = (string)$explode[0];
                $reflection = new ReflectionClass($this->class);
                $this->classInstance = $reflection->newInstanceWithoutConstructor();
                $this->methods = get_class_methods($this->classInstance);
                
                $container->setFactory('middleware', function(){
                    return new \Obullo\Mvc\Middleware($this);
                });
                $this->classInstance->setContainer($container);
                if ($reflection->hasMethod('__construct')) {
                    $this->classInstance->__construct();
                }
            }
        }
    }

    /**
     * Build services
     * 
     * @return void
     */
    public function build(string $env)
    {
        $this->env = $env;
        $this->init();
        $class = $this->getName().'\\Module';
        $module = new $class($this);
        $module->init();
        $module->buildServices();

        return $module;
    }

    /**
     * Returns to module name if it's not empty
     * otherwise returns to 'App' module.
     * 
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Returns to env
     * 
     * @return string
     */
    public function getEnv() : string
    {
        return $this->env;
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
     * Returns to resolved class methods
     * 
     * @return string
     */
    public function getClassMethods() : array
    {
        return $this->methods;
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
     * Returns to resolved method variables
     * 
     * @return array
     */
    public function getRouteArguments() : array
    {
        return $this->router->getMatchedRoute()->getArguments();
    }
}