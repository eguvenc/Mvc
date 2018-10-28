
### Repositories

Repositories are places where database operations are created. Using the variables created within the Entity classes, they manage the database operations in one place.

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

Let's add a record to the database with `Entity` and `Repository` objects.

```php
$post = $request->getParsedBody();

$user = new UserEntity;
$user->setFirstname($post['firstname']);
$user->setLastname($post['lastname']);
$user->setEmail($post['email']);
 
$userRepo = new UserRepository($adapter);
$lastInsertedId = $userRepo->insert($user);
```