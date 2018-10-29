
## Veritabanı

Veritabanı sınıfı uygulama içerisindeki veritabanı bağlantısı ve özelliklerini kontrol eden fonksiyonları içerir. Çerçeve içerisinde veritabanı paketi harici olarak kullanılır ve bunun için varsayılan olarak <a href="https://docs.zendframework.com/zend-db/adapter/">Zend/Db</a> paketi tercih edilmiştir. Doctribe DBAL paketi de alternatif olarak desteklenmektedir.

Paket mevcut değil ise aşağıdaki konsol komutu ile yüklenmelidir.

```bash
composer require zendframework/zend-db
```

### Zend veritabanı servisi (Varsayılan)

Veritabanı nesnesi diğer servisler gibi `index.php` dosyası içerisinden konfigüre edilir. 

```php
$container->setFactory('adapter', 'Services\ZendDbFactory');
```

### Doctrine veritabanı servisi

```php
$container->setFactory('connection', 'DoctrineDBALFactory');
```

Varsayılan veritabanı servisi `Zend\Db\Adapter\Adapter` nesnesine geri döner.

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

Bu kısım hakkında daha fazla örnek için <a href="https://docs.zendframework.com/zend-db/adapter/">https://docs.zendframework.com/zend-db/adapter/</a> adresini ziyaret edebilirsiniz.

### Depo (Repository) Tasarım Deseni

Depo tasarım deseni veri merkezli uygulamalarda veriye erişimin ve yönetimin tek noktaya indirgenmesini sağlayan bir tasarım desenidir. Bu tasarım deseninde `CRUD` metotları yani; `Create`, `Read`, `Update` ve `Delete` operasyonları tek bir sınıf içerisinden yürütülür. Uygulama varlıkları `Entity` sınıfı tarafından, uygulama veritabanı işlevleri ise `Repository` sınıfı tarafından kontrol edilir. Böylece uygulama katmanlara ayrılarak daha büyük uygulamalar geliştirebilmek için gerekli esneklik sağlanmış olur.


### Varlıklar (Entities)

Varlık nesneleri uygulama içerisinde veritabanı ile kullanılan değişkenlerin tutulduğu yerdir. Depo sınıfı ihtiyaç duyduğunda veritabanı okuma ve yazma işlemlerinde varlık sınfına ait değişken değerlerine başvurmalıdır. Veritabanına yazma ve okuma işlemlerinde bu değerler dışarıdan gelen veriler ile doldurulur.

```php
namespace App\Entity;

class UserEntity
{
    protected $firstname;
    protected $lastname;
    protected $email;

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function setFirstname($firstName)
    {
        $this->firstname = ucfirst(strtolower($firstName));
    }

    public function setLastname($lastName)
    {
        $this->lastname = ucfirst(strtolower($lastName));
    }

    public function getInsertVariables() : array
    {
        return get_object_vars($this);
    }

    public function getUpdateVariables() : array
    {
        $data = array();
        foreach (get_object_vars($this) as $key => $val) {
            if (false == is_null($val)) {
                $data[$key] = $val;
            }
        }
        return $data;
    }
}
```

### Repositories (Depolar)

Depolar veritabanı işlemlerinin oluşturulduğu yerlerdir.

```php
namespace App\Repository;

use App\Entity\UserEntity;
use Zend\Db\TableGateway\Feature\RowGatewayFeature;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class UserRepository
{
    protected $tableGateway;

    public function __construct(Adapter $adapter)
    {
        $this->tableGateway = new TableGateway('users', $adapter, new RowGatewayFeature('id'));
    }

    public function insert(UserEntity $user)
    {
        $this->tableGateway->insert($user->getInsertVariables());

        return $this->tableGateway->getLastInsertValue();
    }

    public function update(UserEntity $user, $id)
    {
        $this->tableGateway->update($user->getUpdateVariables(), array('id' => $id));
    }

    public function delete($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }

    public function getUser($id)
    {
        $id  = (int)$id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (false == $row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function fetchAll()
    {
        return $this->tableGateway->select();
    }
}
```

Varlık ve Depo nesneleri ile veritabanına bir kayıt ekleyelim.

```php
$user = new UserEntity;
$user->setFirstname('firstname');
$user->setLastname('lastname');
$user->setEmail('test@test.com');
 
$userRepo = new UserRepository($adapter);
$lastInsertedId = $userRepo->insert($user);
```

### Sorgu Sonuçları 

Sorgu sonuçlarını elde etmede çoğu amaç için, bir `Zend\Db\ResultSet\ResultSet` örneği veya bir `Zend\Db\ResultSet\AbstractResultSet` türevi kullanılır. `AbstractResultSet` sınıfı aşağıdaki temel işlevleri sunar:

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

### Sorgu Sonuçlarını Entity Sınıfı ile Birleştirmek

`Zend\Db\ResultSet\HydratingResultSet`, geliştiricinin satır verilerini hedef nesneye almak için uygun bir <b>hidrasyon stratejisi</b> seçmesini sağlayan daha esnek bir `ResultSet` nesnesidir. Sonuçların üzerinde yineleme yaparken, `HydratingResultSet` bir hedef nesnenin prototipini alır ve her satır için bir kez klonlar. `HydratingResultSet` daha sonra bu klonu satır verileriyle eşleştirir.

`HydratingResultSet`, yüklemeniz gereken zend-hydrator'a bağlıdır:

```
$ composer require zendframework/zend-hydrator
```

Aşağıdaki örnekte, veritabanından gelen satırlar yinelenecek ve yineleme sırasında `HydratingResultSet`, satır verisini doğrudan klonlanmış `UserEntity` nesnesinin korunan değişkenlerine enjekte edecek şekilde kullanacaktır.

```php
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Hydrator\Reflection as ReflectionHydrator;

class UserEntity
{
    protected $firstname;
    protected $lastname;

    public function getFirstName()
    {
        return $this->firstname;
    }

    public function getLastName()
    {
        return $this->lastname;
    }

    public function setFirstName($firstName)
    {
        $this->firstname = $firstName;
    }

    public function setLastName($lastName)
    {
        $this->lastname = $lastName;
    }
}

$statement = $adapter->createStatement('SELECT * FROM `users` WHERE `id` = ?');
$statement->prepare();
$result = $statement->execute(['id' => 2]);

if ($result instanceof ResultInterface && $result->isQueryResult()) {
    $resultSet = new HydratingResultSet(new ReflectionHydrator, new UserEntity);
    $resultSet->initialize($result);

    foreach ($resultSet as $user) {
        echo $user->getFirstName() . ' ' . $user->getLastName() . PHP_EOL;
    }
}
```

Bu kısım hakkında daha fazla örnek için <a href="https://docs.zendframework.com/zend-db/adapter/">https://docs.zendframework.com/zend-db/adapter/</a> adresini ziyaret edebilirsiniz.