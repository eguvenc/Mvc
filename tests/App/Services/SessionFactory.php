<?php

namespace Tests\App\Services;

use Zend\Session\SessionManager;
use Zend\Session\Validator\HttpUserAgent;
use Zend\Session\Storage\SessionArrayStorage;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class SessionFactory implements FactoryInterface
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
        $manager = null;
        $manager = new SessionManager();
        $manager->setStorage(new SessionArrayStorage());
        // $manager->setName('test');  // No need to set name in test environment may occurs exception.
        // $manager->start();

        return $manager;
    }
}