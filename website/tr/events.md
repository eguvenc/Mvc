
## Olaylar

Olay sınıfı uygulama içerisinde olaylar ilan edip önceden belirlediğimiz dinleyici sınıflar ile bu olayları yönetmemizi sağlar. Çerçeve içerisinde olay paketi harici olarak kullanılır ve bunun için `Zend/EventManager` tercih edilmiştir.

Paket mevcut değil ise aşağıdaki konsol komutu ile yüklenmelidir.

```bash
composer require zendframework/zend-eventmanager
```

### Olay servisi

Olay nesnesi diğer servisler gibi `index.php` dosyası içerisinden konfigüre edilir. 

```php
$container->setFactory('events', 'Services\EventManagerFactory');
```

Olay servisi `Zend\EventManager\EventManager` nesnesine geri döner.

```php
namespace Services;

use Zend\EventManager\EventManager;

class EventManagerFactory implements FactoryInterface
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
        $container->setAlias('EventManager', $requestedName);

        return new EventManager;
    }
}
```

### Tetikleyiciler

> Uygulama için bir olayı tetiklemek için trigger metotları kullanılır.

Bu metotlardan `triggerEvent()` metodu nesne biçimindeki olayları <b>başlatmayı</b> ve dinleyicilere <b>veri</b> göndermeyi sağlar.

```php
$event = new Event;
$event->setName('router');
$event->setParam('request', $request);
$events->triggerEvent($event);
```

`trigger()` metodunun farkı ise <b>nesne yaratmadan</b> olayları başlatmasıdır.

```php
$events = $container->get('events');
$events->trigger('name', null, ['request' => $request]);
```

Bir olaydan dönen değeri almak için <b>last()</b> metodu kullanılır. 

```php
$result = $events->triggerEvent($event)->last();
```

### Dinleyiciler

Dinleyiciler uygulamanın çalışması esnasında tetiklenmiş olaylardan gelen verileri izlemeyi sağlarlar.

> Uygulama dinleyicileri `App\Event` klasöründe yer alır.

Aşağıdaki örnekte `ErrorListener` adlı dinleyici sınıfı uygulamadaki hataları izlemeyi ve yönetmenizi sağlıyor.

```php
namespace App\Event;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\ListenerAggregateInterface;
use Obullo\Container\{
    ContainerAwareInterface,
    ContainerAwareTrait
};
use Throwable;
use RuntimeException;

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

`Obullo\Error\ErrorHandler` sınıfı içerisinde `ErrorListener` sınıfına ait `error.handler` olayı aşağıdaki gibi tetikleniyor.

```php
/**
 * Error handler class handler errror
 *
 * @param mixed $error mostly exception object
 *
 * @return object response
 */
protected function handleError(Throwable $exception)
{        
    $container = $this->getContainer();

    $event = new Event;
    $event->setName('error.handler');
    $event->setParam('exception', $exception);
    $event->setTarget($this);

    $container->get('events')
        ->triggerEvent($event);

    return $this->render(
        'An error was encountered',
        500,
        array(),
        $exception
    );           
}
```

Aşağıdaki örnekte ise `onErrorOutput` metodunu dinleyen `error.output` olayından dönen sonuç hataların hangi seviyede gösterileceğini belirliyor. 

```php
/**
 * Error handler class emit body
 * 
 * @return void
 */
protected function emitBody($response)
{
    $event = new Event;
    $event->setName('error.output');
    $event->setTarget($this);

    $container = $this->getContainer();
    $result = $container->get('events')
        ->triggerEvent($event)
        ->last();

    if ($result) {
        echo $response->getBody();
    }
}
```

Detaylı dökümentasyona <a href="https://docs.zendframework.com/zend-eventmanager/">https://docs.zendframework.com/zend-eventmanager/</a> bağlantısından ulaşabilirsiniz.