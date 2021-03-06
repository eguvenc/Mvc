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
use Throwable;
use RuntimeException;

class ErrorListener implements ListenerAggregateInterface,ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ListenerAggregateTrait;

    public function attach(EventManagerInterface $events, $priority = null)
    {
        $this->listeners[] = $events->attach(strstr(__NAMESPACE__, '\\', true).'.error.handler', [$this, 'onErrorHandler']);
    }

    public function onErrorHandler(EventInterface $e)
    {
        $exception = $e->getParam('exception');

        switch ($exception) {
            case ($exception instanceof Throwable):
            case ($exception instanceof RuntimeException):
                // error log
                break;
        }
    }
}