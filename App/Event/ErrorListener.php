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
        $this->listeners[] = $events->attach('error.handler', [$this, 'onErrorHandler']);
    }

    public function onErrorHandler(EventInterface $e)
    {
        $error = $e->getParam('exception');

        switch ($error) {
            case ($error instanceof Throwable):
            case ($error instanceof RuntimeException):
                // error log
                break;
        }
    }
}
