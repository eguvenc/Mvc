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
use Obullo\Http\Controller;
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
        $container = new ServiceManager;
        $container->setFactory('loader', 'Tests\App\Services\LoaderFactory');
        $container->setFactory('events', 'Tests\App\Services\EventManagerFactory');
        $container->setFactory('view', 'Tests\App\Services\ViewPlatesFactory');

        $routes = $container->get('loader')
            ->load(ROOT, '/tests/var/config/routes.yaml');
        $collection = $builder->build($routes->toArray());

        $router = new Router($collection);
        $router->match('/','example.com');
        $container->setService('router', $router);
        
        $this->controller = new Controller;
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
        $this->std = new stdClass;
        $this->assertInstanceOf('stdClass', $this->std);
    }

    public function testRender() 
    {
        $response = $this->controller->render('test');
        $this->assertEquals('test', (string)$response->getBody());
    }

    public function testRenderHtml() 
    {
        $response = $this->controller->renderHtml('<b>test</b>');
        $this->assertEquals('<b>test</b>', (string)$response->getBody());
    }

    public function testRedirect() 
    {
        $response = $this->controller->redirect('/');
        $headers  = $response->getHeaders();

        $this->assertEquals('/', $headers['location'][0]);
        $this->assertEquals('302', $response->getStatusCode());
    }

    public function testJson()
    {
        $response = $this->controller->json(array('test' => '123'));
        $headers  = $response->getHeaders();

        $this->assertEquals($headers['content-type'][0], 'application/json');
        $this->assertEquals('{"test":"123"}', $response->getBody());
    }
}