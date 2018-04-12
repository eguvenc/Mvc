<?php

use League\Container\{
    Container,
    ReflectionContainer
};
use Obullo\Mvc\HttpModule;
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

class HttpModuleTest extends PHPUnit_Framework_TestCase
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

		$container = new Container;
		$this->module = new HttpModule($router);
		$this->module->setContainer($container);
		$this->module->build();
	}

	public function testGetName()
	{
		$this->assertEquals('Tests', $this->module->getName());
	}

}