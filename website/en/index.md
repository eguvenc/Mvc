
# Obullo / Mvc

[![Build Status](https://travis-ci.org/obullo/Mvc.svg?branch=master)](https://travis-ci.org/obullo/Mvc)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/obullo/mvc.svg)](https://packagist.org/packages/obullo/mvc)

> Create your mvc framework using obullo and zend components.

## Create your project

``` bash
$ composer create project obullo/skeleton
```

## Install

``` bash
$ composer update
```

## Requirements

The following versions of PHP are supported by this version.

* 7.0
* 7.1
* 7.2

## Testing

``` bash
$ vendor/bin/phpunit
```

## Quick start

Check your `public/app/index.php` file.

```php
require '../../vendor/autoload.php';

define('ROOT', dirname(dirname(__DIR__)));
define('APP', 'App');

use Obullo\Http\Application;
use Zend\ServiceManager\ServiceManager;
use Dotenv\Dotenv;
```

Environment Manager

```php
if (false == isset($_SERVER['APP_ENV'])) {
    (new Dotenv(ROOT))->load();
}
$env = $_SERVER['APP_ENV'] ?? 'dev';

if ('prod' !== $env) {
    ini_set('display_errors', 1);  
    error_reporting(E_ALL);
}
```

Service Manager

```php
$container = new ServiceManager;
$container->setFactory('loader', 'Services\LoaderFactory');
$container->setFactory('translator', 'Services\TranslatorFactory');
$container->setFactory('events', 'Services\EventManagerFactory');
$container->setFactory('request', 'Services\RequestFactory');
$container->setFactory('session', 'Services\SessionFactory');
$container->setFactory('view', 'Services\ViewPlatesFactory');
$container->setFactory('logger', 'Services\LoggerFactory');
$container->setFactory('cookie', 'Services\CookieFactory');
$container->setFactory('flash', 'Services\FlashMessengerFactory');
$container->setFactory('error', 'Services\ErrorHandlerFactory');
$container->setFactory('escaper', 'Services\EscaperFactory');
```

Exception Handler

```php
set_exception_handler(array($container->get('error'), 'handle'));
```

Event Listeners

```php
$listeners = [
    'App\Event\SessionListener',
    'App\Event\ErrorListener',
    'App\Event\RouteListener',
    // 'App\Event\HttpMethodListener',
    'App\Event\SendResponseListener',
];
$application = new Application($container, $listeners);
$application->start();
```

Response Sender

```php
$response = $application->process($queue = [], $container->get('request'));
$application->sendResponse($response);
```

## Container & Services

[Container.md](/en/Container.md)

## Routing

[Router.md](/en/Router.md)

## Config

[Config.md](/en/Couter.md)

## Controller & Depedencies

[Controller.md](/en/Controller.md)

## Error

[Error.md](/en/Error.md)

## Cookie

[Coookie.md](/en/Cookie.md)

## Logger

[Logger.md](/en/Logger.md)

## Middleware

[Middleware.md](/en/Middleware.md)

## Events

[Events.md](/en/Events.md)

## Session

[Logging.md](/en/Logging.md)

## View

[View.md](/en/View.md)