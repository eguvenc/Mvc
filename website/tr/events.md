
## Olaylar

Olay sınıfı uygulama içerisinde olaylar ilan edip ve bu olayları bağımsız olarak belirlediğiniz dinleyici sınıflar içerisinden yönetmenizi sağlar. Çerçeve içerisinde olay paketi harici olarak kullanılır ve bunun için `Zend/EventManager` tercih edilmiştir.

### Olay servisi

```php
$container->setFactory('events', 'Services\EventManagerFactory');
```

### Tetikleyiciler

> Bir olayı tetiklemek için trigger metotları kullanılır.

Bu metotlardan `triggerEvent()` metodu nesne biçimindeki olayları <b>başlatmayı</b> ve dinleyicilere <b>veri</b> göndermeyi sağlar.

```php
$event = new Event;
$event->setName('router');
$event->setParam('request', $request);

$result = $events->triggerEvent($event);
```

`trigger()` metodu ise bir nesne yaratmadan olayları <b>başlatmayı</b> ve dinleyicilere <b>veri</b> göndermeyi sağlar.

```php
$events = $container->get('events');
$result = $events->trigger('name', null, ['request' => $request]);
```

### Dinleyiciler

> Dinleyiciler daha önceki tetiklenmiş olaylardan gelen verileri kontrol etmeyi sağlarlar.

Aşağıdaki örnekte `ErrorListener` adlı dinleyici sınıfı uygulamadaki hataları kontrol etmenizi sağlıyor.

```php
namespace App\Event;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\ListenerAggregateInterface;

use Obullo\Mvc\Container\{
    ContainerAwareInterface,
    ContainerAwareTrait
};
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
        $error = $e->getParams();

        if (is_object($error)) {
            switch ($error) {
                case ($error instanceof Throwable):
                case ($error instanceof RuntimeException):
                    // error log
                    break;
            }
        }
    }
}
```

`Obullo\Mvc\Error\ErrorHandler` sınıfı içerisinde `ErrorListener` sınıfına ait `error.handler` olayı aşağıdaki gibi tetikleniyor.

```php
protected function handleError(Throwable $exception)
{        
    $this->getContainer()
        ->get('events')
        ->trigger('error.handler',$this,$exception);

    return $this->render(
        'An error was encountered',
        500,
        array(),
        $exception
    );           
}
```

Detaylı dökümentasyona <a href="https://docs.zendframework.com/zend-eventmanager/">https://docs.zendframework.com/zend-eventmanager/</a> bağlantısından ulaşabilirsiniz.