
## Çoklu Dil Desteği

Çeviri işlemleri için `translator` servisi konfigüre edilmelidir. Çerçeve içerisinde çeviri paketi harici olarak kullanılır ve bunun için <a href="https://docs.zendframework.com/zend-i18n/">Zend-i18n</a> paketi tercih edilmiştir.

Paket mevcut değil ise aşağıdaki konsol komutu ile yüklenmelidir.

```bash
composer require zendframework/zend-i18n
```

### Çeviri servisi

Çeviri nesnesi diğer servisler gibi `index.php` dosyası içerisinden konfigüre edilir. 

```php
$container->setFactory('translator', 'Services\TranslatorFactory');
```

Çeviri servisi `Zend\I18n\Translator\Translator` nesnesine geri döner.

```php
namespace Services;

use Zend\I18n\Translator\Resources;
use Zend\I18n\Translator\Translator;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class TranslatorFactory implements FactoryInterface
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
        $container->setAlias('MvcTranslator', $requestedName); // Zend components support

        $config = $container->get('loader')
            ->load(ROOT, '/config/%s/framework.yaml', true)
            ->framework
            ->translator;
            
        $translator = new Translator;
        $translator->setLocale($config->default_locale);
        $translator->addTranslationFilePattern('PhpArray', ROOT, '/var/messages/%s/messages.php');

		return $translator;
    }
}
```