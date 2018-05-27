<?php

use Obullo\Mvc\Dependency\Resolver;
use Obullo\Mvc\Config\Cache\FileHandler;
use Zend\ServiceManager\ServiceManager;
use Zend\Config\Reader\ReaderInterface as Reader;

class ResolverTest extends PHPUnit_Framework_TestCase
{
	public function __construct()
	{
		$class = new class {
			public function test(Reader $reader, int $id, string $message = 'welcome')
			{

			}
		};
		$container = new ServiceManager;
		$container->setService('reader', new Obullo\Mvc\Config\Reader\YamlReader(new FileHandler('/tests/var/cache/config/')));
		$reflection = new ReflectionClass($class);
		$this->resolver = new Resolver($reflection);
		$this->resolver->setContainer($container);
	}

	public function testResolve()
	{
		$args = $this->resolver->resolve('test');
		$this->assertInstanceOf('Obullo\Mvc\Config\Reader\YamlReader', $args[0]);
	}

	public function testSetArguments()
	{
		$this->resolver->setArguments(['id' => 5, 'message' => 'welcome']);
		$args = $this->resolver->resolve('test');

		$this->assertInstanceOf('Obullo\Mvc\Config\Reader\YamlReader', $args[0]);
		$this->assertEquals($args[1], 5);
		$this->assertEquals($args[2], 'welcome');
	}		
}