<?php

namespace Tests\App;

use Obullo\Mvc\Application;
use Obullo\Http\Stack\StackInterface as Stack;
use Psr\Container\ContainerInterface as Container;

class Module extends Application
{
	protected function configureConfig(Container $container)
	{
        $config = \Zend\Config\Factory::fromFiles(
            [
                ROOT.'/tests/var/config/app.yaml',
            ]
        );
	}

    protected function configureContainer(Container $container)
    {

    }

    protected function configureMiddleware(Stack $stack) : Stack
    {
        $Error  = 'Tests\App\Middleware\Error';
        $Router = 'Tests\App\Middleware\Router';
        $stack = $stack->withMiddleware(new $Error)
        	->withMiddleware(new $Router($this));
        return $stack;
    }
}