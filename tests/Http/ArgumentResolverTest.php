<?php

use Obullo\Config\ConfigLoader;
use Obullo\Mvc\Http\ArgumentResolver;
use Zend\ServiceManager\ServiceManager;
use Zend\Config\Reader\ReaderInterface as Reader;

class ArgumentResolverTest extends PHPUnit_Framework_TestCase
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
		$this->argumentResolver = new ArgumentResolver($reflection);
		$this->argumentResolver->setReflectionClass($reflection);
		$this->argumentResolver->setContainer($container);
	}

	public function testResolve()
	{
		$args = $this->argumentResolver->resolve('test');
		$this->assertInstanceOf('Obullo\Config\ConfigLoader', $args[0]);
	}

	public function testSetArguments()
	{
		$this->argumentResolver->setArguments(['id' => 5, 'message' => 'welcome']);
		$args = $this->argumentResolver->resolve('test');

		$this->assertInstanceOf('Obullo\Config\ConfigLoader', $args[0]);
		$this->assertEquals($args[1], 5);
		$this->assertEquals($args[2], 'welcome');
	}		
}