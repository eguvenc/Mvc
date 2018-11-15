
## Çerezler

Çerez, herhangi bir internet sitesi tarafından son kullanıcının bilgisayarına bırakılan bir tür tanımlama dosyasıdır. Çerez dosyalarında oturum bilgileri ve benzeri veriler saklanır. Çerez kullanan bir siteyi ziyaret ettiğinizde, bu site tarayıcınıza bir ya da birden fazla çerez bırakma konusunda talep gönderebilir.

> Bir çereze kayıt edilebilecek maksimum veri 4KB tır.

Çerezler uygulama içinde doğal php yöntemi `setcookie()` metodu ile kaydedilmelidir.

##### setcookie($name, $value = "", $expires = 0 , $path = "", $domain = "", $secure = false, $httponly = false);

### Bir çereze veri kaydetmek

Aşağıdaki örnekte tarayıcıda 1 saatliğine geçerli olan bir çerez kaydediyoruz. 1 saatlik süre sonunda çereze tarayıcıdan silinmiş olur.

```php
$value = 'foo';

setcookie("TestCookie", $value);
setcookie("TestCookie", $value, time()+3600);  /* 1 saatliğine geçerli */
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
            <td>Çerezin geçerli olacağı alan adıdır. Tüm alt domainlerde geçerli (site-wide) çerezler kaydetmek için domain parametresini <kbd>.your-domain.com</kbd> gibi girmeniz gereklidir.</td>
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
            <td>httponly</td>
            <td>Eğer http only parameteresi true gönderilirse çerez sadece http protokolü üzerinden okunabilir hale gelir javascript gibi diller ile çerezin okunması engellenmiş olur. Çerez güvenliği ile ilgili daha fazla bilgi için <a href="http://resources.infosecinstitute.com/securing-cookies-httponly-secure-flags/" target="_blank">bu makaleden</a> faydalanabilirsiniz.</td>
        </tr>
        </tbody>
</table>


### Diziler

Çerez ismini belirtirken dizi gösterimini kullanmak suretiyle çerez dizileri tanımlayabilirsiniz. Bu sayede dizi elemanı sayısı kadar çerez tanımlayabilirsiniz, fakat çerezleri betiğinizle aldığınızda değerlerin hepsi çerez isminde bir diziye yerleştirilirler:

```php
// çerezleri tanımlayalım

setcookie("cookie[three]", "cookiethree");
setcookie("cookie[two]", "cookietwo");
setcookie("cookie[one]", "cookieone");

// sayfayı yeniden yükledikten sonra çerezler okuyalım

if (isset($_COOKIE['cookie'])) {
    foreach ($_COOKIE['cookie'] as $name => $value) {
        $name = htmlspecialchars($name);
        $value = htmlspecialchars($value);
        echo "$name : $value <br />\n";
    }
}
```


### Bir çerez verisini okumak

```php
// Bağımsız bir çerezi oku

echo $_COOKIE["TestCookie"];

// Tüm çerezleri görmek için başka bir yol
print_r($_COOKIE);
```

### Bir çerezi silmek

Bir çerezi silerken, tarayıcınızın yürürlükten kaldırma mekanizmasını tetikleyebilmek için, süre bitiminin geçmişte kalmasını sağlamanız gerekir. Önceki örnekte gönderilen çerezin nasıl silineceğini görelim:

```php
// süre bitimini 1 saat önceye ayarlayalım

setcookie ("TestCookie", "", time() - 3600);
setcookie ("TestCookie", "", time() - 3600, "/", "example.com", 1);
```
