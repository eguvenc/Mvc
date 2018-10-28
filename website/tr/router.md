
## Yönlendirmeler

Yönlendirme sınıfı <a href="http://router.obullo.com/">Obullo/Router</a> paketini kullanır. Bu paket <a href="https://docs.djangoproject.com/en/2.0/topics/http/urls/">Django Url Dispatcher</a> kütüphanesinden ilham alınarak geliştirilmiş bağımsız bir paketdir.

### Uygulama yönlendirme

Yönlendirmeler `config/routes.yaml` dosyası içerisinde muhafaza edilir.

```
home:
    path: /
    handler: App\Controller\DefaultController::index
```

### Parametreler

```
name:
    method : GET
    host: example.com
    scheme: http
    middleware: 
      - App\Middleware\Auth
      - App\Middleware\Guest
    path: /
    handler: App\Controller\DefaultController::index
```

### Önbellekteki dosyalar

Konfigürasyon dosyaları `cache` açıksa önbelleğe alınır. Bu dosyayı proje kök dizininde iken aşağıdaki komutla silebilirsiniz.

```
$ php console cache:clear
```

Önbellek temizleme yani `cache:clear` komutu `/var/cache/` klasörü altındaki `config.php` dosyasını temizler. Yerel sunucuda yani `dev` ortamında cache parametresinin `false` değerinde olması gerekmektedir. Aksi durumda yönlendirme yada konfigürasyon değişiklikleri çalışmayacaktır.

```php
$aggregator = new ConfigAggregator(
    [
        new ArrayProvider(
            [ConfigAggregator::ENABLE_CACHE => (getenv('APP_ENV') == 'dev') ? false : true ]
        ),
        new ZendConfigProvider(ROOT.'/config/autoload/{,*.}{json,yaml,php}'),
    ],
    ROOT.'/var/cache/config.php'
);
```

> Önbellekleme yerel sunucuda varsayılan olarak kapalıdır, fakat canlı sunucularda (prod ortamında) bu konfigürasyon aktif hale gelir. Konfigürasyon ve yönlendirme dosyalarındaki değişikliklerin geçerli olabilmesi için her `deploy` işleminde `rm var/cache/config.php`  veya `php console cache:clear` komutunu çalıştıran bir `bash script` yazmanız tavsiye edilir. 

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
        // $route = $e->getParam('route');
        // $route->getName();
    }
}
```

Detaylı dökümentasyona <a href="http://router.obullo.com/tr/">http://router.obullo.com/tr/</a> bağlantısından ulaşabilirsiniz.