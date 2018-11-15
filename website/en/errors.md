
## Hatalar

Uygulamadaki tüm hatalar `Obullo\Error\ErrorHandler` sınıfı tarafından yönetilir.

> Uygulama içerisindeki php hataları `index.php` dosyası içerisinde <b>set_exception_handler()</b> ve <b>set_error_handler()</b>
komutları ile `ErrorHandler` sınıfına yönlendirilir.

### Hata servisi

```php
$container->setFactory('error', 'Services\ErrorHandlerFactory');
```

Hata servisi diğer servisler gibi `index.php` dosyası içerisinden konfigüre edilir.

### Hata stratejileri

`Html` ve `Json` olmak iki tür hata stratejisi vardır. Varsayılan strateji html türüdür.

> Uygulama bir hata ile karşılaştığında error servisi üzerinden `templates/error.phtml` görünümünü işleyerek `Obullo\Error\ErrorHandler->handle()` fonksiyonu ile `Psr7 Response` nesnesine geri döner.

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

Uygulama katmanları `index.php` dosyasından yönetilir ve tüm katmanlar `App\Middleware` klasöründe yer alır.

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

Eğer uygulama katmanları çalışırken özel bir hata gönderilmek istenirse hata katmanı ilgili katman içerisinden aşağıdaki gibi çağırılır.

Örnek bir işlev;

```php
public function process(Request $request, RequestHandler $handler) : ResponseInterface
{
    if ($request->getMethod() != 'GET') {        
        return $handler->process(new Error('404'));
    }
    return $handler->handle($request);
}
```

Hata katmanına birinci parametreden http durum kodu, ikinciden hata mesajı ve üçüncü parametreden http başlıkları gönderilebilir.

```php
new Error('503', 'Bilinmeyen bir hata oluştu', $headers = array());
```

### Hata dinleyicisi

Hata servisi içerisindeki `ErrorHandler->handle()` metodu içerisine gönderilen tüm hatalar `error.handler` olayını tetikler.

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
namespace App\Event;

class ErrorListener implements ListenerAggregateInterface,ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ListenerAggregateTrait;

    public function attach(EventManagerInterface $events, $priority = null)
    {
        $this->listeners[] = $events->attach('error.handler', [$this, 'onErrorHandler']);
        $this->listeners[] = $events->attach('error.output', [$this, 'onErrorOutput']);
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

    public function onErrorOutput(EventInterface $e) : bool
    {
        $level = error_reporting();
        if ($level > 0) {
            return true;
        }
        return false;
    }
}
```

<table>
    <thead>
        <tr>
            <th>Olay adı</th>
            <th>Dinleyici</th>
            <th>Açıklama</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>error.handler</td>
            <td>onErrorHandler</td>
            <td>Bu metot uygulamadan dönen hataları yakalayarak hata günlükleri oluşturma ve benzeri operasyonlar için hata filtrelemeyi sağlar.</td>
        </tr>
        <tr>
            <td>error.output</td>
            <td>onErrorOutput</td>
            <td>Bu metot ise uygulamadaki hataların gösterilip gösterilmeyeceğini veya hangi hata seviyelerinde gösterileceğini belirler. Fonskiyon sonucu <b>boolean</b> tipinde bir değere dönmek zorundadır.</td>
        </tr>
        </tbody>
</table>

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