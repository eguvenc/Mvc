<?php

namespace View\Event;

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

        // Auto wire controllers
        //
        $routes = $container->get('config')->routes;
        foreach ($routes as $route) {
            $factories['\\'.strstr($route->handler, '::', true)] = '\\'.$this->bundle->getName().'\Service\LazyControllerFactory';
        }
        $container->configure(
            [
                'factories' => $factories
            ]
        );
    }
}
