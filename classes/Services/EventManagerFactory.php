<?php

namespace Services;

use Zend\EventManager\EventManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class EventManagerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $container->setAlias('EventManager', $requestedName);

        $events = new EventManager;
        $listeners = [
            'Event\ErrorListener',
            'Event\HttpMethodListener',
            'Event\RouteListener',
            'Event\SendResponseListener',
        ];
        foreach ($listeners as $listener) { // Create listeners
            $object = new $listener;
            if ($object instanceof ContainerAwareInterface) {
                $object->setContainer($container);
            }
            $object->attach($events);   
        }
        return $events;
    }
}