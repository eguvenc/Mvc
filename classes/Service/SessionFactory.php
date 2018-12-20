<?php

namespace Service;

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
        $application = $container->get('config')->application;

        $manager = new SessionManager();
        $manager->setStorage(new SessionArrayStorage());
        $manager->getValidatorChain()
            ->attach('session.validate', [new HttpUserAgent(), 'isValid']);

        $manager->setName($application->session->name);
        return $manager;
    }
}