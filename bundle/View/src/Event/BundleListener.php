<?php

namespace View\Event;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Obullo\Http\BundleAwareTrait;
use Obullo\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
class BundleListener implements ListenerAggregateInterface,ContainerAwareInterface
{
    use BundleAwareTrait;
    use ContainerAwareTrait;
    use ListenerAggregateTrait;

    public function attach(EventManagerInterface $events, $priority = null)
    {
        $this->bundle = $this->getBundle();
        $this->listeners[] = $events->attach($this->bundle->getName().'.bootstrap', [$this, 'onBootstrap']);
    }

    public function onBootstrap(EventInterface $e)
    {
        $container = $this->getContainer();

        // Configure container to auto wire controllers
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
    }
}
