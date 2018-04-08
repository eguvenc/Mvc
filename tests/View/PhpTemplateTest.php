<?php

use Obullo\Mvc\View;
use League\Container\{
    Container,
    ReflectionContainer
};
use League\Plates\Engine;
use Obullo\Mvc\View\PhpTemplate;
use League\Plates\Extension\Asset;
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
use Obullo\Mvc\Config\Cache\FileHandler;
use Obullo\Mvc\Config\Loader\YamlLoader;

class PhpTemplateTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
        $engine = new Engine(ROOT.'/tests/var/view');
        $engine->setFileExtension('php');
        $engine->addFolder('templates', ROOT.'/tests/var/templates');
        $engine->loadExtension(new Asset('/var/assets/', true));

		$container = new Container;
		$container->delegate(
		    new ReflectionContainer
		);
        $fileHandler = new FileHandler('/tests/var/cache/config/');
        $loader = new YamlLoader($fileHandler);
        $container->share('loader', $loader);

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

        $routes = $loader->load('/tests/var/config/routes.yaml');
        $collection = $builder->build($routes);

        $router = new Router($collection);
        $container->share('router',$router);

        $template = new PhpTemplate($engine);
        $template->setContainer($container);
        $template->registerFunctions();

        $this->template = $template;
	}

    public function testRender()
    {
        $response = $this->template->render('test', ['var' => 'variable']);
        $this->assertInstanceOf('Zend\Diactoros\Response', $response);
        $this->assertEquals('Test variable:variable', trim((string)$response->getBody()));
    }

    public function testRenderView()
    {
        $view = $this->template->renderView('test', ['var' => 'variable']);
        $this->assertEquals('Test variable:variable', $view);
    }

    public function testGetEngine()
    {
        $this->assertInstanceOf('League\Plates\Engine', $this->template->getEngine());
    }
}