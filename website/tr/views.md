
## Görünümler

Görünüm sınıfı uygulama içerisindeki html arayüzünü kontrol eden metotları içerir. Çerçeve içerisinde view paketi harici olarak kullanılır ve bunun için <a href="http://platesphp.com/v3/templates/">League\Plates</a> paketi tercih edilmiştir.

### View servisi

View nesnesi diğer servisler gibi `index.php` dosyası içerisinde konfigüre edilir. 

```php
$container = new ServiceManager;
$container->setFactory('view', 'Services\ViewPlatesFactory');
```

View servisi `Obullo\View\PlatesPhp` nesnesine geri döner ve bu nesne içerisindeki `render` metodu `League\Plates\Template\Template` sınıfı render metodunu çağırır.

```php
namespace Services;

use Obullo\View\Helper;
use Obullo\View\PlatesPhp;
use League\Plates\Engine;
use League\Plates\Extension\Asset;

class ViewPlatesFactory implements FactoryInterface
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
        $engine = new Engine(ROOT.'/'.APP.'/View');
        $engine->setFileExtension('phtml');
        $engine->addFolder('templates', ROOT.'/templates');
        $engine->loadExtension(new Asset(ROOT.'/public/'.strtolower(APP).'/', false));

        /**
         * View helpers
         */
        $engine->registerFunction('url', new Helper\Url($container));
        $engine->registerFunction('escapeHtml', new Helper\EscapeHtml);
        $engine->registerFunction('escapeHtmlAttr', new Helper\EscapeHtmlAttr);
        $engine->registerFunction('escapeCss', new Helper\EscapeCss);
        $engine->registerFunction('escapeJs', new Helper\EscapeJs);
        $engine->registerFunction('escapeUrl', new Helper\EscapeUrl);

        $template = new PlatesPhp($engine);
        $template->setContainer($container);

        return $template;
    }
}
```