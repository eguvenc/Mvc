<?php

namespace Obullo\Mvc;

use Obullo\Mvc\RouteDispatcher;

/**
 * Middleware manager
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Middleware
{
    protected $count = 0;
    protected $dispatcher;
    protected $stack = array();
    protected $middleware = array();

    /**
     * Constructor
     * 
     * @param RouteDispatcher $dispatcher dispatcher
     */
    public function __construct(RouteDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Add middleware
     * 
     * @param string $name middleware
     */
    public function add(string $name)
    {
        ++$this->count;
        $this->middleware[$this->count] = array(
            'method' => array('__construct'),
            'class' => $this->dispatcher->getFirstNamespace().'\Middleware\\'.$name,
            'arguments' => array(),
        );
        return $this;
    }

    /**
     * Set arguments
     * 
     * @param  string $name argument name
     * @param  mixed $arg arguments
     * @return object
     */
    public function addArgument(string $name, $arg)
    {
        $this->middleware[$this->count]['arguments'][$name] = $arg;
        return $this;
    }

    /**
     * Include methods
     * 
     * @param string $name method
     * @return object
     */
    public function addMethod(string $name)
    {
        unset($this->middleware[$this->count]['method'][0]);
        $this->middleware[$this->count]['method'][] = $name;
        return $this;
    }

    /**
     * Exclude methods
     * 
     * @param string $name method
     * @return object
     */
    public function removeMethod(string $name)
    {
        foreach ($this->getClassMethods() as $classMethod) {
            if ($classMethod != '__construct' && $classMethod != $name)  {
                $this->middleware[$this->count]['method'][] = $classMethod;
            }
        }
        return $this;
    }

    /**
     * Returns to class methods
     * 
     * @return array
     */
    protected function getClassMethods()
    {
        $classInstance = $this->dispatcher->getClassInstance();
        $methods = get_class_methods($classInstance);
        if ($methods[0] == '__construct') {
            unset($methods[0]);
            unset($this->middleware[$this->count]['method'][0]);
        }
        return $methods;
    }

    /**
     * Returns to all middlewares
     * 
     * @return array
     */
    public function getStack() : array
    {
        foreach ($this->middleware as $data) {
            if ((isset($data['method'][0]) && $data['method'][0] == '__construct') || in_array($this->dispatcher->getClassMethod(), $data['method'])) {
                $this->stack[] = $data;
            }
        }
        return $this->stack;
    }
}