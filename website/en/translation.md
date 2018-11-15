
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
$translator->setLocale('tr');
$translator->addTranslationFilePattern('PhpArray', ROOT, '/var/messages/%s/messages.php', 'system');
echo $translator->translate('Application Error', 'system');

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

Örnek bir xml yükleyicisi.

```php
$translator->getPluginManager()->setService('XmlFile', new \App\MyXmlLoader);
$translator->addTranslationFilePattern('XmlFile', ROOT, '/var/messages/%s/messages.xml');
```

### Yerel değişkeni ayarlamak

Varsayılan olarak çevirmen, <b>ext/intl</b> doğal `Locale` sınıfını kullanacaktır. Alternatif bir yerel ayarı açıkça ayarlamak isterseniz, bunu `$translator->setLocale()` yöntemi ile yapabilirsiniz.

Bir lokasyonda belirli bir mesaj tanımlayıcısı için çeviri olmadığında, mesaj tanımlayıcının kendisi varsayılan olarak geçerli kılınır. Alternatif olarak, bir geri dönüş çeviricisi (fallback locale) kullanmak istiyorsanız bunu `$translator->setFallbackLocale()` metodu ile yapabilirsiniz.

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
            'locale variable:'.sprintf('%02s', $locale)
        );
    }
}
```

Çıktı

```
locale variable: en
```

### Önbellekleme

Canlı sunucu ortamında, çevirilerinizi önbelleğe almak anlamlıdır. Bu, her defasında tek tek formatları yüklemekten ve ayrıştırmanızdan kurtarmaz, aynı zamanda optimize edilmiş bir yükleme prosedürünü de garanti eder. Önbelleğe almayı etkinleştirmek için bir `Zend\Cache\Storage\Adapter` öğesini `$translator->setCache()` yöntemine iletin. Önbelleği devre dışı bırakmak için, yönteme boş bir değer iletmeniz yeterli olacaktır.

## Görünüm yardımcıları

Tarih ve para birimleri gibi formatları farklı dillerde görünüm dosyalarınız içerisinde gösterebilmek için `Services\ViewPlatesFactory` servisi içerisinde bu fonksiyonları tanımlanmanız gerekir.

> Zend I18n kütüphanesi View fonksiyonlarını kullanabilmek için Php <b>ext/intl</b> kütüphanesinin yüklü olması gerekir.


### CurrencyFormat Helper

Konfigürasyon:

```php
$engine->registerFunction('currencyFormat', new \Zend\I18n\View\Helper\CurrencyFormat);
```

Örnekler:

```php
echo $this->currencyFormat(1234.56, 'TRY', null, 'tr_TR');
// Çıktı: ₺1.234,56

echo $this->currencyFormat(1234.56, 'USD', null, 'en_US');
// Çıktı: "$1,234.56"

echo $this->currencyFormat(1234.56, 'EUR', null, 'de_DE');
// Çıktı: "1.234,56 €"

echo $this->currencyFormat(1234.56, 'USD', true, 'en_US');
// Çıktı: "$1,234.56"

echo $this->currencyFormat(1234.56, 'USD', false, 'en_US');
// Çıktı: "$1,235"

echo $this->currencyFormat(12345678.90, 'EUR', true, 'de_DE', '#0.# kg');
// Çıktı: "12345678,90 kg"

echo $this->currencyFormat(12345678.90, 'EUR', false, 'de_DE', '#0.# kg');
// Çıktı: "12345679 kg"
```

`$currencyCode` ve `$locale` seçenekleri biçimlendirmeden önce ayarlanabilir ve fonksiyon her kullanıldığında uygulanması sağlanabilir.

```php
// Görünüm dosyaları içerisinde

$this->plugin('currencyformat')->setCurrencyCode('USD')->setLocale('en_US');

echo $this->currencyFormat(1234.56);
// This returns: "$1,234.56"

echo $this->currencyFormat(5678.90);
// This returns: "$5,678.90"
```

Ondalık sayıları kapatıp açmak:

```php
// Görünüm dosyaları içerisinde

$this->plugin('currencyFormat')->setShouldShowDecimals(false);

echo $this->currencyFormat(1234.56, 'USD', null, 'en_US');

// Çıktı: "$1,235"
```

Para birimi şablonunu ayarlamak:

```php
// Görünüm dosyaları içerisinde

$this->plugin('currencyFormat')->setCurrencyPattern('#0.# kg');

echo $this->currencyFormat(12345678.90, 'EUR', null, 'de_DE');

// Çıktı: "12345678,90 kg"
```

<table>
    <thead>
        <tr>
            <th>Argüman</th>
            <th>Açıklama</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>$number</td>
            <td>Sayısal para birimi değeri.</td>
        </tr>
        <tr>
            <td>$currencyCode</td>
            <td>Kullanılacak para birimini gösteren 3 harfli ISO 4217 para birimi kodu. Eğer girilmedi ise, varsayılan değeri kullanacaktır (varsayılan olarak boş).</td>
        </tr>
        <tr>
            <td>$showDecimals</td>
            <td>Boolean false, ondalığın temsil edilmemesi gerektiğini gösterir. Eğer girilmedi ise, varsayılan değeri kullanacaktır (varsayılan olarak true).</td>
        </tr>
        <tr>
            <td>$locale</td>
            <td>Para biriminin biçimlendirileceği yer (yerel ad, ör. En_US). Eğer girilmedi ise, varsayılan yerel ayarı kullanacaktır (varsayılan değer Locale :: getDefault () değeridir).</td>
        </tr>
        <tr>
            <td>$pattern</td>
            <td>Biçimlendiricinin kullanması gereken desen dizesi. Eğer girilmedi ise, varsayılan değeri kullanır (varsayılan olarak boş).</td>
        </tr>
    </tbody>
</table>


### DateFormat Helper

DateFormat fonksiyonu, yerelleştirilmiş tarih/saat değerlerinin oluşturulmasını basitleştirmek için kullanılabilir.

Konfigürasyon:

```php
$engine->registerFunction('dateFormat', new \Zend\I18n\View\Helper\DateFormat);
```

Örnekler:

```php
// Görünüm dosyaları içerisinde

// Tarih ve saat
echo $this->dateFormat(
    new DateTime(),
    IntlDateFormatter::MEDIUM, // date
    IntlDateFormatter::MEDIUM, // time
    "en_US"
);
// Çıktı: "Jul 2, 2012 6:44:03 PM"
```

Sadece tarih

```php
echo $this->dateFormat(
    new DateTime(),
    IntlDateFormatter::LONG, // date
    IntlDateFormatter::NONE, // time
    "en_US"
);
// Çıktı: "July 2, 2012"
```

Sadece saat

```php
echo $this->dateFormat(
    new DateTime(),
    IntlDateFormatter::NONE,  // date
    IntlDateFormatter::SHORT, // time
    "en_US"
);
// Çıktı: "6:44 PM"
```

<table>
    <thead>
        <tr>
            <th>Argüman</th>
            <th>Açıklama</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>$date</td>
            <td>Biçimlendirilecek değer. Bu bir DateTime örneği, bir Unix zaman damgası değerini temsil eden bir tam sayı veya localtime() tarafından döndürülen biçimde bir dizi olabilir.</td>
        </tr>
        <tr>
            <td>$dateType</td>
            <td>Kullanılacak tarih türü (none, short, medium, long, full). Bu IntlDateFormatter sabitlerinden biridir. IntlDateFormatter için varsayılan değer: IntlDateFormatter::NONE.</td>
        </tr>
        <tr>
            <td>$timeType</td>
            <td>Kullanılacak saat türü (none, short, medium, long, full). Bu IntlDateFormatter sabitlerinden biridir. IntlDateFormatter için varsayılan değer: IntlDateFormatter::NONE.</td>
        </tr>
        <tr>
            <td>$locale</td>
            <td>Tarihin biçimlendirileceği yer (yerel ad, ör. En_US). Girilmedi ise, varsayılan yerel ayarı kullanacaktır (Locale :: getDefault () öğesinin dönüş değeri).</td>
        </tr>
    </tbody>
</table>

#### Dışarıya açık metotlar

`$locale` seçeneği, `setLocale()` yöntemiyle biçimlendirmeden önce ayarlanabilir ve fonskiyon her kullanıldığında bu zaman uygulanır.

Varsayılan olarak, biçimlendirme sırasında sistemin varsayılan saat dilimi kullanılır. Bu, bir DateTime nesnesinde ayarlanabilecek herhangi bir zaman dilimini geçersiz kılar. Biçimlendirme sırasında saat dilimini değiştirmek için `setTimezone()` yöntemini kullanın.

```php
// Görünüm dosyası içerisinde

$this->plugin('dateFormat')
    ->setTimezone('America/New_York')
    ->setLocale('en_US');

echo $this->dateFormat(new DateTime(), IntlDateFormatter::MEDIUM);  // "Jul 2, 2012"
echo $this->dateFormat(new DateTime(), IntlDateFormatter::SHORT);   // "7/2/12"
```

### NumberFormat Helper

NumberFormat fonksiyonu, yerel ayara özgü sayı ve/veya yüzde dizelerinin oluşturulmasını basitleştirmek için kullanılabilir.

Konfigürasyon:

```php
$engine->registerFunction('numberFormat', new \Zend\I18n\View\Helper\NumberFormat);
```

Örnekler:

Ondalık biçimlendirme örneği:

```php
// Görünüm dosyası içinde

echo $this->numberFormat(
    1234567.891234567890000,
    NumberFormatter::DECIMAL,
    NumberFormatter::TYPE_DEFAULT,
    'de_DE'
);
// Çıktı: "1.234.567,891"
```

Yüzde biçimlendirme örneği:

```php
echo $this->numberFormat(
    0.80,
    NumberFormatter::PERCENT,
    NumberFormatter::TYPE_DEFAULT,
    'en_US'
);
// Çıktı: "80%"
```

Bilimsel gösterim formatı örneği:

```php
echo $this->numberFormat(
    0.00123456789,
    NumberFormatter::SCIENTIFIC,
    NumberFormatter::TYPE_DEFAULT,
    'fr_FR'
);
// Çıktı: "1,23456789E-3"
```

<table>
    <thead>
        <tr>
            <th>Argüman</th>
            <th>Açıklama</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>$number</td>
            <td>Numara formatı.</td>
        </tr>
        <tr>
            <td>$formatStyle</td>
            <td>Numara Format sitillerinden biri: NumberFormatter::DECIMAL, NumberFormatter::CURRENCY, gibi.</td>
        </tr>
        <tr>
            <td>$formatType</td>
            <td>Numara Format türleri: NumberFormatter::TYPE_DEFAULT (temel sayısal), NumberFormatter::TYPE_CURRENCY, gibi.</td>
        </tr>
        <tr>
            <td>$locale</td>
            <td>Geçerli bir yerel değişken.</td>
        </tr>
        <tr>
            <td>$decimals</td>
            <td>Gösterilecek ondalık noktasının ötesindeki basamak sayısı.</td>
        </tr>
        <tr>
            <td>$textAttributes</td>
            <td>Numarayla kullanılacak metin nitelikleri (ör., pozitif/negatif sayılar için önek ve/veya sonek, para birimi kodu): NumberFormatter::POSITIVE_PREFIX, NumberFormatter::NEGATIVE_PREFIX, gibi. </td>
        </tr>
    </tbody>
</table>

#### Dışarıya açık metotlar

Biçimlendirmeden önce `$formatStyle`, `$formatType`, `$locale` ve `$textAttributes` seçeneklerinin her biri öncelikli ayarlanabilir ve fonksiyon her çağırıldığında uygulanır.

```php
// Görünüm dosyası içinde

$this->plugin("numberFormat")
            ->setFormatStyle(NumberFormatter::PERCENT)
            ->setFormatType(NumberFormatter::TYPE_DOUBLE)
            ->setLocale("en_US")
            ->setTextAttributes([
                NumberFormatter::POSITIVE_PREFIX => '^ ',
                NumberFormatter::NEGATIVE_PREFIX => 'v ',
            ]);

echo $this->numberFormat(0.56);   // "^ 56%"
echo $this->numberFormat(-0.90);  // "v 90%"
```

### Translate Helper

`Zend\I18n\Translator\Translator` sınıfı için  sınıfı için yardımcı fonksiyon görevi görür.

Konfigürasyon:

```php
$engine->registerFunction('translate', (new Translate)->setTranslator($container->get('translator')));
```

Örnekler:

```php
// Görünüm dosyarı içerisinde

echo $this->translate("Some translated text.");
echo $this->translate("Translated text from a custom text domain.", "customDomain");
echo sprintf($this->translate("The current time is %s."), $currentTime);
echo $this->translate("Translate in a specific locale", "default", "de");
```

<table>
    <thead>
        <tr>
            <th>Argüman</th>
            <th>Açıklama</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>$message</td>
            <td>Çevirilecek metin.</td>
        </tr>
        <tr>
            <td>$textDomain</td>
            <td>Çevirinin metin alanı/bağlamı; varsayılan "default".</td>
        </tr>
        <tr>
            <td>$locale</td>
            <td>Metinin çevrilmesi gereken yer (yerel adı, ör. en_US). Eğer girilmedi ise, varsayılan yerel ayarı kullanacaktır (Locale::getDefault() öğesinin dönüş değeri).</td>
        </tr>

    </tbody>
</table>

#### Gettext aracı

`xgettext` aracı, translate fonksiyonu içeren PHP kaynak dosyalarından `*.po` dosyalarını derlemek için kullanılabilir.


```bash
$ xgettext --language=php --add-location --keyword=translate --keyword=translatePlural:1,2 my-view-file.phtml
```

Daha detaylı bilgi için <a href="https://en.wikipedia.org/wiki/Gettext">Gettext Wikipedia</a> sayfasınız ziyaret edebilirsiniz.


#### Dışarıya açık metotlar

Bir `Zend\I18n\Translator\Translator` ve bir varsayılan metin alanı ayarlamak için genel yöntemler <a href="https://docs.zendframework.com/zend-i18n/view-helpers/#abstract-translator-helper">AbstractTranslatorHelper</a> öğesinden devralınır.


> Çeviri fonksiyonları hakkında data geniş bilgiye <a hreg="https://docs.zendframework.com/zend-i18n/view-helpers/">https://docs.zendframework.com/zend-i18n/view-helpers/</a> adresinden ulaşabilirsiniz.