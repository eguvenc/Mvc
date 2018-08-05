<?php

use Obullo\Mvc\RouteDispatcher;
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
use Zend\ServiceManager\ServiceManager;

class RouteDispatcherTest extends PHPUnit_Framework_TestCase
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
            ->load(ROOT, '/tests/var/config/routes.yaml');
        $collection = $builder->build($routes->toArray());

        $router = new Router($collection);
        $router->match('/','example.com');

		$this->dispatcher = new RouteDispatcher($router);
		$this->dispatcher->setContainer($container);
		$this->dispatcher->dispatch();
	}

	public function testGetFirstNamespace()
	{
		$this->assertEquals('Tests', $this->dispatcher->getFirstNamespace());
	}

    public function testGetClassIsCallable()
    {
        $this->assertTrue($this->dispatcher->getClassIsCallable());
    }

    public function testGetClassName()
    {
        $this->assertEquals('Tests\App\Controller\DefaultController', $this->dispatcher->getClassName());
    }

    public function testGetClassMethod()
    {
        $this->assertEquals('index', $this->dispatcher->getClassMethod());
    }

    public function testGetClassInstance()
    {
        $this->assertInstanceOf('Tests\App\Controller\DefaultController', $this->dispatcher->getClassInstance());
    }

    public function testGetRouter()
    {
        $this->assertInstanceOf('Obullo\Router\Router', $this->dispatcher->getRouter());
    }
}