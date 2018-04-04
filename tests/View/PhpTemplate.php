<?php

use Obullo\Mvc\View;

use League\Container\{
    Container,
    ReflectionContainer
};
use League\Plates\Engine;
use Obullo\Mvc\View\PhpTemplate;
use League\Plates\Extension\Asset;

class PhpTemplateTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
        $engine = new Engine(ROOT.'/tests/var/view');
        $engine->setFileExtension('php');
        $engine->addFolder('templates', ROOT.'/test/var/templates');
        $engine->loadExtension(new Asset('/var/assets/', true));

		$container = new Container;
		$container->delegate(
		    new ReflectionContainer
		);
        $template = new PhpTemplate($engine);
        $template->setContainer($container);
        $template->registerFunctions();

        $this->template = $template;
	}
}