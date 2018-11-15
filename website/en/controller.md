
## Kontrolör

`Obullo\Http` kontrolör sınıfı http isteklerini kontrol ederek içerdiği yardımcı metotlar ile istenen http yanıtlarına dönmeniz için ortak bir arayüz sağlar.

```php
namespace App\Controller;

use Obullo\Http\Controller;
use Zend\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DefaultController extends Controller
{
    public function index(Request $request) : Response
    {
        return new HtmlResponse($this->render('welcome'));
    }
}
```

> $this->render($name, $data = null);

Render metodu `view` nesnesi kullanarak html çıktısına döner.


```php
$html = $this->render('welcome');
$html = $this->view->render('welcome'); 
```

Yukarıdaki iki fonksiyon aynı işlevi görür ve işlenmiş görünüm string türünde HtmlResponse nesnesine aktarılır.

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
    public function index(Request $request, $name, $id) : Response
    {
        $string = $name.'-';
        $string.= $id;

        return new HtmlResponse($string);
    }
}
```

Yukarıdaki örnekte görüldüğü gibi yönlendirme kurallarına göre gerçekleşen http çözümlemesinden sonra parametre isimleri parametre değerleri ile doldurulur.


Çıktı

```
test - 1
```

> Parametre isimleri yönlendirme isimlerinde girilen türler ile aynı olmak zorundadır.


### Http yönlendirme

Kontrolör nesnesi içerisindeki `url` yardımcı metodu yönlendirme nesnesini kullanarak güvenli url değerleri oluşturmayı sağlar.

> $this->url($uriOrRouteName = null, $params = []);

Url yardımcı metodu `$router->url()` metodunu çalıştırarak yönlendirme isimleri ile güveli url adresleri oluşturur. 

```php
use Zend\Diactoros\Response\HtmlResponse;

class DefaultController extends Controller
{
    public function index(Request $request, $name, $id) : Response
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
    public function index(Request $request, $name, $id) : Response
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
    public function index(Request $request, $name, $id) : Response
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

Eğer kontrolör sınıfı metot parametreleri, konteyner içerisinde bir servis olarak kayıtlı ise otomatik olarak metot içerisine enjekte edilirler. Aşağıda örnekte birden fazla parametre alan bir http isteği çözümleniyor.

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

### Proxy yöntemi

Eğer parametre alanları işgal edilmek istenmiyorsa bağımlı olunan nesnelere proxy yöntemi ile içeriden de ulaşılabilir.


```php
namespace App\Controller;

use Obullo\Http\Controller;
use Zend\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DefaultController extends Controller
{
    public function index(Request $request, $name, $id) : Response
    {
        $routeName = $router->getMatchedRoute()
            ->getName();

        $html = "Route Name: $routeName<br />";
        $html.= "Name: $name<br />";
        $html.= "ID: $id<br />";

        return new HtmlResponse($html);
    }
}
```