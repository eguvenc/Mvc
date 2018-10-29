
## Hatalar

Uygulamadaki tüm hatalar `error` servisi üzerinden yönetilir.

### Hata servisi

```php
$container->setFactory('error', 'Services\ErrorHandlerFactory');
```

Hata servisi diğer servisler gibi `index.php` dosyası içerisinden konfigüre edilir.

### Hata stratejileri

`Html` ve `Json` olmak iki tür hata stratejisi vardır. Varsayılan strateji html türüdür. Uygulama bir hata ile karşılaştığında error servisi üzerinden `templates/error.phtml` görünümünü işleyerek `Obullo\Error\ErrorHandler->handle()` fonksiyonu ile Psr7 `response` nesnesine geri döner.

```php
namespace Services;

use Obullo\Error\{
    ErrorHandler,
    HtmlStrategy
};
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ErrorHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $error = new ErrorHandler;
        $error->setContainer($container);

        $strategy = new HtmlStrategy($container->get('view'));
        $strategy->setTranslator($container->get('translator'));
        $error->setResponseStrategy($strategy);

        return $error;
    }
}
```

App `templates/error.phtml` görünümü özelleştirilebilir.


### Hata katmanı

Katmanlar varsayılan olarak `index.php` dosyasında tanımlıdır.

```php
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
```

Uygulama içerisindeki diğer katmanlar içerisinden özel bir hata gönderilmek istenirse hata katmanı çağırılır.

```php
public function process(Request $request, RequestHandler $handler) : ResponseInterface
{
    $match = false;
    if (! $match) {        
        return $handler->process(new Error('404'));
    }
    return $handler->handle($request);
}
```

Birinci parametreden http durum kodu, ikinciden hata mesajı ve üçüncü parametreden http başlıkları gönderilebilir.

```php
new Error('503', 'Bilinmeyen bir hata oluştu', $headers = array());
```

### Hata dinleyicisi

Hata servisi içerisindeki `ErrorHandler->handle()` metodu içerisine gönderilen tüm hatalar `error.handler` olayına tayin edilir.

```php
$event = new Event;
$event->setName('error.handler');
$event->setParam('exception', $exception);
$event->setTarget($this);

$container->get('events')
    ->triggerEvent($event);
```

Bir olay olarak gerçekleşen bu hatalar `App\Event\ErrorListener` sınıfı tarafından dinlenerek hata yönetiminin özelleştirilebilmesini sağlar.

```php
class ErrorListener implements ListenerAggregateInterface,ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ListenerAggregateTrait;

    public function attach(EventManagerInterface $events, $priority = null)
    {
        $this->listeners[] = $events->attach('error.handler', [$this, 'onErrorHandler']);
    }

    public function onErrorHandler(EventInterface $e)
    {
        $error = $e->getParam('exception');

        switch ($error) {
            case ($error instanceof Throwable):
            case ($error instanceof RuntimeException):
                // error log
                break;
        }
    }
}
```

### İstisnai hatalar

İstisnai hatalar oluştuğunda bu hatalar aşağıdaki gibi `index.php` dosyasından `Error` sınıfına yönlendirilir.

```php
// -------------------------------------------------------------------
// Exception Handler
// -------------------------------------------------------------------
//
set_exception_handler(array($container->get('error'), 'handle'));
```

### Php hataları

Php hataları `ErrorException` sınıfı ile istisnai hatalara dönüştürülür ve tüm hataların tek bir yerden kontrolü sağlanmış olur.

```php
// -------------------------------------------------------------------
// Error Handler
// -------------------------------------------------------------------
//
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});
```

> Error yönetimi `index.php` dosyasından özelleştirilebilir.