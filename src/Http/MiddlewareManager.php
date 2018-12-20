<?php

namespace Obullo\Http;

use Obullo\Http\HttpControllerInterface as Controller;
use Obullo\Http\Exception\InvalidArgumentException;

/**
 * Middleware manager
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class MiddlewareManager implements MiddlewareManagerInterface
{
    protected $count = 0;
    protected $controller;
    protected $stack = array();
    protected $middleware = array();

    /**
     * Constructor
     * 
     * @param object Controller
     */
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
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
            'class' => '\Middleware\\'.$name,
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
    public function addArguments(array $args)
    {
        $this->middleware[$this->count]['arguments'] = $args;
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
        $methods = get_class_methods($this->controller);
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
            if (isset($data['method'][0]) && $data['method'][0] == '__construct') {
                $this->stack[] = $data;
            }
        }
        return $this->stack;
    }
}