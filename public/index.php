<?php

require '../vendor/autoload.php';

define('ROOT', dirname(__DIR__));
define('APP', 'App');

use Obullo\Http\{
    ControllerResolver,
    ArgumentResolver,
    Kernel
};
use Obullo\Stack\Builder as Stack;
use Obullo\Container\ContainerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\EventManager\Event;
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
$container = new ServiceManager;
$container->setFactory('request', 'Services\RequestFactory');
$container->setFactory('loader', 'Services\LoaderFactory');
$container->setFactory('router', 'Services\RouterFactory');
$container->setFactory('translator', 'Services\TranslatorFactory');
$container->setFactory('events', 'Services\EventManagerFactory');
$container->setFactory('session', 'Services\SessionFactory');
$container->setFactory('adapter', 'Services\ZendDbFactory');
$container->setFactory('view', 'Services\ViewPlatesFactory');
$container->setFactory('logger', 'Services\LoggerFactory');
$container->setFactory('flash', 'Services\FlashMessengerFactory');
$container->setFactory('error', 'Services\ErrorHandlerFactory');
$container->setFactory('escaper', 'Services\EscaperFactory');

// -------------------------------------------------------------------
// Initialize
// -------------------------------------------------------------------
// 
$events  = $container->get('events');
$request = $container->get('request');
$session = $container->get('session');

// -------------------------------------------------------------------
// Sessions
// -------------------------------------------------------------------
// 
$session->start();

// -------------------------------------------------------------------
// Exception Handler
// -------------------------------------------------------------------
//
set_exception_handler(array($container->get('error'), 'handle'));

// -------------------------------------------------------------------
// Error Handler
// -------------------------------------------------------------------
//
set_error_handler(function($errno, $errstr, $errfile, $errline) {      
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});
// -------------------------------------------------------------------
// Event Listeners
// -------------------------------------------------------------------
//
$listeners = [
    'App\Event\ErrorListener',
    'App\Event\RouteListener',
    'App\Event\HttpMethodListener',
    'App\Event\SendResponseListener',
];
foreach ($listeners as $listener) { // Create listeners
    $object = new $listener;
    if ($object instanceof ContainerAwareInterface) {
        $object->setContainer($container);
    }
    $object->attach($events);
}
// -------------------------------------------------------------------
// Stack Queue
// -------------------------------------------------------------------
//
$queue = [
    new App\Middleware\HttpMethod
];
$stack = new Stack;
$stack->setContainer($container);
foreach ($queue as $value) {
    $stack = $stack->withMiddleware($value);
}
// -------------------------------------------------------------------
// Http Kernel
// -------------------------------------------------------------------
//
$kernel = new Kernel($events, $container->get('router'), new ControllerResolver, $stack, new ArgumentResolver);
$kernel->setContainer($container);

// -------------------------------------------------------------------
// Handle Process
// -------------------------------------------------------------------
//
// Execute the kernel, which turns the request into a response 
// by dispatching route, calling a controller, and returning the response
// 
$response = $kernel->handle($request);

// -------------------------------------------------------------------
// Send Response
// -------------------------------------------------------------------
//
// Send the headers and echo the content
// 
$kernel->send($response);