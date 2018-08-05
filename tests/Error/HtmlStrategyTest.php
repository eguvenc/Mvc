<?php

use Obullo\Mvc\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
use Obullo\Mvc\Error\ErrorHandler;
use Obullo\Mvc\Error\HtmlStrategy;
use Zend\ServiceManager\ServiceManager;

class HtmlStrategyTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$container = new ServiceManager;
		$container->setFactory('loader', 'Tests\App\Services\LoaderFactory');
		$container->setFactory('translator', 'Tests\App\Services\TranslatorFactory');
		$container->setFactory('events', 'Tests\App\Services\EventManagerFactory');
		$container->setFactory('view', 'Tests\App\Services\ViewPlatesFactory');
		
		$this->errorHandler = new ErrorHandler;
		$this->errorHandler->setContainer($container);

		$strategy = new HtmlStrategy($container->get('view'));
		$strategy->setTranslator($container->get('translator'));

        $object = new Tests\App\Event\ErrorListener;
        $object->setContainer($container);
        $object->attach($container->get('events'));

		$this->errorHandler->setResponseStrategy($strategy);
	}

	public function testRenderErrorResponse()
	{
		$response = $this->errorHandler->renderErrorResponse(
			'Exception Error',
			'500',
			[],
			new ErrorException('Handle exception error test !')
		);

$htmlError = '<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Exception Error</title>
<style>
body{ color: #777575 !important; margin:0 !important; padding:20px !important; font-family:Arial,Verdana,sans-serif !important;font-weight:normal;  }
h1, h2, h3, h4 {
    margin: 0;
    padding: 0;
    font-weight: normal;
    line-height:48px;
}
</style>
</head>
<body>
<h1>Application Error</h1>
<section class="head">
	<table>
		<tr><td style="width:10%">Type</td><td>ErrorException</td></tr>
				    <tr><td>Message</td><td>Handle exception error test !</td></tr>';

		$this->assertEquals(500, $response->getStatusCode());
		$this->assertContains($htmlError, (string)$response->getBody());
	}

}