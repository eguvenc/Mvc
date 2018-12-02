<?php

namespace ServiceManager;

use Interop\Container\ContainerInterface;
use Obullo\Container\ContainerAwareInterface;
use Zend\ServiceManager\Initializer\InitializerInterface;

class SharedInitializer implements InitializerInterface
{
	/**
	* Initialize the given instance
	*
	* @param  ContainerInterface $container
	* @param  object             $instance
	* @return void
	*/
	public function __invoke(ContainerInterface $container, $instance)
	{
	    if ($instance instanceof ContainerAwareInterface) {
	        $instance->setContainer($container);
	    }
	    if ($instance instanceof TranslatorAwareInterface) {
	        $instance->setTranslator($container->get('translator'));
	    }
	}
}