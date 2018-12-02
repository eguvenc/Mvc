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
        $url = $container->get('config')->database->url;
        $database = parse_url($url);
        
		$adapter = new Adapter (
            [
    		    'driver'   => $database['scheme'],
    		    'database' => ltrim($database['path'], '/'),
    		    'hostname' => $database['host'],
    		    'port' 	   => $database['port'],
    		    'username' => $database['user'],
    		    'password' => $database['pass'],
            ],
            null,
            null,
            new ZendSQLLogger($container->get('logger'))
        );
        return $adapter;
    }
}