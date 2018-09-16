
## Yönlendirmeler

Yönlendirme sınıfı `Obullo/Router` paketini kullanır. Bu paket <a href="https://docs.djangoproject.com/en/2.0/topics/http/urls/">Django Url Dispatcher</a> kütüphanesinden ilham alınarak geliştirilmiş bağımsız bir paketdir.

### Uygulama yönlendirme

Yönlendirmeler `config/routes.yaml` dosyası içerisinde muhafaza edilir.

```
home:
    path: /
    handler: App\Controller\DefaultController::index

dummy:
    path: /dummy
    handler: App\Controller\DefaultController::dummy
```

### Parametreler

```
name:
    method : GET
    host: example.com
    scheme: http
    middleware: 
      - App\Middleware\Auth
      - App\Middleware\Dummy
    path: /
    handler: App\Controller\DefaultController::index
```

### Önbellekteki dosyalar

Konfigürasyon dosyaları `cache` açıksa önbelleğe alınır. Bu dosyayı aşağıdaki komutla silebilirsiniz.

```
$ rm var/cache/config.php
```

`dev` ortamında cache parametresinin `false` değerinde olması gerekmektedir.

```php
$aggregator = new ConfigAggregator(
    [
        new ArrayProvider(
            [ConfigAggregator::ENABLE_CACHE => (getenv('APP_ENV') == 'dev') ? false : true]
        ),
        new ZendConfigProvider(ROOT.'/config/autoload/{,*.}{json,yaml,php}'),
    ],
    ROOT.'/var/cache/config.php'
);
```

Detaylı dökümentasyona <a href="http://config.obullo.com/tr/">http://config.obullo.com/tr/</a> bağlantısından ulaşabilirsiniz.


### Route olayları

Uygulama routing ile ilgili işlemler için `App/Event/RouteListener` sınıfı dinler.


<table>
    <thead>
        <tr>
            <th>Olay</th>
            <th>Açıklama</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>OnMatch</td>
            <td>Route eşleşmesinin gerçekleştiği olaydır.Metot eşleşmenin olduğu `Route` nesnesine geri döner böylece bu metot içerisinden özelleştirmeler yapılabilir.</td>
        </tr>
    </tbody>
</table>


```php
class RouteListener implements ListenerAggregateInterface,ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ListenerAggregateTrait;

    public function attach(EventManagerInterface $events, $priority = null)
    {
        $this->listeners[] = $events->attach('route.match', [$this, 'onMatch']);
    }

    public function onMatch(EventInterface $e)
    {
        /*
        $route = $e->getParams();
        $route->getName();
        */
    }
}
```

Detaylı dökümentasyona <a href="http://router.obullo.com/tr/">http://router.obullo.com/tr/</a> bağlantısından ulaşabilirsiniz.