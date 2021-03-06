
## Servis Yönetimi

Servis yönetim sınıfı uygulamanızda `instance` yönetimi üstlenir. Paylaşımlı nesneler bu sınıf içerisine servis olarak konfigüre edilir ve ihtiyaç olduğunda yeniden ilan edilmeye gerek kalmadan kullanılabilirler.
Çerçeve içerisinde bu paket harici olarak kullanılır ve bunun için <a href="https://docs.zendframework.com/zend-servicemanager/">Zend/ServiceManager</a> paketi tercih edilmiştir.

Paket mevcut değil ise aşağıdaki konsol komutu ile yüklenmelidir.

```bash
composer require zendframework/zend-servicemanager
```

### Servisler

Tüm servisler `index.php` dosyası içerisinden konfigüre edilir. Servisler `setFactory()` metodu ile uygulamaya tanımlanmış olur ve çağırıldıkları zaman uygulamaya dahil edilirler.

```php
$container = new ServiceManager;
$container->setFactory('loader', 'Services\LoaderFactory');
$container->setFactory('session', 'Services\SessionFactory');
```

Her servis `Zend\ServiceManager\Factory\FactoryInterface` arayüzünü uygulamak zorundadır.

```php
namespace Services;

use Zend\Session\SessionManager;
use Zend\Session\Validator\HttpUserAgent;
use Zend\Session\Storage\SessionArrayStorage;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class SessionFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $framework = $container->get('loader')
            ->load(ROOT, '/config/%s/framework.yaml', true)
            ->framework;

        $manager = new SessionManager();
        $manager->setStorage(new SessionArrayStorage());
        $manager->getValidatorChain()
            ->attach('session.validate', [new HttpUserAgent(), 'isValid']);

        if (false == defined('STDIN')) {
            $manager->setName($framework->session->name);
        }
        return $manager;
    }
}
```

### Servislere erişim

```php
$container->get('session');  // Zend\Session\SessionManager
```

### Kontroller içerisinden erişim

Proxy yöntemi sayesinde servisler kontrolör dosyası içerisinden direkt çağırılabilirler.

```php
$this->session;  // Zend\Session\SessionManager
```

> Servisler hakkında detaylı dökümentasyona  <a href="https://docs.zendframework.com/zend-servicemanager/">https://docs.zendframework.com/zend-servicemanager/</a> bağlantısından ulaşabilirsiniz.