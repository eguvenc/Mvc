<?php

use Obullo\Mvc\Config\Loader\YamlLoader;
use Obullo\Mvc\Config\Cache\FileHandler;
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
use Obullo\Mvc\Exception;
use Obullo\Mvc\Controller;
use Zend\ServiceManager\ServiceManager;

class ControllerTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
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

        $fileHandler = new FileHandler('/tests/var/cache/config/');
        $loader = new YamlLoader($fileHandler);
        $routes = $loader->load('/tests/var/config/routes.yaml');
        $collection = $builder->build($routes);

        $router = new Router($collection);
        $router->match('/','example.com');

		$container = new ServiceManager;
		$this->controller = new Controller;

		$container->setService('router', $router);
		$container->setFactory('events', 'Tests\App\Services\EventManagerFactory');
		$container->setFactory('view', 'Tests\App\Services\ViewPlatesFactory');

		$this->controller->setContainer($container);
	}

	public function testGetContainer()
	{
		$this->assertInstanceOf('Zend\ServiceManager\ServiceManager', $this->controller->getContainer());
	}

	public function testGetterMethod()
	{
		$this->assertInstanceOf('Zend\EventManager\EventManager', $this->controller->events);
	}

	public function testSetterMethod()
	{
		$this->cache = new FileHandler('/tests/var/cache/config/');
		$this->assertInstanceOf('Obullo\Mvc\Config\Cache\FileHandler', $this->cache);
	}

	public function testRender() 
	{
		$response = $this->controller->render('test');
		$this->assertEquals('test', (string)$response->getBody());
	}

	public function testRedirect() 
	{
		$response = $this->controller->redirect('/');
		$headers = $response->getHeaders();

		$this->assertEquals('/', $headers['location'][0]);
		$this->assertEquals('302', $response->getStatusCode());
	}
}