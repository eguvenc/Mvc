
## Kontrolör

Kontrolör sınıfı http isteklerini kontrol ederek içerdiği metotlar ile istenen http yanıtlarına dönmenizi sağlar.

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
```

Ve işlenmiş görünüm string türünde HtmlResponse nesnesine aktarılır.

```php
return new HtmlResponse($html);
```

### Parametreler

Aşağıdaki parametrelerle bir http isteğinin olduğunu varsayalım.

```php
http://example.com/test/1
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

Yukarıdaki örnekte görüldüğü gibi parametre isimleri parametre değerleri ile doldurulur.


Çıktı

```
test - 1
```

> Parametre isimleri yönlendirme isimlerinde girilen türler ile aynı olmak zorundadır.


### Http yönlendirme

Url kontrolör metodu yönlendirme nesnesine güvenli url değerleri oluşturmayı sağlar.

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

> $this->url($uriOrRouteName = null, $params = []);

Url yardımcı metodu `$router->url()` metodunu çalıştırarak yönlendirme isimleri ile güveli url adresleri oluşturur. Url isimleri oluşturulan url adresleri kullanmak uygulamanızın her zaman daha güvenli olmasını sağlayacaktır.

Eğer url değeri bir yönlendirme ismi değilse girilen url değerine geri döner.

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

### Json

Json yanıtları için http json başlıkları ile `Zend\Diactoros\Response\JsonResponse` kullanılmalıdır.

```php
new JsonResponse($data, $status = 200, array $headers = [], $encodingOptions = self::DEFAULT_JSON_FLAGS);
```

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

Bir rest api için örnek;

```php
return new JsonResponse(
    ['name' => 'Örnek Veri'],
    200,
    ['cache-control' => 'max-age=3600'],
    JSON_UNESCAPED_UNICODE
);
```

Çıktı

```php
{
  "name": "Örnek Veri"
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

Eğer kontrolör sınıfı metot parametreleri, konteyner içerisinde bir servis olarak kayıtlı ise otomatik olarak metot içerisine enjekte edilirler.

```php
http://example.com/test/1
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
        $html = $router->getMatchedRoute()
            ->getName();

        return new HtmlResponse($this->render($html));
    }
}
```

Yukarıdaki örneğin çıktısı:

```php
home
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
        $html = $this->router->getMatchedRoute()
            ->getName();

        return new HtmlResponse($this->render($html));
    }
}
```