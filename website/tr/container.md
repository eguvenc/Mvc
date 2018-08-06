
## Konteyner

Mvc konteyner paketi `Zend Servis Yöneticisini` kullanır. <a href="https://docs.zendframework.com/zend-servicemanager/">Zend Service Manager</a> a buradan ulaşabilirisniz.

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