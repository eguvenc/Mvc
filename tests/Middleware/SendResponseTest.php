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
use Obullo\Mvc\Container\ContainerAwareInterface;
use Obullo\Mvc\Config\Loader\YamlLoader;
use Obullo\Mvc\Config\Cache\FileHandler;
use Obullo\Mvc\Http\ServerRequestFactory;
use Obullo\Stack\Builder as Stack;

class SendResponseTest extends PHPUnit_Framework_TestCase
{
    public function testHelloWorld()
    {
        $container = new ServiceManager;
        $container->setFactory('events', 'Tests\App\Services\EventManagerFactory');
        $container->setFactory('session', 'Tests\App\Services\SessionFactory');
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
        
        $fileHandler = new FileHandler('/tests/var/cache/config/');
        $loader = new YamlLoader($fileHandler);
        $routes = $loader->load('/tests/var/config/routes_with_middleware.yaml');
        $collection = $builder->build($routes);

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
        
        $stack = new Stack;
        $queue[] = new \Obullo\Mvc\Middleware\SendResponse($application);
        foreach ($queue as $value) {
            if ($value instanceof ContainerAwareInterface) {
                $value->setContainer($container);
            }
            $stack = $stack->withMiddleware($value);
        }
        $response = $stack->process($request);

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
        
        $fileHandler = new FileHandler('/tests/var/cache/config/');
        $loader = new YamlLoader($fileHandler);
        $routes = $loader->load('/tests/var/config/routes_with_middleware.yaml');
        $collection = $builder->build($routes);

        $router = new Router($collection);
        $router->match('/abc123','example.com');

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
            ->withPath('/abc123')
            ->withHost('example.com');
        $request = $request->withUri($uri);

        $container->setService('request', $request);
        
        $stack = new Stack;
        $queue[] = new \Obullo\Mvc\Middleware\SendResponse($application);
        foreach ($queue as $value) {
            if ($value instanceof ContainerAwareInterface) {
                $value->setContainer($container);
            }
            $stack = $stack->withMiddleware($value);
        }
        $response = $stack->process($request);

        $body = '<body>
<h1>Not Found</h1>
<h3>The page you are looking for could not be found</h3>
</body>';

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertContains($body, (string)$response->getBody());
    }
}