<?php

namespace Obullo\Http;

use Obullo\Exception\MiddlewareArgumentException;

/**
 * Middleware manager
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Middleware
{
    protected $count = 0;
    protected $resolver;
    protected $stack = array();
    protected $middleware = array();

    /**
     * Constructor
     * 
     * @param ControllerResolver $resolver resolver
     */
    public function __construct(ControllerResolver $resolver)
    {
        $this->resolver = $resolver;
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
            'class' => $this->resolver->getFirstNamespace().'\Middleware\\'.$name,
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
        if (false == $this->isAssocativeArray($args)) {
            throw new MiddlewareArgumentException('Middleware add argument method parameter must be associative array.');
        }
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
        $classInstance = $this->resolver->getClassInstance();
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
            if ((isset($data['method'][0]) && $data['method'][0] == '__construct') || in_array($this->resolver->getClassMethod(), $data['method'])) {
                $this->stack[] = $data;
            }
        }
        return $this->stack;
    }

    /**
     * Simple test for an associative array
     *
     * @link http://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
     * @param array $array
     * @return bool
     */
    private function isAssocativeArray(array $array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
}