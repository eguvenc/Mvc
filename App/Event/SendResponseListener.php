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
use Psr\Http\Message\ResponseInterface as Response;

class SendResponseListener implements ListenerAggregateInterface,ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ListenerAggregateTrait;

    public function attach(EventManagerInterface $events, $priority = null)
    {
        $this->listeners[] = $events->attach('before.headers', [$this, 'onBeforeHeaders']);
        $this->listeners[] = $events->attach('before.emit',[$this, 'onBeforeEmit']);
        $this->listeners[] = $events->attach('after.emit', [$this, 'onAfterEmit']);
    }

    public function onBeforeHeaders(EventInterface $e) : Response
    {
        $response = $e->getParams();
        // $application = $e->getTarget();
        
        return $response;
    }

    public function onBeforeEmit(EventInterface $e) : Response
    {
        $response = $e->getParams();

        return $response;
    }

    public function onAfterEmit(EventInterface $e)
    {
        // $response = $e->getParams();
    }
}