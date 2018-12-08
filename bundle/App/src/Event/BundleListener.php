<?php

namespace App\Event;

use App\Error\ErrorHandler;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Obullo\Http\Bundle;
use Obullo\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
class BundleListener implements ListenerAggregateInterface,ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ListenerAggregateTrait;

    public function attach(EventManagerInterface $events, $priority = null)
    {
        $this->bundle = new Bundle(__NAMESPACE__);
        $this->listeners[] = $events->attach($this->bundle->getName().'.bootstrap', [$this, 'onBootstrap']);
    }

    public function onBootstrap(EventInterface $e)
    {
        $container = $this->getContainer();

        $errorHandler = new ErrorHandler;
        $errorHandler->setBundle($this->bundle);
        $errorHandler->setView($container->get('view'));
        $errorHandler->set404Template('View::_404.phtml');
        $errorHandler->setErrorTemplate('View::_Error.phtml');
        $errorHandler->setEvents($container->get('events'));
        $errorHandler->setTranslator($container->get('translator'));
        $errorHandler->setExceptionHandler();

        // Auto wire all module controllers
        // 
        $routes = $container->get('config')->routes;
        foreach ($routes as $route) {
            $factories[strstr($route->handler, '::', true)] = '\\'.$this->bundle->getName().'\Service\LazyControllerFactory';
        }
        $container->configure(
            [
                'factories' => $factories
            ]
        );

        // Start the sessions if we need
        // 
        // $session = $container->get('session');
        // $session->start();
    }
}
