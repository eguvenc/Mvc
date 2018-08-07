
# Obullo / Mvc

[![Build Status](https://travis-ci.org/obullo/Mvc.svg?branch=master)](https://travis-ci.org/obullo/Mvc)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/obullo/mvc.svg)](https://packagist.org/packages/obullo/mvc)

> Obullo ve Zend bileşenleri ile kendi mvc çatınızı oluşturun.

## Proje yaratmak

``` bash
$ composer create project obullo/skeleton
```

## Kurulum

``` bash
$ composer update
```

## Gereksinimler

Bu versiyon aşağıdaki PHP sürümlerini destekliyor.

* 7.0
* 7.1
* 7.2

## Testler

``` bash
$ vendor/bin/phpunit
```

## Hızlı başlangıç

Kök dizindeki `public/app/index.php` dosyasına göz atın.

```php
require '../../vendor/autoload.php';

define('ROOT', dirname(dirname(__DIR__)));
define('APP', 'App');

use Obullo\Mvc\Application;
use Zend\ServiceManager\ServiceManager;
use Dotenv\Dotenv;
```

Ortam Yöneticisi

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

Servis Yöneticisi

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

İstisnai Hata Kontrolü

```php
set_exception_handler(array($container->get('error'), 'handle'));
```

Olay Dinleyiciler

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

Yanıt Gönderici

```php
$response = $application->process($queue = [], $container->get('request'));
$application->sendResponse($response);
```

## Konteyner & Servisler

[Container.md](container.md)

## Yönlendirmeler

[Router.md](router.md)

## Konfigürasyon

[Config.md](config.md)

## Kontrolör & Bağımlılıklar

[Controller.md](controller.md)

## Hatalar

[Error.md](error.md)

## Çerezler

[Coookie.md](cookie.md)

## Loglama

[Logger.md](logger.md)

## Katmanlar

[Middleware.md](middleware.md)

## Olaylar

[Events.md](events.md)

## Oturumlar

[session.md](session.md)

## Görünümler

[View.md](view.md)
