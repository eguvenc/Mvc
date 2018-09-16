<?php

use Zend\ServiceManager\ServiceManager;
use Zend\Diactoros\ServerRequestFactory;
use Obullo\Http\{
    ControllerResolver,
    ArgumentResolver,
    Kernel
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

class SendResponseTest extends PHPUnit_Framework_TestCase
{
    public function testHelloWorld()
    {
        $container = new ServiceManager;
        $container->setFactory('events', 'Tests\App\Services\EventManagerFactory');
        $container->setFactory('session', 'Tests\App\Services\SessionFactory');
        $container->setFactory('loader', 'Tests\App\Services\LoaderFactory');
        $events = $container->get('events');
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
            $object->attach($events);
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
        
        $routes = $container->get('loader')
            ->load(ROOT, '/tests/var/config/routes_with_middleware.yaml');
        $collection = $builder->build($routes->toArray());

        $router = new Router($collection);
        $router->match('/','example.com');

        $controllerResolver = new ControllerResolver;
        $controllerResolver->setRouter($router);
        $controllerResolver->setContainer($container);
        $controllerResolver->dispatch();

        $queue = [
            new \Tests\App\Middleware\HttpMethod,
        ];
        $request = ServerRequestFactory::fromGlobals();
        $uri = $request->getUri()
            ->withPath('/')
            ->withHost('example.com');
        $request = $request->withUri($uri);
        $container->setService('request', $request);
        
        $stack = new Stack;
        $stack->setContainer($container);

        $kernel = new Kernel($events, $router, new ControllerResolver, $stack, new ArgumentResolver);
        $kernel->setContainer($container);
        $response = $kernel->handle($request);

        $this->assertEquals('Hello World !', (string)$response->getBody());
    }

    public function test404Error()
    {
        $container = new ServiceManager;
        $container->setFactory('error', 'Tests\App\Services\ErrorHandlerFactory');
        $container->setFactory('translator', 'Tests\App\Services\TranslatorFactory');
        $container->setFactory('loader', 'Tests\App\Services\LoaderFactory');
        $container->setFactory('view', 'Tests\App\Services\ViewPlatesFactory');
        $container->setFactory('events', 'Tests\App\Services\EventManagerFactory');
        $container->setFactory('session', 'Tests\App\Services\SessionFactory');
        $events = $container->get('events');
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
            $object->attach($events);
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
            
        $routes = $container->get('loader')
            ->load(ROOT, '/tests/var/config/routes_with_middleware.yaml');
        $collection = $builder->build($routes->toArray());

        $router = new Router($collection);
        $router->match('/abc123','example.com');

        $controllerResolver = new ControllerResolver;
        $controllerResolver->setRouter($router);
        $controllerResolver->setContainer($container);
        $controllerResolver->dispatch();

        $queue = [
            new \Tests\App\Middleware\HttpMethod,
        ];
        $request = ServerRequestFactory::fromGlobals();
        $uri = $request->getUri()
            ->withPath('/abc123')
            ->withHost('example.com');
        $request = $request->withUri($uri);

        $container->setService('request', $request);
        
        $stack = new Stack;
        $stack->setContainer($container);
        $kernel = new Kernel($events, $router, new ControllerResolver, $stack, new ArgumentResolver);
        $kernel->setContainer($container);
        $response = $kernel->handle($request);

        $body = '<body>
<h1>Not Found</h1>
<h3>The page you are looking for could not be found</h3>
</body>';

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertContains($body, (string)$response->getBody());
    }
}