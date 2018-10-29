
## Konsol

Çerçeve içerisinde konsol paketi harici olarak kullanılır ve bunun için varsayılan olarak <a href="https://symfony.com/doc/current/components/console.html">Symfony/Console</a> paketi tercih edilmiştir.

Paket mevcut değil ise aşağıdaki konsol komutu ile yüklenmelidir.

```bash
composer require symfony/console
```

### Konsol dosyası

Konsol komutları uygulamanızın kök dizinindeki `console` dosyası ile çalıştırılır.

```
php console command:name
```

### Konsol komutları

Çerçeve, 3 adet konsol komutu ile birlikte gelir.

* log:debug
* log:clear
* cache:clear

Yerel sunucuda, konsoldan `log:debug` komutu ile `/var/log/debug.log` dosyasına yazılan logları takip edebilirsiniz.

```bash
$ php console log:debug
```

Çıktı

```
[2018-08-21 13:36:57] system.INFO: My logger is now ready [] []
```

Yerel sunucuda, konsoldan `log:clear` komutu ile `/var/log/debug.log` dosyasını silebilirsiniz.

```bash
$ php console log:clear
```

Çıktı

```bash
Log file deleted successfully.
```

Önbellek temizleme yani `cache:clear` komutu `/var/cache/` klasörü altındaki `config.php` dosyasını temizler.

```bash
$ php console cache:clear
```

> Eğer konfigürasyon dosyaları için önbellekleme açıksa bu komut ile önbellek temizlenir. Önbellekleme yerel sunucuda varsayılan olarak kapalıdır. Fakat canlı sunucularda bu konfigürasyon aktif hale gelir. Konfigürasyon ve yönlendirme dosyalarındaki değişikliklerin geçerli olabilmesi için her `deploy` işleminde `cache:clear` komutunu çalıştıran bir `bash script` yazmanız tavsiye edilir. Daha detaylı bilgiye konfigürasyon paketi dökümentasyonundan ulaşabilirsiniz.

### Yeni konsol komutları

Yeni konsol komutlarını `Command` klasörü içerisinde oluşturabilirsiniz.

```php
namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HelloWorld extends Command
{
    protected function configure()
    {
        $this
            ->setName('hello:world')
            ->setDescription('Say hello world !');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$output->writeln('<info>Hello World !</info>');
    }
}
```

Kök dizindeki <b>console</b> dosyasına `$application->add()` metodu ile yeni komut sınıfını eklemeliyiz.

```php
use Symfony\Component\Console\Application;
use Command\{
	LogClear,
	LogDebug,
	CacheClear
	HelloWorld
};
$application = new Application();
$application->add(new LogClear);
$application->add(new LogDebug);
$application->add(new CacheClear);
$application->add(new HelloWorld);
$application->run();
```

Konsol komutunu çalıştırmak için komut istemcisine aşağıdaki satırları yazalım.

```bash
php console hello:world
```

Çıktı

```bash
Hello World !
```

Konsol renklendirme için <a href="https://symfony.com/doc/current/console/coloring.html">https://symfony.com/doc/current/console/coloring.html</a> adresini ziyaret edebilirsiniz.