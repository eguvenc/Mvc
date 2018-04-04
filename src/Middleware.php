<?php

namespace Obullo\Mvc;

use Obullo\Mvc\Module;

/**
 * Middleware
 *
 * @copyright 2018 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Middleware
{
	protected $count = 0;
	protected $module;
	protected $stack = array();
	protected $middleware = array();

	/**
	 * Constructor
	 * 
	 * @param Module $module module
	 */
	public function __construct(Module $module)
	{
		$this->module = $module;
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
			'class' => $this->module->getName().'\Middleware\\'.$name,
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
	public function withArgument(string $name, $arg)
	{
		$this->middleware[$this->count]['arguments'][$name] = $arg;
		return $this;
	}

	/**
	 * Include methods
	 * 
	 * @param string|array $names method
	 * @return object
	 */
	public function includeMethods($names)
	{
		unset($this->middleware[$this->count]['method'][0]);
		$this->middleware[$this->count]['method'] = (array)$names;
		return $this;
	}

	/**
	 * Exclude methods
	 * 
	 * @param string|array $names method
	 * @return object
	 */
	public function excludeMethods($names)
	{
		foreach ($this->getClassMethods() as $classMethod) {
			if ($classMethod != '__construct' && ! in_array($classMethod, (array)$names))  {
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
		$methods = $this->module->getClassMethods();
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
			if ((isset($data['method'][0]) && $data['method'][0] == '__construct') || in_array($this->module->getClassMethod(), $data['method'])) {
				$this->stack[] = $data;
			}
		}
		return $this->stack;
	}
}