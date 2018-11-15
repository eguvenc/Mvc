
## Yönlendirmeler

Yönlendirme sınıfı dışarıdan gelen http adresini çözümler ve konfigürasyon dosyasındaki eşleşmeye göre uygulamanın çıktı üretmesi için kullanıcıyı ilgli kontrolör dosyasına yönlendirir. Çerçeve içerisinde yönlendirme paketi harici olarak kullanılır ve bunun için <a href="http://router.obullo.com/">Obullo/Router</a> paketi tercih edilmiştir.

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

### Mevcut parametreler

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

### Yönlendirme Türleri

Yönlendirme servisinde tanımlı olan yönlendirme türleri uygulamanızda bulun url adreslerindeki parametreleri belirli türlere zorlar.

```php
$config = array(
    'types' => [
        new IntType('<int:id>'),
        new StrType('<str:name>'),
        new StrType('<str:word>'),
        new AnyType('<any:any>'),
        new BoolType('<bool:status>'),
        new IntType('<int:page>'),
        new FourDigitYearType('<yyyy:year>'),
        new TwoDigitDayType('<dd:day>'),
        new TwoDigitMonthType('<mm:month>'),
        new TranslationType('<locale:locale>'),
        new SlugType('<slug:slug>'),
        new SlugType('<slug:slug_>', '(?<%s>[\w-_]+)'), // slug with underscore
    ]
);
```

Uygulamamızın aşağıdaki gibi bir http isteği aldığını varsayalım.

```php
http://example.com/2018/yoga-festival-india
```

Bu durumda `<yyyy:year>` ve `<slug:slug>` türlerini kullanmamız gerekir.

```
news:
    path: /<yyyy:year>/<slug:slug>
    handler: App\Controller\NewsController::index
```

Mevcut türler dışında uygulama içinde kendi türlerinizi de oluşturabilirsiniz. Türler hakkında detaylı bilgiyi <a href="http://router.obullo.com/tr/types.html">http://router.obullo.com/tr/types.html</a> adresinden elde edebilirsiniz.


### Çözümleme

Aşağıda örnekte birden fazla parametre alan bir http isteği çözümleniyor.

```php
http://example.com/test/1?foo=bar
```

App `/config/routes.yaml` dosyası içeriği

```
home:
    path: /<str:name>/<int:id>
    handler: App\Controller\DefaultController::index
```

Kontrolör dosyası

```php
namespace App\Controller;

use Obullo\Router\Router;
use Zend\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DefaultController
{
    public function index(Request $request, Router $router, $name, $id) : Response
    {
        $routeName = $router->getMatchedRoute()
            ->getName();

        $html = "Route Name: $routeName<br />";
        $html.= "Name: $name<br />";
        $html.= "ID: $id<br />";
        $html.= "Query params:".print_r($request->getQueryParams(), true);

        return new HtmlResponse($html);
    }
}
```

Yukarıdaki örneğin çıktısı:

```php
Route Name: home
Name: test
ID: 1
Query params: Array ( [foo] => bar ) 
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

Uygulama routing ile ilgili işlemler için `App/Event/RouteListener` sınıfını dinler.

<table>
    <thead>
        <tr>
            <th>Olay</th>
            <th>Dinleyici</th>
            <th>Açıklama</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>route.match</td>
            <td>onMatch</td>
            <td>Route eşleşmesinin gerçekleştiği olaydır. Metot eşleşmenin olduğu `Route` nesnesine geri döner böylece bu metot içerisinden özelleştirmeler yapılabilir.</td>
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

> Yönlendirme paketi hakkında detaylı dökümentasyona <a href="http://router.obullo.com/tr/">http://router.obullo.com/tr/</a> bağlantısından ulaşabilirsiniz.