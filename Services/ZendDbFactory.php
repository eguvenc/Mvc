<?php

namespace Services;

use Zend\Db\Adapter\Adapter;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Obullo\Logger\ZendSQLLogger;

class ZendDbFactory implements FactoryInterface
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
        $database = $container->get('loader')
            ->load(ROOT, '/config/%s/database.yaml', true)
            ->database;
            
        $config = parse_url($database->url);

		$adapter = new Adapter (
            [
    		    'driver'   => $config['scheme'],
    		    'database' => ltrim($config['path'], '/'),
    		    'hostname' => $config['host'],
    		    'port' 	   => $config['port'],
    		    'username' => $config['user'],
    		    'password' => $config['pass'],
            ],
            null,
            null,
            new ZendSQLLogger($container->get('logger'))
        );
        return $adapter;
    }
}