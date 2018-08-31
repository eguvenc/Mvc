<?php

use Obullo\Mvc\RouteDispatcher;
use Obullo\Mvc\Http\ControllerResolver;
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

class ControllerResolverTest extends PHPUnit_Framework_TestCase
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

        $this->controllerResolver = new ControllerResolver;
        $this->controllerResolver->setRouter($router);
        $this->controllerResolver->setContainer($container);
        $this->controllerResolver->dispatch();
	}

	public function testGetFirstNamespace()
	{
		$this->assertEquals('Tests', $this->controllerResolver->getFirstNamespace());
	}

    public function testGetClassIsCallable()
    {
        $this->assertTrue($this->controllerResolver->getClassIsCallable());
    }

    public function testGetClassName()
    {
        $this->assertEquals('Tests\App\Controller\DefaultController', $this->controllerResolver->getClassName());
    }

    public function testGetClassMethod()
    {
        $this->assertEquals('index', $this->controllerResolver->getClassMethod());
    }

    public function testGetClassInstance()
    {
        $this->assertInstanceOf('Tests\App\Controller\DefaultController', $this->controllerResolver->getClassInstance());
    }

    public function testGetRouter()
    {
        $this->assertInstanceOf('Obullo\Router\Router', $this->controllerResolver->getRouter());
    }
}