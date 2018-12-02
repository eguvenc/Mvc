
### Oturumlar

Session servisi uygulama içerisinde kullanıcı oturumlarını yönetmemizi sağlar. Çerçeve içerisinde session paketi harici olarak kullanılır ve bunun için <a href="https://docs.zendframework.com/zend-session/">Zend/Session</a> tercih edilmiştir.

Paket mevcut değil ise aşağıdaki konsol komutu ile yüklenmelidir.

```bash
composer require zendframework/zend-session
```

### Session servisi

Session nesnesi diğer servisler gibi `index.php` dosyası içerisinden konfigüre edilir. 

```php
$container->setFactory('session', 'Services\SessionFactory');
```

Session servisi `Zend\Session\SessionManager` nesnesine geri döner.

```php
namespace Services;

use Zend\Session\SessionManager;
use Zend\Session\Validator\HttpUserAgent;
use Zend\Session\Storage\SessionArrayStorage;

class SessionFactory implements FactoryInterface
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
        $application = $container->get('config')->application;

		$manager = new SessionManager();
		$manager->setStorage(new SessionArrayStorage());
		$manager->getValidatorChain()
			->attach('session.validate', [new HttpUserAgent(), 'isValid']);
		$manager->setName($application->session->name);
		
		return $manager;
	}
}
```

### Oturumları başlatmak

Uygulama içinde oturumlar `BundleListener` sınıfı içerisinde `$session` servisi ile başlatılır.

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
        $this->listeners[] = $events->attach('bundle.bootstrap', [$this, 'onBootstrap']);
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
        $session = $container->get('session');
        $session->start();
    }
}
```

Eğer uygulamanız session sınıfını kullanmayı gerektirmiyorsa oturumları başlatmayı aşağıdaki gibi kapatın.

```php
// $session = $container->get('session');
// $session->start();
```

Bu işlemden sonra artık oturumları kontrolör `__construct()` metodu içerisinde yerel olarak başlatabilirsiniz.

```php
namespace App\Controller;

use Obullo\Http\Controller;
use Zend\Session\SessionManager as Session;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DefaultController extends Controller
{
	public function __construct(Session $session)
	{
		$session->start();
	}
}
```

`$session->start()` komutu php `session_start()` komutu ile eşdeğerdir.

### Oturum değerleri

Oturumlar başlatıldıktan sonra `$_SESSION` değişkenine veriler atanabilir.

Veri kaydetmek.

```php
$_SESSION['test'] = 'foo'; 
```

Veri okumak.

```php
echo $_SESSION['test']; // foo;
```


### Flash messenger

Flash messenger sınıfı uygulama işlemlerinden sonra kullanıcıya gösterilmesi amaçlanan bilgi mesajlarını `$_SESSION` içerisinde geçici olarak tutarak bir sonraki http isteğinde bu mesajın kullanıcıya gösterilmesini sağlar.

### Flash servisi

Flash nesnesi diğer servisler gibi `index.php` dosyası içerisinden konfigüre edilir. 

```php
$container->setFactory('flash', 'Services\FlashMessengerFactory');
```

Flash servisi `Obullo\Session\FlashMessenger` nesnesine geri döner.

```php
namespace Services;

use Obullo\Session\FlashMessenger;

class FlashMessengerFactory implements FactoryInterface
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
		$params = [
			'view' => array(
				'message_open_format'      => '<div%s><button type="button" class="close" 
				data-dismiss="alert" aria-hidden="true">&times;</button><ul><li>',
				'message_separator_string' => '</li><li>',
				'message_close_string'     => '</li></ul></div>',
			)
		];
		$flash = new FlashMessenger($params);
		$flash->setEscaper($container->get('escaper'));
		return $flash;
	}
}
```

### FlashMessenger sınıfı metotları

#### $flash->setMessageOpenFormat(string $messageOpenFormat);

Mesaj şablonunun başlangıç etiketini belirler.

```php
$flash->setMessageOpenFormat("<div%s><ul><li>");
```

#### $flash->setMessageSeparatorString(string $messageSeparatorString);

Mesaj şablonunun tekrarlama etiketini belirler.

```php
$flash->setMessageSeparatorString("</li><li>");
```

#### $flash->setMessageCloseString(string $messageCloseString);

Mesaj şablonunun kapatma etiketlerini belirler.

```php
$flash->setMessageCloseString("</li></ul></div>");
```

#### $flash->setEscaper($escaper);

Html escape metodu için nesne atar.

#### $flash->flushMessages(array $classes = [], $autoEscape = null) : array;

Tüm flash mesajlarını array formatında çıktılar. İlk parametre extra css class isimlerini gönderilmesini sağlar. İkinci parametre ise html escape özelliğini çalıştırır.

#### $flash->success($message);

Başarılı mesaj şablonu ile mesaj yaratır.

#### $flash->error($message);

Hata mesaj şablonu ile mesaj yaratır.

#### $flash->info($message);

Info mesaj şablonu ile mesaj yaratır.

#### $flash->warning($message);

Warning mesaj şablonu ile mesaj yaratır.

#### $flash->get($key) : string
	
Tek bir mesajı almayı sağlar.

```php
$flash->get('success');
```

#### $flash->keep($key);

Mesajın bir sonraki http isteğinde de gözükebilir olmasını sağlar.

### Mesajlar

Bir flaş mesajı göstermek oldukça kolaydır bir durum metodu seçin ve içine mesajınızı girin.

<table>
	<thead>
		<tr>
			<th>Durum</th>
			<th>Açıklama</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>success</td>
			<td>Başarılı işlemlerde kullanılır.</td>
		</tr>
		<tr>
			<td>error</td>
			<td>İşlemlerde bir hata olduğunda kullanılır.</td>
		</tr>
		<tr>
			<td>warning</td>
			<td>Uyarı amaçlı mesajları göstermek amacıyla kullanılır.</td>
		</tr>
		<tr>
			<td>info</td>
			<td>Bilgi amaçlı mesajları göstermek amacıyla kullanılır.</td>
		</tr>
	</tbody>
</table>


```php
$this->flash->success('Form saved successfully.');
```

Çıktı

```php
print_r($this->flash->flushMessages());

/*
Array
(
	[0] => <div class="success">
	<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
	<ul>
		<li>Form saved successfully</li>
		<li></li>
	</ul>
	</div>
)
*/
```

Birden fazla flaş mesajı göstermek için birden fazla metot kullanmanız gerekir.

```php
$this->flash->success('Form saved successfully.');
$this->flash->error('Error.');
$this->flash->warning('Something went wrong.');
$this->flash->info('Email has been sent to your mail address.');
```

Aşağıdaki kodu html sayfanıza yerleştirin.


```php
foreach ($this->flash->flushMessages() as $message) {
	echo $message;
};
```

```php
/*
Çıktı

<div class="success">..Form saved successfully.</div>
<div class="error">..Error.</div>
<div class="warning">..Something went wrong.</div>
<div class="info">..Email has been sent to your mail address.</div>
*/
```

### Mesaj kalıcılığı

Eğer bir flaş mesajının bir sonraki http isteğinde de kalıcı olmasını istiyorsanız bu metodu kullanmanız gerekir.

```php
$this->flash->keep('warning');
$this->flash->keep('success');
```

### Mesaj durumu

Geçerli bir flaş mesajına ait durum değerini <kbd>status</kbd> anahtarı ile alabilirsiniz.

```php
$this->flash->success();
```

Bir sonraki sayfa için.

```php
$this->flash->get('status');  // success
```