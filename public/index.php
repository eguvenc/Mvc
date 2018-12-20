<?php

require '../vendor/autoload.php';

define('ROOT', dirname(__DIR__));

use Obullo\Http\{
    ControllerResolver,
    Kernel
}; 
use ServiceManager\SharedInitializer;
use Zend\ServiceManager\ServiceManager;
use Dotenv\Dotenv;

// -------------------------------------------------------------------
// Convert Errors to Exceptions
// -------------------------------------------------------------------
//
set_error_handler(function($errno, $errstr, $errfile, $errline) {      
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// -------------------------------------------------------------------
// Environment Manager
// -------------------------------------------------------------------
//
if (false == isset($_SERVER['APP_ENV'])) {
    (new Dotenv(ROOT))->load();
}
$env = $_SERVER['APP_ENV'] ?? 'dev';

error_reporting(0);
if ('prod' !== $env) {
    ini_set('display_errors', 1);  
    error_reporting(E_ALL);
}
// -------------------------------------------------------------------
// Service Manager
// -------------------------------------------------------------------
//
$container = new ServiceManager;
$container->setFactory('request', 'Service\RequestFactory');
$container->setFactory('config', 'Service\ConfigFactory');
$container->setFactory('router', 'Service\RouterFactory');
$container->setFactory('translator', 'Service\TranslatorFactory');
$container->setFactory('events', 'Service\EventManagerFactory');
$container->setFactory('session', 'Service\SessionFactory');
$container->setFactory('adapter', 'Service\ZendDbFactory');
$container->setFactory('logger', 'Service\LoggerFactory');
$container->setFactory('flash', 'Service\FlashMessengerFactory');
$container->setFactory('escaper', 'Service\EscaperFactory');
$container->setFactory('view', 'Service\ViewFactory');

// -------------------------------------------------------------------
// Service Manager Global Configuration
// -------------------------------------------------------------------
//
$container->addInitializer(new SharedInitializer);
$container->addAbstractFactory(new Middleware\LazyMiddlewareFactory);

// -------------------------------------------------------------------
// Initialize Packages
// -------------------------------------------------------------------
//
$request = $container->get('request');

// -------------------------------------------------------------------
// Http Kernel
// -------------------------------------------------------------------
//
$kernel = new Kernel($container->get('events'), $container->get('router'), new ControllerResolver($container));

// -------------------------------------------------------------------
// Handle Process
// -------------------------------------------------------------------
//
// Execute the kernel, which turns the request into a response 
// by dispatching route, calling a controller, and returning the response
// 
$response = $kernel->handleRequest($request);

// -------------------------------------------------------------------
// Send Response
// -------------------------------------------------------------------
//
// Send the headers and echo the content
// 
$kernel->send($response);