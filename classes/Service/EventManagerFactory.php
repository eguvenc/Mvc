<?php

namespace Service;

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
            'Event\HttpMethodListener',
            'Event\RouteListener',
            'Event\SendResponseListener',
        ];
        foreach ($listeners as $listener) { // Create listeners
            $object = new $listener;
            $object->setContainer($container);
            $object->attach($events);   
        }
        return $events;
    }
}