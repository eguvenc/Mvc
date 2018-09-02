
## Http

Http sınıfı sunucuya gelen http isteklerini yönetir ve Psr7 standartlarını zorunlu tutar. Bu paket `Zend/Diactoros` paketine genişler.

### Konfigürasyon

```php
namespace Services;

use Obullo\Mvc\Http\ServerRequestFactory;
use Zend\ServiceManager\Factory\FactoryInterface;

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
    public function __invoke($container, $requestedName, array $options = null)
    {
        return ServerRequestFactory::fromGlobals();
    }
}
```

Request nesnesi `index.php` dosyası içerisinde servis olarak konfigüre edilmiştir.

> Http nesnesi metotları Psr7 metotlarını içerir. Psr7 metotları hakkında detaylı bilgiye <a href="https://www.php-fig.org/psr/psr-7/">https://www.php-fig.org/psr/psr-7</a> adresinden ulaşabilirsiniz.

### Kurtarıcı metotlar

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