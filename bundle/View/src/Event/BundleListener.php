<?php

namespace View\Event;

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
        $this->listeners[] = $events->attach('View.bootstrap', [$this, 'onBootstrap']);
    }

    public function onBootstrap(EventInterface $e)
    {
        $container = $this->getContainer();
        $container->configure(
            [
                 'factories' => [
                     \View\Controller\ViewController::class => \ServiceManager\LazyControllerFactory::class
                 ]
            ]
        );
    }
}
