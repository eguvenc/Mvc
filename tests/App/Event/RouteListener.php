<?php

namespace Tests\App\Event;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\ListenerAggregateInterface;

use Obullo\Mvc\Container\{
    ContainerAwareInterface,
    ContainerAwareTrait
};
use Obullo\Router\{
    RouteCollection,
    Builder
};
use Obullo\Router\Types\{
    StrType,
    IntType,
    TranslationType
};
class RouteListener implements ListenerAggregateInterface,ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ListenerAggregateTrait;

    public function attach(EventManagerInterface $events, $priority = null)
    {
        $this->listeners[] = $events->attach('route.builder', [$this, 'onBuilder']);
        $this->listeners[] = $events->attach('route.match', [$this, 'onMatch']);
    }

    public function onBuilder(EventInterface $e) : RouteCollection
    {   
        $context = $e->getParam('context');
        $types = [
            new IntType('<int:id>'),
            new IntType('<int:page>'),
            new StrType('<str:name>'),
            new TranslationType('<locale:locale>'),
        ];
        $collection = new RouteCollection(array(
            'types' => $types
        ));
        $collection->setContext($context);
        $builder = new Builder($collection);

        $routes = $this->getContainer()
            ->get('loader')
            ->load(ROOT, '/tests/var/config/routes.yaml')
            ->toArray();

        return $builder->build($routes);        
    }

    public function onMatch(EventInterface $e)
    {
        /*
        $route = $e->getParams();
        $route->getName();
        */
    }
}
