
## Loglama

Log servisi loglama işlemleri için `Monolog/Logger` composer paketini kullanır. 

### Loglama servisi

```php
$container->setFactory('logger', 'Services\LoggerFactory');
```

Log servisi `dev` ortamında log mesajlarını varsayılan olarak `/var/log/debug.log` doyasına işler.

```php
class LoggerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $monolog = $container->get('loader')
            ->load(ROOT, '/config/%s/monolog.yaml', true)
            ->monolog;

        $logger = new Logger($monolog->default_channel);

        if (false == $monolog->enabled) {
            $logger->pushHandler(new NullHandler);
            return $logger;
        }
        if (getenv('APP_ENV') == 'dev') {
            $logger->pushHandler(new StreamHandler(ROOT .'/var/log/debug.log', Logger::DEBUG, true, 0666));
        }
        return $logger;
    }
}
```

### Loglama

Kontrolör içerisinden log sınıfına erişim.

```php
class DefaultController extends Controller
{
	public function __construct()
	{
        $this->logger->info('My logger is now ready');
    }
}
```

Konteyner içerisinden,

```php
$logger = $container->get('logger');
$logger->info('My logger is now ready');
```

### Konsol komutları

Yerel sunucuda, konsoldan `log:debug` komutu ile `/var/log/debug.log` dosyasını takip edebilirsiniz.

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

### Loglama seviyeleri

<table>
	<thead>
		<tr>
			<th>Seviye</th>
			<th>Açıklama</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>DEBUG (100)</td>
			<td>Detaylı hata ayıklama bilgileri</td>
		</tr>
		<tr>
			<td>INFO (200)</td>
			<td>Interesting events. Examples: User logs in, SQL logs.</td>
		</tr>

		<tr>
			<td>WARNING (250)</td>
			<td>Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.</td>
		</tr>

		<tr>
			<td>ERROR (400):</td>
			<td>Runtime errors that do not require immediate action but should typically be logged and monitored.</td>
		</tr>

		<tr>
			<td>CRITICAL (500):</td>
			<td>Critical conditions. Example: Application component unavailable, unexpected exception.</td>
		</tr>

		<tr>
			<td>ALERT (550):</td>
			<td>Action must be taken immediately. Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.</td>
		</tr>

		<tr>
			<td>EMERGENCY (600):</td>
			<td>Emergency: system is unusable.</td>
		</tr>
	</tbody>
</table>

Detaylı dökümentasyona <a href="https://seldaek.github.io/monolog/">https://seldaek.github.io/monolog/</a> bağlantısından ulaşabilirsiniz.