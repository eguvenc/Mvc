<?php

use Obullo\Config\ConfigLoader;
use Obullo\Mvc\Dependency\Resolver;
use Zend\ServiceManager\ServiceManager;
use Zend\Config\Reader\ReaderInterface as Reader;

class ResolverTest extends PHPUnit_Framework_TestCase
{
	public function __construct()
	{
		$class = new class {
			public function test(ConfigLoader $loader, int $id, string $message = 'welcome')
			{

			}
		};
        $loader = new ConfigLoader(
            array(),
            ROOT.'/tests/var/cache/config/'
        );
		$container = new ServiceManager;
		$container->setService('loader', $loader);

		$reflection = new ReflectionClass($class);
		$this->resolver = new Resolver($reflection);
		$this->resolver->setContainer($container);
	}

	public function testResolve()
	{
		$args = $this->resolver->resolve('test');
		$this->assertInstanceOf('Obullo\Config\ConfigLoader', $args[0]);
	}

	public function testSetArguments()
	{
		$this->resolver->setArguments(['id' => 5, 'message' => 'welcome']);
		$args = $this->resolver->resolve('test');

		$this->assertInstanceOf('Obullo\Config\ConfigLoader', $args[0]);
		$this->assertEquals($args[1], 5);
		$this->assertEquals($args[2], 'welcome');
	}		
}