<?php

use Obullo\Exception;
use Zend\ServiceManager\ServiceManager;
use Obullo\Container\{
	ContainerAwareTrait,
	ContainerAwareInterface
};
class ContainerAwareTest extends PHPUnit_Framework_TestCase implements ContainerAwareInterface 
{
	use ContainerAwareTrait;

	public function setUp()
	{
		$this->container = new ServiceManager;
	}

	public function testContainer()
	{
		$this->setContainer($this->container);
		$container = $this->getContainer();
		$this->assertInstanceOf('Zend\ServiceManager\ServiceManager', $container);
	}
}