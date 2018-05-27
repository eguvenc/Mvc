<?php

use Obullo\Mvc\Exception;
use Zend\ServiceManager\ServiceManager;
use Obullo\Mvc\Config\Cache\FileHandler;
use Obullo\Mvc\Container\ContainerAwareTrait;
use Obullo\Mvc\Container\ContainerProxyTrait;

class ContainerProxyTest extends PHPUnit_Framework_TestCase
{
	use ContainerAwareTrait;
	use ContainerProxyTrait;

	public function setUp()
	{
		$this->container = new ServiceManager;
		$this->container->setService('cache', new FileHandler('/tests/var/cache/config/'));
	}

	public function testContainer()
	{
		$this->setContainer($this->container);
		$container = $this->getContainer();
		$this->assertInstanceOf('Zend\ServiceManager\ServiceManager', $container);
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