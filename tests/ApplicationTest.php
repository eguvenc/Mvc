<?php

use League\Container\{
    Container,
    ReflectionContainer
};
use Obullo\Mvc\Application;
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
use Tests\App\Config;
use Obullo\Mvc\Module;
use Obullo\Mvc\Config\Loader\YamlLoader;
use Obullo\Mvc\Config\Cache\FileHandler;
use Zend\Diactoros\ServerRequestFactory;
use Obullo\Stack\Builder as Stack;

class ApplicationTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
        $this->app = new Config('dev');
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
        $this->collection = $builder->build($routes);

        $this->container = new Container;
        $this->container->delegate(
            new ReflectionContainer
        );
	}

    public function testGetEnv()
    {
        $this->assertEquals('dev', $this->app->getEnv());
    }

    public function testModule()
    {
        $router = new Router($this->collection);
        $router->match('/','example.com');
        $module = new Module($router);
        $module->setContainer($this->container);
        $module->build();

        $this->app->setModule($module);
        $this->assertInstanceOf('Obullo\Mvc\Module', $this->app->getModule());
        $this->assertEquals('Tests', $module->getName());
    }

    public function testBuild()
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
        $routes = $loader->load('/tests/var/config/routes_with_middleware.yaml');
        $this->collection = $builder->build($routes);

        $router = new Router($this->collection);
        $router->match('/','example.com');
        $module = new Module($router);
        $module->setContainer($this->container);
        $module->build();

        $request = ServerRequestFactory::fromGlobals();
        $this->app->setModule($module);
        $this->app->setStack(new Stack);
        $middlewares = $this->app->build();

        $this->assertInstanceOf('Tests\App\Middleware\Test', $middlewares[0]);
        $this->assertInstanceOf('Tests\App\Middleware\Dummy', $middlewares[1]);
    }

    public function testProcess()
    {
        $router = new Router($this->collection);
        $router->match('/','example.com');
        $module = new Module($router);
        $module->setContainer($this->container);
        $module->build();

        $request = ServerRequestFactory::fromGlobals();
        $this->app->setModule($module);
        $this->app->setStack(new Stack);
        $response = $this->app->process($request);

        $this->assertEquals('Hello World !', (string)$response->getBody());
    }

    public function testProcessWithLocalizedRoute()
    {
        $context = new RequestContext;
        $context->setPath('/en/test');
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
        $router->match('/en/test','example.com');

        $module = new Module($router);
        $module->setContainer($this->container);
        $module->build();

        $_SERVER['REQUEST_URI'] = '/en/test';
        $request = ServerRequestFactory::fromGlobals(
            $_SERVER,
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES
        );
        $this->app->setModule($module);
        $this->app->setStack(new Stack);
        $response = $this->app->process($request);
        $this->assertEquals('en', (string)$response->getBody());
    }
}