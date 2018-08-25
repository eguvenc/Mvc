
## Katmanlar

Katman sınıfı Psr15 standartlarını zorunlu tutar ve `Obullo/Stack` paketini kullanır.

> Http katmanları http çözümlemesinden önce `$request` yada `$response` nesnelerini etkilemek için kullanılırlar. Her bir katman uygulamayı sarar ve merkeze doğru ilerledikçe uygulamaya ulaşılır. Merkeze ulaşıldığında route eşleşmesi var ise eşleşme çıktısı, yok ise `Error` katmanı ile `$response` nesnesine dönülerek çıktı ekrana yazdırılır.

### Küresel katmanlar

Uygulamanıza bir katmanı küresel olarak eklemek için `index.php` yığın kuyruğu başlığına gidin.

```php
// -------------------------------------------------------------------
// Stack Queue
// -------------------------------------------------------------------
//
use Obullo\Mvc\Middleware\{
	HttpMethod
};
use App\Middleware\Translation;

$queue = [
    new Translation,
    new HttpMethod
];
$queue = $application->mergeQueue($queue);
```

> En yukarıda ilan edilen bir http katmanı ilk önce, en son ilan edilen ise en son çalışır.


### Dil katmanı

Eğer uygulamanıza çoklu dil desteği eklemek istiyorsanız bunu `Translation` katmanı ile yapabilirsiniz. Aşağıdaki örnek http adreslerine dil desteği ekleyelim.

```
http://example.com/en
http://example.com/en/dummy
```

App `config/routes.yaml` dosyanızı açın ve yönlendirme kurallarınızı aşağıdaki gibi değiştirin.

```
home: 
    path: /<locale:locale>
    handler: App\Controller\DefaultController::index

dummy:
    path: /<locale:locale>/dummy
    handler: App\Controller\DefaultController::dummy
```

`index.php` dosyanızı açın ve dil katmanını ekleyin.

```php
// -------------------------------------------------------------------
// Stack Queue
// -------------------------------------------------------------------
//
use Obullo\Mvc\Middleware\{
	HttpMethod
};
use App\Middleware\Translation;

$queue = [
    new Translation,
    new HttpMethod
];
$queue = $application->mergeQueue($queue);
```

Artık dil değişkeni kontrolör dosyanızdan erişilmeye hazır.

Http isteği

```
http://example.com/en/dummy
```

Kontrolör dosyası

```php
namespace App\Controller;

use Zend\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\RequestInterface as Request;

class DefaultController
{
    public function dummy(Request $request)
    {
        $locale = $this->translator->getLocale();

        return new HtmlResponse(
        	'locale:'.sprintf('%02s', $locale)
        );
    }
}
```

### Yönlendirme katmanları

Bir yönlendirmeye katman eklemek.

```
delete:
    path: /<int:id>
    handler: App\Controller\UserController::delete
    middleware: App\Middleware\Guest
```

Bir yönlendirme gurubuna katman eklemek.

```
users/:
    middleware: 
        - App\Middleware\Guest
    delete:
        path: /<int:id>
    	handler: App\Controller\UserController::delete
```

Yukarıda aşağıdaki bir url adresine yalnızca yetkili kullanıcıların erişebilmesi için bir katman ekledik.

```
http://example.com/users/delete/1
```

Yetkisiz kullanıcı (Guest) katmanı

```php
namespace App\Middleware;

use Psr\Http\{
    Message\ResponseInterface,
    Message\ServerRequestInterface as Request,
    Server\MiddlewareInterface,
    Server\RequestHandlerInterface as RequestHandler
};
use Obullo\Mvc\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Disallow unauthorized users.
 */
class Guest implements MiddlewareInterface,ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function process(Request $request, RequestHandler $handler) : ResponseInterface
    {
        $user = $this->getContainer()
        	->get('user');

        if ($user->guest()) {
        	$this->flash->error('This page requires authentication.');
			return new RedirectResponse('/login');
        }
        return $handler->handle($request);
    }
}
```

### Kontrolör katmanları

Aşağıdaki örnekte `save` ve `delete` metotlarına yetkisiz kullanıcıların erişmesi engelleniyor.

```php
namespace App\Controller;

use Zend\Db\TableGateway\TableGateway;

use Obullo\Mvc\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class UserController extends Controller
{
	public function __construct()
	{
		$this->middleware
			->add('Guest')
			->addMethod('save')
			->addMethod('delete');
	}

    public function index(Request $request) : Response
    {
        return $this->render('dashboard');
    }

    public function save()
    {
    	$users = new TableGateway('users', $this->adapter);
        $users->insert(['name' => 'username']);
    }

    public function delete(Request $request, int $id) : Response
    {
        $users = new TableGateway('users', $this->adapter);
        $users->delete(['id' => $id]);

		return $this->redirect('home');
    }
}
```


### Kontrolör katman yönetimi

#### $this->middleware->add(string $name);

Bir kontrolör sınıfına katman ekler.

#### $this->middleware->addArgument(string $name, mixed $arg);

Bir kontrolör sınıfına eklenen katmana ait `__construct` metoduna argümanlar ekler. Zincirleme metot yöntemini destekler.

#### $this->middleware->addMethod(string $name);

Bir kontrolör sınıfına eklenen katmanın eklenen metotlar için çalışmasını sağlar. Zincirleme metot yöntemini destekler.

#### $this->middleware->removeMethod(string $name);

Bir kontrolör sınıfına eklenen katmanın silinen metotlar dışındaki metotlar için çalışmasını sağlar. Zincirleme metot yöntemini destekler.

#### $this->middleware->getStack();

Middleware yığınına geri döner.

