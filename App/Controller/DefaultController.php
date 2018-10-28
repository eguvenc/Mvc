<?php

namespace App\Controller;

// use Zend\Db\Sql\Delete;
// use Zend\Db\Sql\Where;
// use Zend\Db\Sql\Sql;
// use Zend\Db\TableGateway\TableGateway;
// use Zend\Db\TableGateway\Feature\RowGatewayFeature;

use Obullo\Http\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DefaultController extends Controller
{
	public function __construct(Request $request)
	{
        // $this->logger->info('My logger is now ready');

        // $this->translator->setLocale('es');
        // echo $this->translator->getLocale();

        // $this->translator->addTranslationFilePattern('PhpArray', ROOT, '/var/messages/%s/messages.php');
        // echo $this->translator->translate(100);

        // echo $this->translator->translate('Invalid type given. String expected', 'default', 'en');   
        // echo $this->translator->translate('Application Error');

		// $this->middleware->add('Error')
  //           ->addArguments(array('code' => 404, 'message' => '404 - Sayfa bulunamadı'));

        // $stack = $this->middleware->getStack();
        //  print_r($stack);
	}


    public function index(Request $request) : Response
    {
        // $sql = new Sql($this->adapter);

        // // $select = $sql->select();
        // // $select->from('foo');
        // // $select->where(['id' => 2]);

        // $delete = $sql->delete();
        // $delete->from('test_users');
        // $delete->where(['id' => 4]);

        // echo $this->adapter->driver->formatParameterName('id');
        // die;

        // $stmt    = $this->adapter->query('SELECT * FROM `users` WHERE `id` = ? AND `name` = ?');
        // $results = $stmt->execute(['name' => 'user_78', 'id' => 2]);

        // // $results = $this->adapter->query('SELECT * FROM `users` WHERE `id` = :id', ['id' => 2]);

        // foreach ($results as $row) {
        //     // echo $row->name;
        // }
        // $profiler = $this->adapter->getProfiler()->getLastProfile();

        // var_dump($profiler);

        // var_dump($result->toArray()); // object(Zend\Db\ResultSet\ResultSet)#96

        // $query = $sql->buildSqlString($delete);
        // $this->adapter->query($query, $this->adapter::QUERY_MODE_EXECUTE);

        // $userTable = new TableGateway('test_users', $this->adapter);
        // $userTable->insert(['name' => 'test_124']);

        // $userRow = $rowset->current();

        // var_dump($userRow);

        // $delete = new Delete('users');
        // $delete->where(new Where()); //  buraryı test

        // return $this->json(
        //     ['name' => 'Örnek Veri'],
        //     200,
        //     ['cache-control' => 'max-age=3600'],
        //     JSON_UNESCAPED_UNICODE
        // );
        
        // $this->flash->warning('Message has been sent');

        // $this->response->redirect('/asdasd/');
        // $this->response->render(array $data);
        // 
        // $this->redirect();

        return $this->render('welcome');
    }

    public function dummy()
    {    
        return $this->render('welcome');
    }
}