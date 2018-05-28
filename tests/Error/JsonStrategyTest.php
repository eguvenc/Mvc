<?php

use Obullo\Mvc\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
use Obullo\Mvc\Error\ErrorHandler;
use Obullo\Mvc\Error\JsonStrategy;
use Zend\ServiceManager\ServiceManager;
use Obullo\Mvc\Config\Cache\FileHandler;

class JsonStrategyTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$container = new ServiceManager;
		$container->setFactory('loader', 'Tests\App\Services\LoaderFactory');
		$container->setFactory('translator', 'Tests\App\Services\TranslatorFactory');
		$container->setFactory('events', 'Tests\App\Services\EventManagerFactory');
		$container->setFactory('view', 'Tests\App\Services\ViewPlatesFactory');

        $object = new Tests\App\Event\ErrorListener;
        $object->setContainer($container);
        $object->attach($container->get('events'));

		$this->container = $container;
		$this->errorHandler = new ErrorHandler;
		$this->errorHandler->setContainer($container);
	}

	public function testRenderErrorResponse()
	{
		$strategy = new JsonStrategy;
		$strategy->enableExceptions();
		// $strategy->enableExceptionTrace();
		$strategy->setTranslator($this->container->get('translator'));

		$this->errorHandler->setResponseStrategy($strategy);

		$response = $this->errorHandler->renderErrorResponse(
			'Exception Error',
			'500',
			[],
			new ErrorException('Handle exception error test !')
		);
		$output = '{"error":{"message":"Handle exception error test !"},"exception":{"type":"ErrorException","code":0,"message":"Handle exception error test !"';

		$this->assertEquals(500, $response->getStatusCode());
		$this->assertContains($output, (string)$response->getBody());
	}
}