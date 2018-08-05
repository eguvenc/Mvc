<?php

use Obullo\Mvc\Exception;
use Zend\ServiceManager\ServiceManager;
use Zend\Config\Reader\Yaml as YamlReader;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Obullo\Mvc\Container\ContainerAwareTrait;
use Obullo\Mvc\Container\ContainerProxyTrait;

class ContainerProxyTest extends PHPUnit_Framework_TestCase
{
	use ContainerAwareTrait;
	use ContainerProxyTrait;

	public function setUp()
	{
		$this->container = new ServiceManager;
		$this->container->setService('yaml', new YamlReader([SymfonyYaml::class, 'parse']));
	}

	public function testContainer()
	{
		$this->setContainer($this->container);
		$container = $this->getContainer();
		$this->assertInstanceOf('Zend\ServiceManager\ServiceManager', $container);
	}

	public function testGetterMethod()
	{
		$this->assertInstanceOf('Zend\Config\Reader\Yaml', $this->yaml);
	}

	public function testSetterMethod()
	{
		$this->test = new YamlReader([SymfonyYaml::class, 'parse']);
		$this->assertInstanceOf('Zend\Config\Reader\Yaml', $this->test);
	}
}