
## Kontrolör

`Obullo\Http` kontrolör sınıfı http isteklerini kontrol ederek içerdiği yardımcı metotlar ile istenen http yanıtlarına dönmenizi sağlar.

```php
namespace App\Controller;

use Obullo\Http\Controller;
use Zend\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DefaultController extends Controller
{
    public function index() : Response
    {
        return new HtmlResponse($this->renderView('Welcome.phtml'));
    }
}
```

> $this->renderView($name, $data = array());

RenderView metodu `view` nesnesi kullanarak html çıktısına döner.


```php
$html = $this->renderView('Welcome.phtml');
$html = $this->getContainer('html')->render('Welcome.phtml'); 
```

Yukarıdaki iki fonksiyon aynı işlevi görür. İşlenmiş görünüm string türünde elde edildikten sonra`HtmlResponse` nesnesine aktarılmalıdır.

```php
return new HtmlResponse($html);
```

### Parametreler

Aşağıda örnekte birden fazla parametre alan bir http isteği çözümleniyor.

```php
http://example.com/test/1
```

App `/config/routes.yaml` dosyası içeriği

```
home:
    path: /<str:name>/<int:id>
    handler: App\Controller\DefaultController::index
```

Kontrolör dosyası örneği.

```php
namespace App\Controller;

use Obullo\Http\Controller;
use Zend\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DefaultController extends Controller
{
    public function index($name, $id) : Response
    {
        $string = $name.'-';
        $string.= $id;

        return new HtmlResponse($string);
    }
}
```

Yukarıdaki örnekte görüldüğü gibi yönlendirme kurallarına göre gerçekleşen http çözümlemesinden sonra parametre isimleri parametre değerleri ile dolduruldu.


Çıktı

```
test - 1
```

> Parametre isimleri yönlendirme isimlerinde girilen türler ile aynı olmak zorundadır.


### Http yönlendirme

Kontrolör nesnesi içerisindeki `url` yardımcı metodu yönlendirme nesnesini kullanarak güvenli url değerleri oluşturmayı sağlar.

> $this->url($uriOrRouteName = null, $params = []);

Url yardımcı metodu `$router->url()` metodunu çalıştırır.

```php
use Zend\Diactoros\Response\HtmlResponse;

class DefaultController extends Controller
{
    public function index($name, $id) : Response
    {
        return new RedirectResponse($this->url('home', ['name' => $name, 'id' => $id]));
    }
}
```

Url isimleri oluşturulan url adresleri kullanmak uygulamanızın her zaman daha güvenli olmasını sağlayacaktır. Eğer url değeri bir yönlendirme ismi değilse girilen url değerine geri döner.

```php
use Zend\Diactoros\Response\RedirectResponse;

class DefaultController extends Controller
{
    public function index($name, $id) : Response
    {
        return new RedirectResponse($this->url('/another/page'));
    }
}
```

### Json

Json yanıtları için `Zend\Diactoros\Response\JsonResponse` nesnesi kullanılmalıdır.

```php
new JsonResponse($data, $status = 200, array $headers = [], $encodingOptions = self::DEFAULT_JSON_FLAGS);
```

Birinci parametere dizi türünde veri, ikinci parametere http durum kodu, üçüncü parametre http json başlıkları ve son parametre kodlama opsiyonları içindir.

```php
use Zend\Diactoros\Response\JsonResponse;

class DefaultController extends Controller
{
    public function index($name, $id) : Response
    {
    	$data = [
    		'foo' => 'bar'
    	];
        return new JsonResponse($data);
    }
}
```

Json yanıt için bir tam örnek;

```php
return new JsonResponse(
    ['name' => 'Example data'],
    200,
    ['cache-control' => 'max-age=3600'],
    JSON_UNESCAPED_UNICODE
);
```

Çıktı

```php
{
  "name": "Example data"
}
```

Çıktıya ait başlıklar

```
Cache-Control   max-age=3600
Connection  Keep-Alive
Content-Length  22
Content-Type    application/json
Date    Sat, 11 Aug 2018 09:32:45 GMT
Expires Thu, 19 Nov 1981 08:52:00 GMT
Keep-Alive  timeout=5, max=100
Pragma  no-cache
Server  Apache/2.4.29 (Ubuntu)
```

### Bağımlılıklar 

Eğer kontrolör sınıfı `__construct()` metodu parametreleri, konteyner içerisinde bir servis olarak kayıtlı ise otomatik olarak metot içerisine enjekte edilirler. Aşağıda örnekte birden fazla parametre alan bir http isteği çözümleniyor.

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

use Obullo\Http\Controller;
use Obullo\Router\Router;
use Zend\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DefaultController extends Controller
{
    public function __construct(Request $request, Router $router)
    {
        $this->router = $router;
        $this->request = $request;
    }

    public function index($name, $id) : Response
    {
        $routeName = $this->router->getMatchedRoute()
            ->getName();

        $html = "Route Name: $routeName<br />";
        $html.= "Name: $name<br />";
        $html.= "ID: $id<br />";
        $html.= "Query params:".print_r($this->request->getQueryParams(), true);

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

### LazyControllerFactory

Kontrolör sınıfı `__construct()` metodu parametrelerinin enjeksiyonu `classes\ServiceManager\LazyControllerFactory` sınıfı tarafından gerçekleştirilir. Bu sınıf isteklerinize göre özelleştirebilir olduğu için bağımlılık enjeksiyonu daha esnek hale getirir.

```php
namespace ServiceManager;

use ReflectionClass;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class LazyControllerFactory implements AbstractFactoryInterface
{
    /**
     * Determine if we can create a service with name
     *
     * @param Container $container
     * @param $name
     * @param $requestedName
     *
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        list($bundleName) = explode('\\', __NAMESPACE__, 2);
        return strstr($requestedName, $bundleName . '\Controller') !== false;
    }

    /**
     * These aliases work to substitute class names with Service Manager types that are buried in framework
     * 
     * @var array
     */
    protected $aliases = [
        'Obullo\Router\Router' => 'router',
        'Psr\Http\Message\RequestInterface' => 'request',
        'Obullo\Http\SubRequestInterface' => 'subRequest',
        'Zend\Form\FormElementManager' => 'formElement',
        'Zend\Validator\ValidatorPluginManager' => 'validatorManager',
        'Zend\Mvc\I18n\Translator' => 'translator',
    ];

    /**
     * Create service with name
     *
     * @param Container $container
     * @param $requestedName
     *
     * @return mixed
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $class = new ReflectionClass($requestedName);

        $injectedParameters = array();
        if ($constructor = $class->getConstructor()) {
            if ($params = $constructor->getParameters()) {
                foreach($params as $param) {
                    if ($param->getClass()) {
                        $name = $param->getClass()->getName();
                        if (array_key_exists($name, $this->aliases)) {
                            $name = $this->aliases[$name];
                        }
                        if ($container->has($name)) {
                            $injectedParameters[] = $container->get($name);
                        }
                    }
                }
            }
        }
        return new $requestedName(...$injectedParameters);
    }
}
```

### Kontrolör bağımlılık yönetimi

`LazyControllerFactory` sınıfının kontrölör dosyalarınızdaki bağımlılık enjeksiyonunu yönetebilmesi için her modül içerisindeki `BundleListener` dinleyicisi içerisinde ihtiyaç duyulan kontrolörlerin aşağıdaki gibi konfigüre edilmesi gerekir.

```php
namespace App\Event;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Obullo\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
class BundleListener implements ListenerAggregateInterface,ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ListenerAggregateTrait;

    public function attach(EventManagerInterface $events, $priority = null)
    {
        $this->listeners[] = $events->attach('App.bootstrap', [$this, 'onBootstrap']);
    }

    public function onBootstrap(EventInterface $e)
    {
        $container = $this->getContainer();
        $container->configure(
            [
                 'factories' => [
                     \App\Controller\DefaultController::class => \ServiceManager\LazyControllerFactory::class
                 ]
            ]
        );
    }
}
```