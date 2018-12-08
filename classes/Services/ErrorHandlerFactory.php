<?php

namespace Services;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ErrorHandlerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        list($class, $method) = explode('::', $options['error_handler']);
        
    	$errorHandler = '\\'.$class;
    	$errorTemplate = $options['error_template'];
    	$notFoundTemplate = $options['404_template'];

		$error = new $errorHandler;
		$error->setHandler($method);
		$error->setView($container->get('view'));
		$error->set404Template($notFoundTemplate);
		$error->setErrorTemplate($errorTemplate);
		$error->setEvents($container->get('events'));
		$error->setTranslator($container->get('translator'));
		$error->setExceptionHandler();

		return $error;
    }
}