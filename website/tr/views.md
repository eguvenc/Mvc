
## Görünümler

Görünüm sınıfı uygulama içerisindeki html arayüzünü kontrol eden metotları içerir. Çerçeve içerisinde view paketi harici olarak kullanılır ve bunun için <a href="http://platesphp.com/v3/templates/">PlatesPhp</a> paketi tercih edilmiştir.

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
        $engine->registerFunction('escapeUrl', new Helper\EscapeUrl);

        $template = new PlatesPhp($engine);
        $template->setContainer($container);

        return $template;
    }
}
```

View fonksiyonlarına geçerli view dosyası içerisinden `$view->method()` yerine `$this->method()` yöntemi ile ulaşılabilir.
 

### Kontrolör fonksiyonları

#### $this->render($name, $data = null, $status = 200, $headers = []) : ResponseInterface

Response nesnesi içerisine view nesnesi render metodunu kullanarak html çıktısını ekler.

```php
$this->render('welcome');
```

Render metodu aslında arka planda konteyner içerisinden view servisine bağlanarak aşağıdaki işlevi çağırır.

```php
return new HtmlResponse($this->view->render('welcome'));
```

#### $this->renderHtml(string $html, $status = 200, $headers = []) : ResponseInterface

RenderHtml metodu ise view nesnesi kullanmadan Response nesnesine içerisine html çıktısını direkt ekler.

```php
$html = $name.'<br />';

return $this->renderHtml($html);
```

Yukarıdaki örneğin çıktısı:

```php
test<br />
```

Render html metodu aslında arka planda aşağıdaki işlevi çağırır.

```php
return new HtmlResponse('test');
```

### Layout tanımlamak

Görünüm içinde herhangi bir yerde layout metodu çağırılabilir. Tipik olarak dosyanın en üstünde çağırılır.

```php
<?php $this->layout('template') ?>

<h1>User Profile</h1>
<p>Hello, <?=$this->escape($name)?></p>
This function also works with folders:

<?php $this->layout('shared::template') ?>
```

### Veri atamak

Bir görünüme veri atamak için layout fonksiyonu ikinci parametresi kullanılır. Böylece bu veriler görünüm dosyası içerisinde yerel olarak erişilebilir hale gelir.

```php
<?php $this->layout('template', ['title' => 'User Profile']) ?>
```

### İçeriğe erişmek

Bir layout içerisinden işlenmiş bir görünüme ulaşmak için `section()` metodu varsayılan `content` parametresi ile kullanılır.

```php
<html>
<head>
    <title><?=$this->escape($title)?></title>
</head>
<body>

<?=$this->section('content')?>

</body>
</html>
```

### Bölümler oluşturmak

Bölümler (sections) oluşturmak için `start()` metodu kullanılır. Bölümü kapatmak için ise `stop()` fonksiyonu ile bölüm kapatılmalıdır.

```php
<?php $this->start('welcome') ?>

    <h1>Welcome!</h1>
    <p>Hello <?=$this->escape($name)?></p>

<?php $this->stop() ?>
```

### Yardımcı Fonksiyonlar

#### $this->asset(string $url);

Uygulama kaynaklarına ait url adreslerini yaratmayı sağlar.

```php
<html>
<head>
    <title>Asset Extension Example</title>
    <link rel="stylesheet" href="<?=$this->asset('/css/bootstrap.css')?>" />
</head>

<body>

<img src="<?=$this->asset('/images/logo.png')?>">

</body>
</html>
```

#### $this->url(string $url, $params = []);

Uygulamanızadaki route paketine tanımlanmış route isimleri ile uyumlu çalışarak güvenli url adresleri üretmenizi sağlar. Aşağıdaki gibi route konfigürasyonunuz olduğunu varsayalım.

```yaml

# name:
#    path: /
#    handler: App\Controller\DefaultController::index

user/:
    update:
        path: /update/<int:id>
        handler: App\Controller\UserController::update
    delete:
        path: /delete/<int:id>
        handler: App\Controller\UserController::delete
```

Url fonksiyonunu çağırdığınızda url adresleri route konfigürasyonunda belirlenmiş türlere göre üretilir.

```php
$this->url('user/update', ['id' => 5]);  // Çıktı:  /user/update/5
$this->url('user/delete', ['id' => 5]);  // Çıktı:  /user/delete/5
```

Bu fonksiyon arka planda router paketi `generate` fonksiyonunu çağırır.

```php
$router->generate($url $params = []);
```

#### $this->escapeHtml($value);

Dinamik oluşturulan html etiketlerindeki olası tehlikeli karakterlerden kaçış için kullanılır.

```php
$this->escapeHtml($row['blog_comment']);
$escaper = new Zend\Escaper\Escaper('utf-8');
```

#### $this->escapeHtmlAttr($value);

Dinamik oluşturulan html niteliklerindeki olası tehlikeli karakterlerden kaçış için kullanılır.

```php
<span title=<?php echo $this->escapeHtmlAttr($output) ?>>
    What framework are you using?
</span>
```

#### $this->escapeUrl($value);

```html
<a href="http://example.com/?name=<?php echo $this->escapeUrl($input); ?>">Click here!</a>
```

Daha fazla detay için <a href="http://platesphp.com/v3/extensions/asset/">Platesphp.com</a> adresini ziyaret edebilirsiniz.