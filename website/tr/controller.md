
## Kontrolör

Mvc controller paketi http isteklerini kontrol ederek içerdiği metotlar ile istenen http yanıtlarına dönmenizi sağlar.

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

> render($nameOrModal, $data = null, $status = 200, $headers = []) : ResponseInterface


Render metodu aşağıdaki metot ile aynı işlevi görür.

```php
$this->view->render('welcome');
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

> renderHtml(string $html, $status = 200, $headers = []) : ResponseInterface


Yukarıdaki örneğin çıktısı:

```php
test
1
```

### Http yönlendirme

Http 302 durum kodu ile `Zend\Diactoros\Response\RedirectResponse` nesnesine geri döner.

```php
class DefaultController extends Controller
{
    public function index(Request $request, $name, $id) : Response
    {
        return $this->redirect('/another/page');
    }
}
```

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

> redirect($uriOrRouteName = null, $params = []) : ResponseInterface

### Json

Http json başlıkları `Zend\Diactoros\Response\JsonResponse` nesnesine geri döner.

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

> json($data, $status = 200, array $headers = [], $encodingOptions = 79)


### Bağımlılıklar 

Eğer konteyner içerisinde kullandığınız parametre adı ile bir servis kayıtlı ise bu servis otomatik olarak enjekte edilir.

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