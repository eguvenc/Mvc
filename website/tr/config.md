
## Konfigürasyon

Konfigürasyon sınıfı `Obullo/Config` paketini kullanır. Bu paket `Zend/Config` paketi üzerinde çalışır.

### Dosyalar

Yükleyici servisi konfigürasyon dosyalarına her yerden erişimi sağlar.

```php
$amqp = $container->get('loader')
        ->load(ROOT, '/config/amqp.yaml')
        ->amqp;

echo $amqp->host; // 127.0.0.1
```

Bu servise ait konfigürasyona `App/Services/LoaderFactory.php` dosyasından erişebilirsiniz.

### Autoload

Uygulama `config/autoload/` dizini içerisine konulan konfigürasyon dosyalarını otomatik olarak yükler. Bunun için `ZendConfigProvider` sınıfını kullanmanız gerekir.

```php
$aggregator = new ConfigAggregator(
    [
        new ArrayProvider([ConfigAggregator::ENABLE_CACHE => true]),
        new ZendConfigProvider(ROOT.'config/autoload/{,*.}{json,yaml,php}'),
    ],
    ROOT.'/var/cache/config.php'
);
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
            [ConfigAggregator::ENABLE_CACHE => (getenv('APP_ENV') == 'dev') ? false : true ]
        ),
        new ZendConfigProvider(ROOT.'/config/autoload/{,*.}{json,yaml,php}'),
    ],
    ROOT.'/var/cache/config.php'
);
```

Detaylı dökümentasyona <a href="http://config.obullo.com/tr/">http://config.obullo.com/tr/</a> bağlantısından ulaşabilirsiniz.