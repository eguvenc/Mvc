<?php

namespace Service;

use RuntimeException;
use MongoDB\Driver\Manager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class MongoFactory implements FactoryInterface
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
        if (! extension_loaded('MongoDB')) {
            throw new RuntimeException(
                'The MongoDB extension has not been installed or enabled.'
            );
        }
        $mongo = $container->get('config')->mongo;

        return new Manager($mongo->url);
    }
}