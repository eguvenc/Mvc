<?php

namespace Event;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\ListenerAggregateInterface;
use Obullo\Container\{
    ContainerAwareInterface,
    ContainerAwareTrait
};
class RouteListener implements ListenerAggregateInterface,ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ListenerAggregateTrait;

    public function attach(EventManagerInterface $events, $priority = null)
    {
        $this->listeners[] = $events->attach('route.match', [$this, 'onMatch']);
    }

    public function onMatch(EventInterface $e)
    {
        // $route = $e->getParam('route');
        // 
        // $route->getName();
        // 
    }
}
