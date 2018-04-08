<?php

use Obullo\Mvc\Exception;

use League\Container\{
    Container,
    ReflectionContainer
};
use Obullo\Mvc\Config\Cache\FileHandler;
use Obullo\Mvc\Container\ContainerProxyTrait;

class ContainerProxyTest extends PHPUnit_Framework_TestCase
{
	use ContainerProxyTrait;

	public function setUp()
	{
		$this->container = new Container;
		$this->container->delegate(
		    new ReflectionContainer
		);
		$this->container->share('cache', new FileHandler('/tests/var/cache/config/'));
	}

	public function testContainer()
	{
		$this->setContainer($this->container);
		$container = $this->getContainer();
		$this->assertInstanceOf('League\Container\Container', $container);
	}

	public function testGetterMethod()
	{
		$this->assertInstanceOf('Obullo\Mvc\Config\Cache\FileHandler', $this->cache);
	}

	public function testSetterMethod()
	{
		$this->test = new FileHandler('/tests/var/cache/config/');
		$this->assertInstanceOf('Obullo\Mvc\Config\Cache\FileHandler', $this->cache);
	}
}