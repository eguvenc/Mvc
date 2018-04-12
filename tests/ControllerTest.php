<?php

use Obullo\Mvc\Exception;
use Obullo\Mvc\Controller;
use League\Container\{
    Container,
    ReflectionContainer
};
use Obullo\Mvc\Config\Cache\FileHandler;

class ControllerTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->container = new Container;
		$this->container->delegate(
		    new ReflectionContainer
		);
		$this->controller = new Controller;
		$this->container->addServiceProvider('Tests\App\Services\Cookie');
		$this->controller->setContainer($this->container);
	}

	public function testGetContainer()
	{
		$this->assertInstanceOf('League\Container\Container', $this->controller->getContainer());
	}

	public function testGetterMethod()
	{
		$this->assertInstanceOf('Obullo\Mvc\Http\Cookie', $this->controller->cookie);
	}

	public function testSetterMethod()
	{
		$this->cache = new FileHandler('/tests/var/cache/config/');
		$this->assertInstanceOf('Obullo\Mvc\Config\Cache\FileHandler', $this->cache);
	}
}