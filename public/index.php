<?php

require '../vendor/autoload.php';

define('ROOT', dirname(__DIR__));

use Obullo\Http\{
    ControllerResolver,
    Kernel
}; 
use Obullo\Stack\Builder as Stack;
use ServiceManager\SharedInitializer;
use Zend\ServiceManager\ServiceManager;
use Dotenv\Dotenv;

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
$container = new ServiceManager();
$container->setFactory('request', 'Services\RequestFactory');
$container->setFactory('config', 'Services\ConfigFactory');
$container->setFactory('router', 'Services\RouterFactory');
$container->setFactory('translator', 'Services\TranslatorFactory');
$container->setFactory('events', 'Services\EventManagerFactory');
$container->setFactory('session', 'Services\SessionFactory');
$container->setFactory('adapter', 'Services\ZendDbFactory');
$container->setFactory('logger', 'Services\LoggerFactory');
$container->setFactory('flash', 'Services\FlashMessengerFactory');
$container->setFactory('escaper', 'Services\EscaperFactory');
$container->setFactory('html', 'Services\ViewFactory');
$container->setFactory('error', 'Services\ErrorHandlerFactory');

// -------------------------------------------------------------------
// Service Manager Initializers
// -------------------------------------------------------------------
//
$container->addInitializer(new SharedInitializer);

// -------------------------------------------------------------------
// Initialize Packages
// -------------------------------------------------------------------
//
$request = $container->get('request');

/**
* Bu class olmalı. (SharedErrorHandler)
*/
$errorHandler = function ($bundleName) {
    set_exception_handler(array($container->get($bundleName.'\ErrorHandler.php'), 'handle'));
    set_error_handler(function($errno, $errstr, $errfile, $errline) {      
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    });
};

// -------------------------------------------------------------------
// Stack Queue
// -------------------------------------------------------------------
//
$queue = [
    new Middleware\HttpMethod
];
// -------------------------------------------------------------------
// Http Kernel
// -------------------------------------------------------------------
//
$kernel = new Kernel($container->get('events'), $container->get('router'), new ControllerResolver($container), $queue);
$kernel->setErrorHandler($errorCallable);

// -------------------------------------------------------------------
// Handle Process
// -------------------------------------------------------------------
//
// Execute the kernel, which turns the request into a response 
// by dispatching route, calling a controller, and returning the response
// 
$response = $kernel->handleRequest($request);

// -------------------------------------------------------------------
// Stack Builder
// -------------------------------------------------------------------
//
$stack = new Stack($container);
foreach ($kernel->getQueue() as $value) {
    $stack = $stack->withMiddleware($value);
}
$stack = $stack->withMiddleware(new Middleware\SendResponse($response));
$response = $stack->process($request);

// -------------------------------------------------------------------
// Send Response
// -------------------------------------------------------------------
//
// Send the headers and echo the content
// 
$kernel->send($response);
