
## Yönlendirmeler

Yönlendirme sınıfı dışarıdan gelen http uri adresini çözümler ve konfigürasyon dosyasındaki eşleşmeye göre uygulamanın çıktı üretmesi için kullanıcıyı ilgli kontrolör dosyasına yönlendirir. Çerçeve içerisinde yönlendirme paketi harici olarak kullanılır ve bunun için <a href="http://router.obullo.com/">Obullo/Router</a> paketi tercih edilmiştir.

Paket mevcut değil ise aşağıdaki konsol komutu ile yüklenmelidir.

```bash
composer require obullo/router
```

### Yönlendirme servisi

Yönelendirme nesnesi diğer servisler gibi `index.php` dosyası içerisinden konfigüre edilir. 

```php
$container->setFactory('router', 'Services\RouterFactory');
```

Yönlendirme servisi `Obullo\Router\Router` nesnesine geri döner.


```php
namespace Services;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class RouterFactory implements FactoryInterface
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
        $types = [
            new IntType('<int:id>'),
            new IntType('<int:page>'),
            new StrType('<str:name>'),
            new TranslationType('<locale:locale>'),
        ];
        $context = new RequestContext;
        $context->fromRequest($container->get('request'));
         
        $collection = new RouteCollection(['types' => $types]);
        $collection->setContext($context);

        $builder = new Builder($collection);
        $routes = $container
            ->get('loader')
            ->load(ROOT, '/config/routes.yaml')
            ->toArray();
            
        $collection = $builder->build($routes);

        return new Router($collection);
    }
}
```

### Uygulama yönlendirme

Yönlendirmeler `config/routes.yaml` dosyası içerisinde muhafaza edilir.

```
home:
    path: /
    handler: App\Controller\DefaultController::index
```

### Parametreler

Bir yönlendirme konfigürasyonunun alabileceği değerler şeması aşağıdaki gibidir.

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