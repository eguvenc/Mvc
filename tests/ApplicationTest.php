<?php

use Zend\ServiceManager\ServiceManager;
use Obullo\Mvc\{
    Application,
    RouteDispatcher
};
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
use Obullo\Stack\Builder as Stack;

class ApplicationTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $container = new ServiceManager;
        $container->setFactory('events', 'Tests\App\Services\EventManagerFactory');
        $container->setFactory('session', 'Tests\App\Services\SessionFactory');
        $container->setFactory('loader', 'Tests\App\Services\LoaderFactory');
        $this->container = $container;
    }

    public function testDispatch()
    {
        $container = $this->container;
        $listeners = [
            'Tests\App\Event\SessionListener',
            'Tests\App\Event\ErrorListener',
            'Tests\App\Event\RouteListener',
            // 'Tests\App\Event\HttpMethodListener',
            'Tests\App\Event\SendResponseListener',
        ];
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
        
        $routes = $this->container->get('loader')
            ->load(ROOT, '/tests/var/config/routes_with_middleware.yaml');
        $collection = $builder->build($routes->toArray());

        $router = new Router($collection);
        $router->match('/','example.com');

        $dispatcher = new RouteDispatcher($router);
        $dispatcher->setContainer($container);
        $dispatcher->dispatch();
        $container->setService('router', $router);

        $application = new Application($container, $listeners);
        $application->start($context, $dispatcher);

        $queue = [
            new \Obullo\Mvc\Middleware\Error,
            new \Obullo\Mvc\Middleware\HttpMethod,
        ];
        $request = ServerRequestFactory::fromGlobals();
        $uri = $request->getUri()
            ->withPath('/')
            ->withHost('example.com');
        $request = $request->withUri($uri);
        $container->setService('request', $request);

        $queue = $application->mergeQueue($queue);
        $response = $application->process($queue, $request);

        $this->assertEquals('Hello World !', (string)$response->getBody());
        $this->assertInstanceOf('Obullo\Mvc\Middleware\Error', $queue[0]);
        $this->assertInstanceOf('Obullo\Mvc\Middleware\HttpMethod', $queue[1]);
    }

    public function testProcessWithLocalizedRoute()
    {
        $container = $this->container;

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
        
        $routes = $this->container->get('loader')
            ->load(ROOT, '/tests/var/config/routes.yaml');
        $collection = $builder->build($routes->toArray());

        $router = new Router($collection);
        $router->match('/en/test','example.com');

        $dispatcher = new RouteDispatcher($router);
        $dispatcher->setContainer($container);
        $dispatcher->dispatch();
        $container->setService('router', $router);

        $application = new Application($container, []);
        $application->start($context, $dispatcher);

        $queue = [
            new \Obullo\Mvc\Middleware\Error,
            new \Obullo\Mvc\Middleware\HttpMethod,
        ];
        $_SERVER['REQUEST_URI'] = '/en/test';
        $request = ServerRequestFactory::fromGlobals(
            $_SERVER,
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES
        );
        $route  = $router->getMatchedRoute();
        $locale = $route->getArgument('locale');
        $route->removeArgument('locale');
        $request = $request->withAttribute('locale', $locale);

        $container->setService('request', $request);
        $response = $application->process($queue, $request);

        $this->assertEquals('en', (string)$response->getBody());
    }
}