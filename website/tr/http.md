
## Http

Request sınıfı sunucuya gelen http isteklerini yönetir ve <a href="https://www.php-fig.org/psr/psr-7/">Psr7</a> standartlarını zorunlu tutar. Çerçeve içerisinde request paketi harici olarak kullanılır ve bunun için <a href="https://docs.zendframework.com/zend-diactoros/">Zend/Diactoros</a> paketi tercih edilmiştir.

Paket mevcut değil ise aşağıdaki konsol komutu ile yüklenmelidir.

```bash
composer require zendframework/zend-diactoros
```

### Request servisi

Request nesnesi diğer servisler gibi `index.php` dosyası içerisinden konfigüre edilir. 

```php
$container = new ServiceManager;
$container->setFactory('request', 'Services\RequestFactory');
```

Request servisi `Obullo\Http\ServerRequest` nesnesine geri döner ve bu nesne `Zend\Diactoros\ServerRequest` nesnesine genişler.

```php
namespace Services;

use Obullo\Http\ServerRequest;

class RequestFactory implements FactoryInterface
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
        $server = normalizeServer($_SERVER,
            is_callable('apache_request_headers') ? 'apache_request_headers' : null
        );
        $files   = normalizeUploadedFiles($_FILES);
        $headers = marshalHeadersFromSapi($server);

        if (null === $_COOKIE && array_key_exists('cookie', $headers)) {
            $cookies = parseCookieHeader($headers['cookie']);
        }
        return new ServerRequest(
            $server,
            $files,
            marshalUriFromSapi($server, $headers),
            marshalMethodFromSapi($server),
            'php://input',
            $headers,
            $_COOKIE,
            $_GET,
            $_POST,
            marshalProtocolVersionFromSapi($server)
        );
    }
}
```

Request servisi sunucunuzda kurulu http servisine özgü değişiklikleri gerçekleştirmeyi olanaklı kılar.

> Http request nesnesi Psr7 metotlarını içerir. Bu metotlar hakkında detaylı bilgiye <a href="https://www.php-fig.org/psr/psr-7/">https://www.php-fig.org/psr/psr-7</a> adresinden ulaşabilirsiniz.

### Psr7 http başlıkları

#### $request->getHeaders()

Tüm http sunucu başlıklarına geri döner.

```php
$headers = $request->getHeaders();
print_r($headers);
```

```php
Array
(
    [Host] => localhost
    [User-Agent] => Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:38.0) Gecko/20100101 Firefox/38.0
    [Accept] => text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
    [Accept-Language] => en-US,en;q=0.5
    [Accept-Encoding] => gzip, deflate
    ...
)
```

#### $request->hasHeader($key)

İlgili başlık varsa `true` aksi durumda false değerine geri döner.

#### $request->getHeader($key)

Seçilen http sunucu başlığına geri döner.

```php
echo $request->getHeader('host'); // localhost
echo $request->getHeader('content-type'); // gzip, deflate
```

#### $request->getHeaderLine($name);

Tek başlığa ait virgül ile ayrılmış değerlere geri döner.

#### $request->withHeader($header, $value);

Girilen başlığa ait değeri değiştirir yada yeni başlıkla ile beraber request nesnesine geri döner.

#### $request->withAddedHeader($header, $value)

Girilen başlıklar üzerinde yeni başlık ekleyerek request nesnesine geri döner.

#### $request->withoutHeader($header)

Girilen başlık olmadan request nesnesine geri döner.


### Psr7 istemci taraflı metotlar

#### $request->getRequestTarget();

Eğer URI varsa bu değere yoksa, "/" değerine döner.

#### $request->withRequestTarget(string $requestTarget);

Nesneye bir URI değeri atar.

```php
$request = $request->
    withMethod('POST')
    withRequestTarget("/test?foo=bar")
    withUri(new Uri('https://example.org/'));
```

#### $request->getUri();

`Zend\Diactoros\Uri` nesnesine geri döner.

#### $request->withUri(UriInterface $uri, $preserveHost = false);

Girilen uri nesnesi ile birlikte request nesnesinin klonlanmış bir örneğine geri döner. İkinci parametre true ise ana bilgisayara ait http başlığının orijinal durumu korunur.

#### $request->getProtocolVersion();

HTTP protokol versiyonuna geri döner.

#### $request->withProtocolVersion();

Girilen HTTP protokol versiyonu ile birlikte request nesnesinin klonlanmış bir örneğine geri döner. Version numaraları sadece http versiyon numaraları olmalıdır. (örnek., "1.1", "1.0").


### Psr7 sunucu taraflı metotlar

#### $request->getServerParams();

Http `$_SERVER` değişkenine geri döner.

#### $request->getCookieParams();

Http `$_COOKIE` değişkenine geri döner.

#### $request->withCookieParams(array $cookies);

Girilen `$_COOKIE` çerez değerleri ile birlikte request nesnesine geri döner.

#### $request->getQueryParams();

Http query parametrelerine geri döner.

#### $request->withQueryParams(array $query);

Girilen http query parametreleri birlikte request nesnesine geri döner.

#### $request->getParsedBody();

Http `$_POST` değişkenine geri döner.

#### $request->withParsedBody($data);

Girilen http post verisi ile birlikte request nesnesine geri döner.

#### $request->getUploadedFiles();

Http `$_FILES` değişkenini okuyarak `UploadedFile` nesnesine geri döner.

```php
$file0 = $request->getUploadedFiles()['files'][0];
$file1 = $request->getUploadedFiles()['files'][1];

printf(
    "Received the files %s and %s",
    $file0->getClientFilename(),
    $file1->getClientFilename()
);

// "Received the files file0.txt and file1.html"
```

Dosyayı upload dizinine taşımak.

```php
$filename = sprintf(
    '%s.%s',
    create_uuid(),
    pathinfo($file0->getClientFilename(), PATHINFO_EXTENSION)
);
$file0->moveTo(DATA_DIR . '/' . $filename);

// Stream a file to Amazon S3.
// Assume $s3wrapper is a PHP stream that will write to S3, and that
// Psr7StreamWrapper is a class that will decorate a StreamInterface as a PHP
// StreamWrapper.

$stream = new Psr7StreamWrapper($file1->getStream());
stream_copy_to_stream($stream, $s3wrapper);
```

#### $request->withUploadedFiles(array $uploadedFiles);

Http `$_FILES` değişkeni ile birlikte request nesnesine geri döner.

#### $request->getAttributes();

Request nesnesine `withAttribute()` metodu ile atanmış değerlere geri döner.

#### $request->getAttribute($attribute, $default = null);

Request nesnesinden bir niteliğe ait değeri alır. İkinci parametre değer bulunamadığında fonksiyonun döneceği değeri belirler.

#### $request->withAttribute($attribute, $value);

Atanan nitelik ile birlikte request nesnesine geri döner.

#### $request->withoutAttribute($attribute);

Atanan nitelik olmadan request nesnesine geri döner.

#### $request->getMethod();

Gelen isteğe ait http metoduna geri döner.

#### $request->withMethod($method);

Nesnenin geçerli metodunu değiştirir.

```php
$request = $request->withMethod('PUT');
```

<a href="https://www.php-fig.org/psr/psr-7/">Daha fazla örnek için Psr7 sayfasını ziyaret edebilirsiniz.</a>

### Psr7 URI sınıfı metotları


#### $uri->getScheme();

Uri şemasına geri döner.

```php
echo $request->getUri()->getScheme(); // http
```

#### $uri->withScheme($scheme);

Uri nesnesine uri şeması tayin eder.

#### $uri->getAuthority();

Yetki alanına geri döner.

```php
echo $request->getUri()->getAuthority(); // example.com
```

#### $uri->getUserInfo();

Uri de geçen kullanıcı bilgilerine geri döner.

```php
use Zend\Diactoros\Uri;

$request = $request->withUri(new Uri('http://john:doe@example.com:81/'));
echo $request->getUri()->getUserInfo();  // john:doe
```

#### $uri->withUserInfo($user, $password = null);

Uri nesnesine kullanıcı bilgileri tayin eder.

#### $uri->getHost();

Http host değerine geri döner.

```php
echo $request->getHost();  // example.com
```

#### $uri->withHost($host);

Uri nesnesine http host değerini atar.

#### $uri->getPort();

Http port değerine geri döner.

```php
$request = $request->withUri(new Uri('http://example.com:81/'));
echo $request->getUri()->getPort();  // 81
```

#### $uri->withPort($port);

Uri nesnesine http port değerini atar.

#### $uri->getPath();

Http path değerine geri döner.

```php
$request = $request->withUri(new Uri('http://example.com/test?a=b'));
echo $request->getUri()->getPath(); // /test
```
#### $uri->withPath($path);

Uri nesnesine http path değerini atar.

#### $uri->getQuery();

Http query parametrelerine string türünde geri döner.

```php
$request = $request->withUri(new Uri('http://example.com/test?a=b'));
echo $request->getUri()->getQuery(); // a=b
```

#### $uri->withQuery($query);

Uri nesnesine http query değerini atar.

#### $uri->getFragment();

Diyez `#` karakteri değerine geri döner.

```php
$request = $request->withUri(new Uri('http://example.com/#1'));
echo $request->getUri()->getFragment(); // 1
```

#### $uri->withFragment($fragment);

Uri nesnesine http fragment değerini atar.


### Yardımcı metotlar

Kullanımı kolaylaştırmak amacıyla `http` kütüphanesi `Psr7` sınıfına genişleyerek aşağıdaki metotların tek bir sınıf üzerinden çalıştırılmasına yardımcı olur.

#### $request->isXmlHttpRequest();

Gelen http isteği `Ajax` ise `true` aksi durumda `false` değerine geri döner.

#### $request->isGet();

Gelen http metodu `GET` ise `true` aksi durumda `false` değerine geri döner.

#### $request->isHead();

Gelen http metodu `HEAD` ise `true` aksi durumda `false` değerine geri döner.

#### $request->isPost();

Gelen http metodu `POST` ise `true` aksi durumda `false` değerine geri döner.

#### $request->isPut();

Gelen http metodu `PUT` ise `true` aksi durumda `false` değerine geri döner.

#### $request->isDelete();

Gelen http metodu `GET` ise `true` aksi durumda `false` değerine geri döner.

#### $request->isTrace();

Gelen http metodu `TRACE` ise `true` aksi durumda `false` değerine geri döner.

#### $request->isConnect();

Gelen http metodu `CONNECT` ise `true` aksi durumda `false` değerine geri döner.

#### $request->isPatch();

Gelen http metodu `PATCH` ise `true` aksi durumda `false` değerine geri döner.

#### $request->isOptions();

Gelen http metodu `OPTIONS` ise `true` aksi durumda `false` değerine geri döner.

#### $request->isPropFind();

Gelen http metodu `PROPFIND` ise `true` aksi durumda `false` değerine geri döner.

Detaylı dökümentasyona <a href="https://docs.zendframework.com/zend-diactoros">https://docs.zendframework.com/zend-diactoros</a> bağlantısından ulaşabilirsiniz.


### Ip Adresleri

Sunucunuz önünde bazen proxy sunucular olabilir böyle bir durumda güvenilir ip adresleri elde etmek için `RemoteAddr` sınıfını kullanabilirsiniz.

Güvenilir kullanıcı ip adresi elde etmek için bir örnek.

```php
use Obullo\Http\RemoteAddr;

$remoteAddr = new RemoteAddr;
$remoteAddr->setUseProxy();
$remoteAddr->setProxyHeader('HTTP_X_FORWARDED_FOR');
$remoteAddr->setTrustedProxies(array('10.0.0.128','10.0.0.129')); // Reverse proxy ip white list

echo $remoteAddr->getIpAddress(); // 212.1.100.99
```