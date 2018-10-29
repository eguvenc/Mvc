
## Çoklu Dil Desteği

Çeviri işlemleri için `translator` servisi konfigüre edilmelidir. Çerçeve içerisinde çeviri paketi harici olarak kullanılır ve bunun için <a href="https://docs.zendframework.com/zend-i18n/">Zend-i18n</a> paketi tercih edilmiştir.

Paket mevcut değil ise aşağıdaki konsol komutu ile yüklenmelidir.

```bash
composer require zendframework/zend-i18n
```

### Çevirici servisi

Çevirici nesnesi diğer servisler gibi `index.php` dosyası içerisinden konfigüre edilir. 

```php
$container->setFactory('translator', 'Services\TranslatorFactory');
```

Çevirici servisi `Zend\I18n\Translator\Translator` nesnesine geri döner.

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

## Çeviri eklemek

Çeviriciye bir dosya eklemek için `addTranslationFilePattern()` metodunu kullanın:

```php
$translator = $container->get('translator');
$translator->addTranslationFilePattern($type, $baseDir, $pattern, $textDomain);
```

Argümanlar:

<table>
    <thead>
        <tr>
            <th>Argüman</th>
            <th>Açıklama</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>$type</td>
            <td>Kullanılacak format yükleyicinin adı; detaylar için bir sonraki bölüme bakın.</td>
        </tr>
        <tr>
            <td>$pattern</td>
            <td>$baseDir altındaki dosyaları bulmak için <b>sprintf()</b> ile biçimlendirilmiş dizedir. Yerleşim için yer tutucu olarak <b>%s</b> kullanın. Örnek olarak, çeviri dosyalarınız <b>/var/messages/$locale/messages.php</b> dizininde bulunuyorsa, deseniniz <b>/var/messages/%s/messages.php</b> olacaktır.</td>
        </tr>
        <tr>
            <td>$baseDir</td>
            <td>Çeviri dosyalarını içeren dizin.</td>
        </tr>
        <tr>
            <td>$textDomain</td>
            <td>Çeviriler için bir "kategori" adı. Bu ihmal edilirse, varsayılan olarak "default" olur. Çevirileri içeriğe göre ayırmak için metin alan adlarını kullanın.</td>
        </tr>
    </tbody>
</table>

Dosya başına bir yerel değeri saklarken, bu dosyaları bir desenle belirtmelisiniz. Bu, kodunuza dokunmadan dosya sistemine yeni çeviriler eklemenizi sağlar. Desenler <b>addTranslationFilePattern()</b> yöntemiyle eklenir:

```php
$translator = $container->get('translator');
$translator->addTranslationFilePattern($type, $baseDir, $pattern, $textDomain);
```

Örnek;

```php
$translator->setLocale("tr");
$translator->addTranslationFilePattern('PhpArray', ROOT, '/var/messages/%s/messages.php', 'system');
echo $translator->translate("Application Error", "system");

// Uygulama Hatası
```

`/var/messages/tr/messages.php` dosyasının içeriği

```php
# Error & Http response messages
# 
return [

    // -------------------------------------------------------------------
    // Application Errors
    // -------------------------------------------------------------------
    //
    'An error was encountered' => 'Bilinmeyen bir hata oluştu',
    'The page you are looking for could not be found' => 'Aradığınız sayfa bulunamadı',
    'Application Error' => 'Uygulama Hatası',
];
```

### Desteklenen formatlar

Çevirici aşağıdaki ana çeviri formatlarını desteklemektedir:

* PHP dizileri
* Gettext
* INI

Ayrıca, bir veya daha fazla `Zend\I18n\Translator\Loader\FileLoaderInterface` veya `Zend\I18n\Translator\Loader\RemoteLoaderInterface` uygulayarak ve yükleyicinizi Translator örneğinin oluşturulmuş eklenti yöneticisi ile kaydederek özel biçimleri kullanabilirsiniz.

### Yerel değişkeni ayarlamak

Varsayılan olarak çevirmen, <b>ext/intl</b> doğal `Locale` sınıfını kullanacaktır. Alternatif bir yerel ayarı açıkça ayarlamak isterseniz, bunu `setLocale()` yöntemi ile yapabilirsiniz.

Bir lokasyonda belirli bir mesaj tanımlayıcısı için çeviri olmadığında, mesaj tanımlayıcının kendisi varsayılan olarak geçerli kılınır. Alternatif olarak, bir geri dönüş çeviricisi (fallback locale) kullanmak istiyorsanız bunu `setFallbackLocale()` metodu ile yapabilirsiniz.

### Mesajların çevrilmesi

Çeviri mesajları çeviricinin `translate()` yöntemi çağrılarak gerçekleştirilir:

```php
$translator->translate($message, $textDomain, $locale);
```

Mesaj, tercüme edilecek mesaj tanımlayıcısıdır. Yükleyicide yoksa veya boşsa, orijinal mesaj kimliği döndürülür. Metin alanı parametresi, çevirme eklerken belirttiğiniz değerdir. Eğer atlanırsa, "varsayılan" metin alanı kullanılacaktır. Yerel ayar parametresi genellikle bu bağlamda kullanılmaz; varsayılan olarak yerel ayar çeviricide ayarlanan yerel ayardan alınır.

Çoğul metinleri çevirmek için, `translatePlural()` yöntemini kullanabilirsiniz. Bu yöntem `translate()` metodu gibi çalışır, tek fark tekil değerin yanında çoğul bir değer olması ve döndürülen çoğul formun ek bir tamsayı sayı almasıdır:

```php
$translator->translatePlural($singular, $plural, $number, $textDomain, $locale);
```

> Çoğul çeviriler, yalnızca temel biçimin çoğul mesajların çevirisini ve çoğul kural tanımlarını desteklemesi durumunda kullanılabilir.


### Dil katmanı

Uygulamanıza çoklu diller ile erişimin mümkün olabilmesi için `Translation` katmanı kullanılmalıdır. Dil katmanı dışarıdan bir dil değişkeni geldiği zaman `setLocale()` metodunu çalıştırır.

```
http://example.com/en
http://example.com/en/dummy
```

`config/routes.yaml` dosyanızı açın ve yönlendirme kurallarınızı aşağıdaki gibi değiştirin.

```
home: 
    path: /<locale:locale>
    handler: App\Controller\DefaultController::index

test:
    path: /<locale:locale>/test
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

Kontrolör dosyası

```php
namespace App\Controller;

use Obullo\Http\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DefaultController extends Controller
{
    public function test(Request $request) : Response
    {
        $locale = $this->translator->getLocale();

        return $this->renderHtml(
            'locale:'.sprintf('%02s', $locale)
        );
    }
}
```

### Önbellekleme

Canlı sunucu ortamında, çevirilerinizi önbelleğe almak anlamlıdır. Bu, her defasında tek tek formatları yüklemekten ve ayrıştırmanızdan kurtarmaz, aynı zamanda optimize edilmiş bir yükleme prosedürünü de garanti eder. Önbelleğe almayı etkinleştirmek için bir `Zend\Cache\Storage\Adapter` öğesini `$translator->setCache()` yöntemine iletin. Önbelleği devre dışı bırakmak için, yönteme boş bir değer iletmeniz yeterli olacaktır.