
## Kontrolör

Kontrolör sınıfı http isteklerini kontrol ederek içerdiği metotlar ile istenen http yanıtlarına dönmenizi sağlar.

```php
namespace App\Controller;

use Obullo\Mvc\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DefaultController extends Controller
{
    public function index(Request $request) : Response
    {
        return $this->render('welcome');
    }
}
```

> $this->render($nameOrModal, $data = null, $status = 200, $headers = []) : ResponseInterface


Render metodu aşağıdaki metotu çağırarak Response nesnesine içerisine html çıktısını ekler.

```php
return new HtmlResponse($this->view->render('welcome'));
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

use Obullo\Mvc\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DefaultController extends Controller
{
    public function index(Request $request, $name, $id) : Response
    {
        $html = $name.'<br />';
        $html.= $id.'<br />';

        return $this->renderHtml($html);
    }
}
```

> $this->renderHtml(string $html, $status = 200, $headers = []) : ResponseInterface

RenderHtml metodu ise view nesnesi kullanmadan Response nesnesine içerisine html çıktısını direkt ekler.

Yukarıdaki örneğin çıktısı:

```php
test
1
```

### Http yönlendirme

Redirect metodu http `302` durum kodu ile `Zend\Diactoros\Response\RedirectResponse` nesnesine geri döner.

```php
class DefaultController extends Controller
{
    public function index(Request $request, $name, $id) : Response
    {
        return $this->redirect('/another/page');
    }
}
```

> $this->redirect($uriOrRouteName = null, $params = []) : ResponseInterface

Route isimleri kullanmak uygulamanızın her zaman daha güvenli olmasını sağlayacaktır.

```php
class DefaultController extends Controller
{
    public function index(Request $request, $name, $id) : Response
    {
        return $this->redirect('home', ['name' => $name, 'id' => $id]);
    }
}
```

### Json

Json metodu http json başlıkları ile `Zend\Diactoros\Response\JsonResponse` nesnesine geri döner.

```php
class DefaultController extends Controller
{
    public function index(Request $request, $name, $id) : Response
    {
    	$data = [
    		'foo' => 'bar'
    	];
        return $this->json($data);
    }
}
```

> $this->json($data, $status = 200, array $headers = [], $encodingOptions = 79)

Bir rest api için örnek;

```php
return $this->json(
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

Eğer kontrolör sınıfı metot parametreleri, konteyner içerisinde bir servis olarak kayıtlı ise otomatik olarak metot içerisine enjekte edilir.

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

```
namespace App\Controller;

use Obullo\Mvc\Controller;
use Obullo\Router\Router;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DefaultController extends Controller
{
    public function index(Request $request, Router $router, $name, $id) : Response
    {
        $html = $router->getMatchedRoute()
            ->getName();

        return $this->renderHtml($html);
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

use Obullo\Mvc\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DefaultController extends Controller
{
    public function index(Request $request, $name, $id) : Response
    {
        $html = $this->router->getMatchedRoute()
            ->getName();

        return $this->renderHtml($html);
    }
}
```