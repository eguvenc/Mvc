
### Entities

Entity objects are where the variables used with the database are kept in the application. When the `Repository` class needs, it should refer to the variable values ​​of the entity class in the database read and write operations. These values ​​are filled with external data in the write and read operations to the database.

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

    public function getEmail()
    {
        return $this->email;
    }

    public function setFirstname($firstName)
    {
        $this->firstname = $firstName;
    }

    public function setLastname($lastName)
    {
        $this->lastname = $lastName;
    }

    public function setEmail($email)
    {
        $this->email = $email;
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

You can use entities to convert input variables to objects.

```php
$post = $request->getParsedBody();

$user = new UserEntity;
$user->setFirstname($post['firstname']);
$user->setLastname($post['lastname']);
$user->setEmail($post['email']);
```