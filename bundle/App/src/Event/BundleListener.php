<?php

namespace App\Event;

use ErrorException;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\I18n\Translator\TranslatorAwareInterface;
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
        $this->listeners[] = $events->attach('App.bootstrap', [$this, 'onBootstrap']);
    }

    public function onBootstrap(EventInterface $e)
    {
        $container = $this->getContainer();
        $container->configure(
            [
                 'factories' => [
                     \App\Controller\DefaultController::class => \ServiceManager\LazyControllerFactory::class
                 ]
            ]
        );
        // $session = $container->get('session');
        // $session->start();
    }
}
