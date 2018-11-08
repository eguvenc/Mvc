<?php

namespace App\Event;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\ListenerAggregateInterface;
use Obullo\Container\{
    ContainerAwareInterface,
    ContainerAwareTrait
};
class HttpMethodListener implements ListenerAggregateInterface,ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ListenerAggregateTrait;

    public function attach(EventManagerInterface $events, $priority = null)
    {
        $this->listeners[] = $events->attach('allowed.method', [$this, 'onAllowedMethod']);
        $this->listeners[] = $events->attach('disallowed.method', [$this, 'onNotAllowedMethod']);
    }

    public function onNotAllowedMethod(EventInterface $e) : string
    {    
        $methods = $e->getParam('methods');
        $message = sprintf(
            'Only Http %s Methods Allowed',
            implode(', ', $methods)
        );
        return $message;
    }

    public function onAllowedMethod(EventInterface $e)
    {
        // $methods = $e->getParam('methods');
    }
}