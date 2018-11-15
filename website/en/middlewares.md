
## Katmanlar

Çerçeve içerisinde katman paketi harici olarak kullanılır ve katman işlemleri için <a href="https://www.php-fig.org/psr/psr-15/">Psr15</a> standartlarına göre tasarlanmış olan <a href="http://stack.obullo.com/">Obullo/Stack</a> paketi tercih edilmiştir.

> Http katmanları http çözümlemesinden önce `$request` yada `$response` nesnelerini tek başına yada her ikisini birden etkilemek için kullanılırlar. Uygulamaya eklenen her bir katman uygulamayı sarar ve merkeze doğru ilerledikçe uygulamaya ulaşılır. Merkeze ulaşıldığında `$response` nesnesine dönülerek çıktı ekrana yazdırılır. Böylece eklenen her bir katman sayesinde http çözümleme aşamaları kontrol altına alınmış olur.

Paket mevcut değil ise aşağıdaki konsol komutu ile yüklenmelidir.

```bash
composer require obullo/stack
```

### Küresel katmanlar

Uygulamanıza bir katmanı küresel olarak eklemek için `index.php` yığın kuyruğu başlığına gidin.

```php
// -------------------------------------------------------------------
// Stack Queue
// -------------------------------------------------------------------
//
$queue = [
    new App\Middleware\HttpMethod
];
$stack = new Stack;
$stack->setContainer($container);
foreach ($queue as $value) {
    $stack = $stack->withMiddleware($value);
}
```

> En yukarıda ilan edilen bir http katmanı ilk önce, en son ilan edilen ise en son çalışır.


### Http method katmanı

Bu katman uygulamanızda varsayılan olarak tanımlıdır ve yönlendirme paketi ile birlikte çalışır.

> Güvenlik unsurları nedeniyle bu katmanı kaldırmamanız tavsiye edilir. Katman kaldırıldığında erişime izin verilmesini istemediğiniz http metotları erişilebilir hale gelecektir.

```php
public function process(Request $request, RequestHandler $handler) : ResponseInterface
{
    $container = $this->getContainer();

    $events = $container->get('events');
    $router = $container->get('router');

    if ($router->hasMatch()) {

        $methods = $router->getMatchedRoute()
            ->getMethods();

        if (! in_array($request->getMethod(), $methods)) {
            
            $event = new Event;
            $event->setName('dissallowed.method');
            $event->setParam('methods', $methods);
            $message = $events->triggerEvent($event)->last();

            return $handler->process(
                new Error('405',$message,['Allow' => implode(', ', $methods)])
            );
        }
        $event = new Event;
        $event->setName('allowed.method');
        $event->setParam('methods', $methods);
        $events->triggerEvent($event);
    }
    return $handler->handle($request);
}
```

Katman içerisinde gerçekleşen olaylar `App\Event\HttpMethodListener` dinleyicisi tarafından izlenebilir.

```php
namespace App\Event;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\ListenerAggregateInterface;
use Obullo\Container\{
    ContainerAwareInterface,
    ContainerAwareTrait
};
class HttpMethodListener implements ListenerAggregateInterface,ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ListenerAggregateTrait;

    public function attach(EventManagerInterface $events, $priority = null)
    {
        $this->listeners[] = $events->attach('allowed.method', [$this, 'onAllowedMethod']);
        $this->listeners[] = $events->attach('disallowed.method', [$this, 'onNotAllowedMethod']);
    }

    public function onNotAllowedMethod(EventInterface $e) : string
    {    
        $methods = $e->getParam('methods');
        $message = sprintf(
            'Only Http %s Methods Allowed',
            implode(', ', $methods)
        );
        return $message;
    }

    public function onAllowedMethod(EventInterface $e)
    {
        // $methods = $e->getParam('methods');
    }
}
```

### Dil katmanı

Dil katmanı uygulamanıza çoklu dil desteği eklemeyi sağlar. Aşağıdaki gibi http istekleri aldığımızı varsayalım.

```
http://example.com/en
http://example.com/en/test
```

İstekleri çözümleyebilmek için `config/routes.yaml` dosyanızı açın ve yönlendirme kurallarınızı aşağıdaki gibi değiştirin.

```
home: 
    path: /<locale:locale>
    handler: App\Controller\DefaultController::index

test:
    path: /<locale:locale>/<str:name>
    handler: App\Controller\DefaultController::test
```

`index.php` dosyanızı açın ve dil katmanını ekleyin.

```php
// -------------------------------------------------------------------
// Stack Queue
// -------------------------------------------------------------------
//
$queue = [
    new App\Middleware\HttpMethod,
    new App\Middleware\Translation,
];
$stack = new Stack;
$stack->setContainer($container);
foreach ($queue as $value) {
    $stack = $stack->withMiddleware($value);
}
```

Http isteği

```
http://example.com/en/test
```

Kontrolör dosyası örneği

```php
namespace App\Controller;

use Obullo\Http\Controller;
use Zend\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DefaultController extends Controller
{
    public function test(Request $request, $name) : Response
    {
        $locale = $this->translator->getLocale();

        return new HtmlResponse(
            'locale:'.sprintf('%02s', $locale).'<br />name:'.$name
        );
    }
}
```

Çıktı:

```
locale: en
name: test
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
use Obullo\Container\{
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
        $container = $this->getContainer()
        $user = $container->get('user'); // custom user class which contains auth methods

        if ($user->guest()) {
            $container->get('flash')->error('This page requires authentication.');
            return new RedirectResponse('/login');
        }
        return $handler->handle($request);
    }
}
```

### Yerel katmanlar (Middleware sınıfı)

Katman yönetimi global olabileceği gibi kontrolör içerisinden yerel olarak da kontrol edilebilir. Aşağıdaki örnekte `save` ve `delete` metotlarına yetkisiz kullanıcıların erişmesi engelleniyor.

```php
namespace App\Controller;

use Zend\Db\TableGateway\TableGateway;
use Obullo\Http\Controller;
use Zend\Diactoros\Response\HtmlResponse;
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
        return new HtmlResponse($this->render('dashboard'));
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

        return new HtmlResponse($this->redirect('home'));
    }
}
```

> Middleware sınıfı `__construct()` metodu içerisinde çalıştırılmak için tasarlanmıştır. Bu tasarımda hedeflenen en tepede sınıf içerisindeki tüm metotları kontrol edebilmektir.


### Argümanlar

Bir katman kontrolör içerisinden eklenirken varsa argümanları `addArguments()` metodu ile eklenebilir.

```php
namespace App\Controller;

use Obullo\Http\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DefaultController extends Controller
{
    public function __construct()
    {
        $this->middleware->add('Error')
            ->addArguments(array('code' => 404, 'message' => '404 - Sayfa bulunamadı'));
    }
}
```

Argümanların gönderilebilmesi için eklenen argüman dizisi `associative array` biçiminde olmalıdır ve yukarıdaki örnekte olduğu gibi argüman isimleri ile `__construct()` metodu içerisindeki parametre isimleri eşleşmelidir.

```php
namespace App\Middleware;

class Error implements MiddlewareInterface, ContainerAwareInterface
{    
    /**
     * Constructor
     * 
     * @param integer $status
     * @param string  $message optional
     * @param array   $headers optional
     */
    public function __construct($code, $message = null, $headers = array()){}
}
```

> Yukarıdaki örneği çalıştırdığınızda `404 - Sayfa bulunamadı` hatası alıyor olmalısınız.

### Katman sınıfı referansı

#### $this->middleware->add(string $name);

Bir kontrolör sınıfına katman ekler.

#### $this->middleware->addArguments(array $args);

Bir kontrolör sınıfına eklenen katmana ait `__construct` metoduna argümanlar ekler. Yalnızca `associative` biçimindeki diziler desteklenir. Argüman isimleri ile `__construct()` metodu içerisindeki parametre isimleri eşleşmelidir.

#### $this->middleware->addMethod(string $name);

Bir kontrolör sınıfına eklenen katmanın eklenen metotlar için çalışmasını sağlar. Zincirleme metot yöntemini destekler.

#### $this->middleware->removeMethod(string $name);

Bir kontrolör sınıfına eklenen katmanın silinen metotlar dışındaki metotlar için çalışmasını sağlar. Zincirleme metot yöntemini destekler.

#### $this->middleware->getStack() : array;

Katman yığınına geri döner.