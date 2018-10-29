
## Konfigürasyon

Konfigürasyon paketi konfigürasyon dosyaları yönetimini üstlenir. Çerçeve içerisinde bu paket bir bileşen olarak kullanılır ve bunun için `Zend/Config` paketi üzerinde çalışan <a href="http://config.obullo.com/">Obullo/Config</a> paketi tercih edilmiştir.


Paket mevcut değil ise aşağıdaki konsol komutu ile yüklenmelidir.

```bash
composer require obullo/config
```

### Dosyalar

Konfigürasyon servisi konfigürasyon dosyalarına her yerden erişimi sağlar.

```php
$amqp = $container->get('loader')
        ->load(ROOT, '/config/amqp.yaml')
        ->amqp;

echo $amqp->host; // 127.0.0.1
```

### Konfigürasyon yükleyici

Yükleyici nesnesi diğer servisler gibi `index.php` dosyası içerisinden konfigüre edilir. 

```php
$container->setFactory('loader', 'Services\LoaderFactory');
```

Yükleyici servisi `Obullo\Config\ConfigLoader` nesnesine geri döner.

```php
namespace Services;

use Obullo\Config\ConfigLoader;
use Obullo\Config\Processor\Env as EnvProcessor;
use Zend\Config\Processor\Constant as ConstantProcessor;

class LoaderFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @link http://config.obullo.com/ documentation of config package.
     * 
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $container->setService('yaml', new YamlReader([SymfonyYaml::class, 'parse']));

        Factory::registerReader('yaml', $container->get('yaml'));
        Factory::setReaderPluginManager($container);

        $env = getenv('APP_ENV');

        $aggregator = new ConfigAggregator(
            [
                new ArrayProvider([ConfigAggregator::ENABLE_CACHE => ($env == 'dev') ? false : true ]),
                new ZendConfigProvider(ROOT.'/config/autoload/{,*.}{json,yaml,php}'),
            ],
            ROOT.'/var/cache/config.php'
        );
        $config = $aggregator->getMergedConfig();
        $container->setService('config', new Config($config, true));  // Create global config object

        $loader = new ConfigLoader(
            $config,
            ROOT.'/var/cache/config.php'
        );
        $loader->setEnv($env);
        $loader->addProcessor(new EnvProcessor);
        $loader->addProcessor(new ConstantProcessor);

        $loader->load(ROOT, '/config/%s/framework.yaml');
        
        return $loader;
    }
}
```

### Otomatik yükleme

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