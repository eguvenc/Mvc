<?php

namespace Service;

use Obullo\Error\ErrorTemplate;
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
        $events = $container->get('events');

        $bundleName = strstr($requestedName, '\\', true);
        $errorListener = '\\'.$bundleName.'\Event\ErrorListener';

        $listener = new $errorListener;  // create error listeners
        $listener->setContainer($container);
        $listener->attach($events);

		$handler = new $requestedName(
            $bundleName,
            $events
        );
        $errorTemplate = new ErrorTemplate;
        $errorTemplate->setTranslator($container->get('translator'));
        $errorTemplate->setView($container->get('view'));

        $handler->setErrorTemplate($errorTemplate);
        $handler->setErrorTemplateName('View::_Error.phtml');
        $handler->setExceptionHandler($requestedName.'::handle');

		return $handler;
    }
}