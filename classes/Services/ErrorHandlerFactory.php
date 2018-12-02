<?php

namespace Services;

use Obullo\Error\{
    ErrorHandler,
    HtmlStrategy
};
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
        $strategy = new HtmlStrategy($container->get('html'));
        $strategy->setTranslator($container->get('translator'));

		$error = new ErrorHandler;
		$error->setResponseStrategy($strategy);
        return $error;
    }
}