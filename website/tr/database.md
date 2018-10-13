
## Veritabanı

Veritabanı sınıfı uygulama içerisindeki veritabanı bağlantısı ve özelliklerini kontrol eden fonksiyonları içerir. Çerçeve içerisinde veritabanı paketi harici olarak kullanılır ve bunun için varsayılan olarak <a href="https://docs.zendframework.com/zend-db/adapter/">Zend Db</a> paketi tercih edilmiştir. Doctribe DBAL paketi de alternatif olarak desteklenmektedir.

### Doctrine veritabanı servisi

```php
$container->setFactory('connection', 'DoctrineDBALFactory');
```

### Zend veritabanı servisi

```php
$container->setFactory('adapter', 'Services\ZendDbFactory');
```

> Uygulama içinde `Services\ZendDbFactory` index.php içerisinde tanımlı olarak gelir.


Veritabanı servisi `Zend\Db\Adapter\Adapter` nesnesine geri döner.

```php
namespace Services;

use Zend\Db\Adapter\Adapter;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Obullo\Logger\ZendSQLLogger;

class ZendDbFactory implements FactoryInterface
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
        $database = $container->get('loader')
            ->load(ROOT, '/config/%s/database.yaml', true)
            ->database;
            
        $config = parse_url($database->url);
		$adapter = new Adapter (
            [
    		    'driver'   => $config['scheme'],
    		    'database' => ltrim($config['path'], '/'),
    		    'hostname' => $config['host'],
    		    'port' 	   => $config['port'],
    		    'username' => $config['user'],
    		    'password' => $config['pass'],
            ],
            null,
            null,
            null,
            new ZendSQLLogger($container->get('logger'))
        );
        return $adapter;
    }
}
```

### Bağlantı konfigürasyonu

`Zend\Db\Adapter\Adapter` sınıfı bağlantıyı aşağıdaki parametreler ile oluşturur.

```php
$adapter = new Zend\Db\Adapter\Adapter([
    'driver'   => 'Mysqli',
    'database' => 'zend_db_example',
    'username' => 'developer',
    'password' => 'developer-password',
]);
```

Aşağıda yapılandırma dizisinde veritabanı bağlantısı için olması gereken anahtar-değer çiftleri için bir tablo gösteriliyor.

<table>
    <thead>
        <tr>
            <th>Anahtar</th>
            <th>Gerekli mi ?</th>
            <th>Değer</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>driver</td>
            <td>Genel olarak evet</td>
            <td>Mysqli, Sqlsrv, Pdo_Sqlite, Pdo_Mysql, Pdo(= Diğer PDO Sürücüsü)</td>
        </tr>
        <tr>
            <td>database</td>
            <td>Genel olarak evet</td>
            <td>Veritabanı ismi</td>
        </tr>
        <tr>
            <td>username</td>
            <td>Genel olarak evet</td>
            <td>Bağlantı kullanıcı adı</td>
        </tr>
        <tr>
            <td>password</td>
            <td>Genel olarak evet</td>
            <td>Bağlantı şifresi</td>
        </tr>
        <tr>
            <td>hostname</td>
            <td>Genel olarak hayır</td>
            <td>Bağlantı IP adresi veya sunucu adı</td>
        </tr>
        <tr>
            <td>port</td>
            <td>Genel olarak hayır</td>
            <td>Bağlantı port numarası</td>
        </tr>
        <tr>
            <td>charset</td>
            <td>Genel olarak hayır</td>
            <td>Bağlantı karakter seti</td>
        </tr>
        </tbody>
</table>


### Sorgu hazırlama

Bu genellikle, değerler için yer tutucuları (placeholders) içeren bir SQL bildirimi sağlayacağınız ve bu yer tutucular için ayrı ayrı ikameler sağlayacağınız anlamına gelir. Örnek olarak:

```php
$result = $adapter->query('SELECT name FROM `users` WHERE `id` = ?', [2]);

foreach ($result as $row) {
    echo $row->name;
}
```

### Sorgu çalıştırma

Bazı durumlarda, ifadeleri doğrudan hazırlıksız yürütmeniz gerekir. Hazırlık adımı olmaksızın bir sorguyu yürütmek için, yürütmeyi gösteren ikinci argüman olarak bir bayrağı iletmeniz gerekir:

```php
$adapter->query(
    'ALTER TABLE ADD INDEX(`foo_index`) ON (`foo_column`)',
    Adapter::QUERY_MODE_EXECUTE
);
```

### Sorgu ifadeleri

`Query()` metodu bir bağdaştırıcı ve bir veritabanının bağdaştırıcı aracılığıyla hızlı sorgulanması için oldukça kullanışlıdır, ancak genellikle bir deyim oluşturmak ve bununla doğrudan etkileşimde bulunmak daha mantıklıdır, böylece hazırla-sonra çalıştır iş akışı üzerinde daha fazla kontrole sahip olursunuz. Bunu yapmak için bağdaştırıcı size, kendi hazırladığınız yürütme iş akışınızı yönetebilmeniz için kullanmak üzere sürücüye özel bir açıklama oluşturmanıza olanak sağlayan `createStatement()` adında bir rutin verir.

```php
$stmt   = $adapter->createStatement($sql, $optionalParameters);
$result = $stmt->execute();
```

### Zend\Db\ResultSet\ResultSet 

Çoğu amaç için, bir `Zend\Db\ResultSet\ResultSet` örneği veya bir `Zend\Db\ResultSet\AbstractResultSet` türevi kullanılır. `AbstractResultSet` sınıfı aşağıdaki temel işlevleri sunar:

```php
namespace Zend\Db\ResultSet;

use Iterator;

abstract class AbstractResultSet implements Iterator, ResultSetInterface
{
    public function initialize(array|Iterator|IteratorAggregate|ResultInterface $dataSource) : self;
    public function getDataSource() : Iterator|IteratorAggregate|ResultInterface;
    public function getFieldCount() : int;

    /** Iterator */
    public function next() : mixed;
    public function key() : string|int;
    public function current() : mixed;
    public function valid() : bool;
    public function rewind() : void;

    /** countable */
    public function count() : int;

    /** get rows as array */
    public function toArray() : array;
}
```

### Zend\Db\ResultSet\HydratingResultSet

Zend\Db\ResultSet\HydratingResultSet is a more flexible ResultSet object that allows the developer to choose an appropriate "hydration strategy" for getting row data into a target object. While iterating over results, HydratingResultSet will take a prototype of a target object and clone it once for each row. The HydratingResultSet will then hydrate that clone with the row data.

The HydratingResultSet depends on zend-hydrator, which you will need to install:

```
$ composer require zendframework/zend-hydrator
```

In the example below, rows from the database will be iterated, and during iteration, HydratingResultSet will use the Reflection based hydrator to inject the row data directly into the protected members of the cloned UserEntity object:

```php
se Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Hydrator\Reflection as ReflectionHydrator;

class UserEntity
{
    protected $first_name;
    protected $last_name;

    public function getFirstName()
    {
        return $this->first_name;
    }

    public function getLastName()
    {
        return $this->last_name;
    }

    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;
    }

    public function setLastName($lastName)
    {
        $this->last_name = $lastName;
    }
}

$statement = $driver->createStatement($sql);
$statement->prepare($parameters);
$result = $statement->execute();

if ($result instanceof ResultInterface && $result->isQueryResult()) {
    $resultSet = new HydratingResultSet(new ReflectionHydrator, new UserEntity);
    $resultSet->initialize($result);

    foreach ($resultSet as $user) {
        echo $user->getFirstName() . ' ' . $user->getLastName() . PHP_EOL;
    }
}
```

For more information, see the <a href="">zend-hydrator</a> documentation to get a better sense of the different strategies that can be employed in order to populate a target object.




Daha fazla örnek için <a href="https://docs.zendframework.com/zend-db/adapter/">https://docs.zendframework.com/zend-db/adapter/</a> adresini ziyaret edebilirsiniz.