<?php

use Obullo\Mvc\Exception;

use League\Container\{
    Container,
    ReflectionContainer
};
use Obullo\Mvc\Container\ContainerAwareTrait;
use Obullo\Mvc\Container\ContainerAwareInterface;

class ContainerAwareTest extends PHPUnit_Framework_TestCase implements ContainerAwareInterface 
{
	use ContainerAwareTrait;

	public function setUp()
	{
		$this->container = new Container;
		$this->container->delegate(
		    new ReflectionContainer
		);
	}

	public function testContainer()
	{
		$this->setContainer($this->container);
		$container = $this->getContainer();
		$this->assertInstanceOf('League\Container\Container', $container);
	}
}