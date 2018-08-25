
## Çerezler

Çerez, herhangi bir internet sitesi tarafından son kullanıcının bilgisayarına bırakılan bir tür tanımlama dosyasıdır. Çerez dosyalarında oturum bilgileri ve benzeri veriler saklanır. Çerez kullanan bir siteyi ziyaret ettiğinizde, bu site tarayıcınıza bir ya da birden fazla çerez bırakma konusunda talep gönderebilir.

> Bir çereze kayıt edilebilecek maksimum veri 4KB tır.

### Çerez servisi

```php
$container->setFactory('cookie', 'Services\CookieFactory');
```

### Bir çereze veri kaydetmek

Çereze sınıfı varsayılan değerleri App `config/dev/framework.yaml` dosyasından okur.

```yaml
# application
#    

framework:
    cookie:
        domain:
        path: /
        secure: false
        httpOnly: true
        expire: 0
```

Varsayılan değerlerle bir çereze veri kaydetmek için set metodu kullanılır.

```php
$this->cookie
    ->expire(0)
    ->set('name', 'value'); 
```

Varsayılan değerleri aşağıdaki gibi değiştrilebilir.

```php
$this->cookie
    ->name('hello')
    ->value('world')
    ->expire(86400)
    ->domain('')
    ->path('/')
    ->set();
```

### Bir çerez verisini okumak

Bir çerezi okumak için get metodu kullanılır.

```php
if ($value = $this->cookie->get('name')) {
	echo $value;
}
```

Eğer çereze kayıtlı bir değer yoksa fonksiyon varsayılan <kbd>null</kbd> değerine döner.


```php
if (false == $this->cookie->get('name', false)) {
    echo "Cookie does not exist";
}
```

İkinci parametre çerez bulunamadığında fonksiyonun hangi türe döneceğini belirler.

### Bir çerezi silmek

Bir çerezi silmek için çerez ismi girmeniz yeterlidir.

```php
$this->cookie->delete("name");
```

Domain ve path metotları ile bir örnek.

```php
$this->cookie
	->domain('my.subdomain.com')
	->path('/')
	->delete("name");
```

veya

```php
$this->cookie
	->name('name')
	->domain('my.subdomain.com')
	->path('/')
	->delete();
```

### Parametreler

<table>
    <thead>
        <tr>
            <th>Parametre</th>
            <th>Açıklama</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>name</td>
            <td>Çerezin kaydedileceği isim.</td>
        </tr>
        <tr>
            <td>value</td>
            <td>Çereze kayıt edilecek değer.</td>
        </tr>
        <tr>
            <td>expire</td>
            <td>Son erme süresi saniye türünden girilir ve girilen değer şu anki zaman üzerine eklenir. Eğer sona erme süresi girilmez ise konfigürasyon dosyasındaki değer varsayılan olarak kabul edilir. Eğer sona erme süresi <kbd>0</kbd> dan küçük olarak girilirse çerez tarayıcı kapandığında kendiliğinden yok olur.</td>
        </tr>
        <tr>
            <td>domain</td>
            <td>Çerezin geçerli olacağı alan adıdır. Site-wide çerezler ( tüm alt domainlerde geçerli çerezler ) kaydetmek için domain parametresini <kbd>.your-domain.com</kbd> gibi girmeniz gereklidir.</td>
        </tr>
        <tr>
            <td>path</td>
            <td>Çerezin geçerli olacağı dizin, genel olarak çerezin tüm url adresinlerine ait alt dizinlerde kabul edilmesi istendiğinden bölü işareti "/" ( forward slash ) varsayılan değer olarak kullanılır.</td>
        </tr>
        <tr>
            <td>secure</td>
            <td>Eğer çerez güvenli bir <kbd>https://</kbd> protokolü üzerinden okunuyorsa bu değerin true olması gerekir. Protokol güvenli olmadığında çereze erişilemez.</td>
        </tr>
        <tr>
            <td>httpOnly</td>
            <td>Eğer http only parameteresi true gönderilirse çerez sadece http protokolü üzerinden okunabilir hale gelir javascript gibi diller ile çerezin okunması engellenmiş olur. Çerez güvenliği ile ilgili daha fazla bilgi için <a href="http://resources.infosecinstitute.com/securing-cookies-httponly-secure-flags/" target="_blank">bu makaleden</a> faydalanabilirsiniz.</td>
        </tr>
        </tbody>
</table>


### Cookie sınıfı referansı

##### $this->cookie->name(string $name);

Kaydedilmek üzere olan bir çereze isim atar.

##### $this->cookie->value(mixed $value);

Kaydedilmek üzere olan bir çerez ismine değer atar.

##### $this->cookie->expire(int $expire = 0);

Kaydedilmek üzere olan bir çerezin sona erme süresini belirler.

##### $this->cookie->domain(string $domain = '');

Kaydedilmek üzere olan bir çereze ait alanadını belirler.

##### $this->cookie->path(string $path = '/');

Kaydedilmek üzere olan bir çereze ait path parametresini tanımlar.

##### $this->cookie->secure(boolean $bool = false);

Kaydedilmek üzere olan bir çereze ait secure parametresini tanımlar.

##### $this->cookie->httpOnly(boolean $bool = false);

Kaydedilmek üzere olan bir çereze ait httpOnly parametresini tanımlar.

##### $this->cookie->set(mixed $name, string $value);

Gönderilen parametrelere göre bir çereze veri kaydeder. En son çalıştırılmalıdır. Kayıt işleminden sonra daha önce kullanılan çereze ait veriler başa döndürülür.

##### $this->cookie->get(string $name, mixed $return = null);

Kayıtlı bir çerezi okur eğer çerez mevcut değilse <kbd>null</kbd> değerine döner.

##### $this->cookie->delete(string $name);

Gönderilen parametrelere göre bir çerezi tarayıcıdan siler.