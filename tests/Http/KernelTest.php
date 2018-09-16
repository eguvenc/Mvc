<?php

use Zend\ServiceManager\ServiceManager;
use Zend\Diactoros\ServerRequestFactory;
use Obullo\Http\{
    Kernel,
    ArgumentResolver,
    ControllerResolver
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
use Obullo\Container\ContainerAwareInterface;
use Obullo\Stack\Builder as Stack;

class KernelTest extends PHPUnit_Framework_TestCase
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
            'Tests\App\Event\ErrorListener',
            'Tests\App\Event\RouteListener',
            // 'Tests\App\Event\HttpMethodListener',
            'Tests\App\Event\SendResponseListener',
        ];
        foreach ($listeners as $listener) { // Create listeners
            $object = new $listener;
            if ($object instanceof ContainerAwareInterface) {
                $object->setContainer($container);
            }
            $object->attach($container->get('events'));
        }
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

        $controllerResolver = new ControllerResolver;
        $controllerResolver->setRouter($router);
        $controllerResolver->setContainer($container);
        $controllerResolver->dispatch();

        $request = ServerRequestFactory::fromGlobals();
        $uri = $request->getUri()
            ->withPath('/')
            ->withHost('example.com');
        $request = $request->withUri($uri);
        $container->setService('request', $request);

        $queue = [
            new \Tests\App\Middleware\HttpMethod,
        ];
        $stack = new Stack;
        $stack->setContainer($container);
        foreach ($queue as $value) {
            $stack = $stack->withMiddleware($value);
        }
        $kernel = new Kernel($container->get('events'), $router, new ControllerResolver, $stack, new ArgumentResolver);
        $kernel->setContainer($container);

        $response = $kernel->handle($request);

        $this->assertEquals('Hello World !', (string)$response->getBody());
        $this->assertInstanceOf('Tests\App\Middleware\HttpMethod', $queue[0]);
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

        $controllerResolver = new ControllerResolver;
        $controllerResolver->setRouter($router);
        $controllerResolver->setContainer($container);
        $controllerResolver->dispatch();

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

        $queue = [
            new \Tests\App\Middleware\HttpMethod,
        ];
        $stack = new Stack;
        $stack->setContainer($container);
        foreach ($queue as $value) {
            $stack = $stack->withMiddleware($value);
        }
        $kernel = new Kernel($container->get('events'), $router, new ControllerResolver, $stack, new ArgumentResolver);
        $kernel->setContainer($container);

        $response = $kernel->handle($request);

        $this->assertEquals('en', (string)$response->getBody());
    }
}