<?php

use Obullo\Router\{
    RequestContext,
    RouteCollection,
    Router,
    Builder
};
use Obullo\Router\Types\{
    StrType,
    IntType,
    TranslationType
};
use Obullo\Mvc\Http\ServerRequestFactory;
use Obullo\Mvc\RouteDispatcher;
use Obullo\Mvc\Middleware;
use Zend\ServiceManager\ServiceManager;

class MiddlewareTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
        $container = new ServiceManager;
        $container->setFactory('loader', 'Tests\App\Services\LoaderFactory');
        
        $context = new RequestContext;
        $context->setPath('/');
        $context->setMethod('GET');
        $context->setHost('example.com');

        $collection = new RouteCollection(array(
            'types' => [
                new IntType('<int:id>'),
                new IntType('<int:page>'),
                new StrType('<str:name>'),
                new TranslationType('<locale:locale>'),
            ]
        ));
        $collection->setContext($context);
        $builder = new Builder($collection);
        
        $routes = $container->get('loader')
        	->load(ROOT, '/tests/var/config/routes_with_middleware.yaml');
        $collection = $builder->build($routes->toArray());

        $router = new Router($collection);
        $router->match('/','example.com');

        $dispatcher = new RouteDispatcher($router);
        $dispatcher->setContainer($container);
        $dispatcher->dispatch();
        $container->setService('router', $router);

        $this->middleware = new Middleware($dispatcher);
	}

	public function testAdd()
	{
		$this->middleware->add('Dummy');
		$stack = $this->middleware->getStack();

		$this->assertEquals('Tests\Middleware\Dummy', $stack[0]['class']);
	}

	public function testAddArgument()
	{
		$this->middleware->add('Dummy')
			->addArgument('name1', 'value1')
			->addArgument('name2', array('value2'));
		$stack = $this->middleware->getStack();

		$this->assertEquals('Tests\Middleware\Dummy', $stack[0]['class']);
		$this->assertEquals('value1', $stack[0]['arguments']['name1']);
		$this->assertEquals('value2', $stack[0]['arguments']['name2'][0]);
	}

	public function testDefaultMethod()
	{
		$this->middleware->add('Dummy');
		$stack = $this->middleware->getStack();

		$this->assertEquals('__construct', $stack[0]['method'][0]);
	}

	public function testAddMethod()
	{
		$this->middleware->add('Dummy')
			->addMethod('index')
			->addMethod('second_method')
			->addArgument('name', 'value');
		$stack = $this->middleware->getStack();

		$this->assertEquals('value', $stack[0]['arguments']['name']);
		$this->assertEquals('index', $stack[0]['method'][1]);
		$this->assertEquals('second_method', $stack[0]['method'][2]);
	}

	public function testRemoveMethod()
	{
		$this->middleware->add('Dummy')
			->removeMethod('index');
		$stack = $this->middleware->getStack();

		$this->assertNotContains('index', $stack[0]['method']);
	}
}