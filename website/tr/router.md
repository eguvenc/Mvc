
## Yönlendirmeler

Mvc router paketi `Obullo/Router` paketini kullanır. Bu paket <a href="https://docs.djangoproject.com/en/2.0/topics/http/urls/">Django Url Dispatcher</a> kütüphanesinden ilham alınarak geliştirilmiş bağımsız bir route paketidir.

### Uygulama yönlendirme

Yönlendirmeler `config/routes.yaml` dosyası içerisinde muhafaza edilir.

```
home:
    path: /
    handler: App\Controller\DefaultController::index

dummy:
    path: /dummy
    handler: App\Controller\DefaultController::dummy
```

### Parametreler

```
name:
	method : GET
	host: example.com
    scheme: http
    middleware: 
      - App\Middleware\Auth
      - App\Middleware\Dummy
    path: /
    handler: App\Controller\DefaultController::index
```

### Route olayları

Uygulama routing ile ilgili işlemler için `App/Event/RouteListener` sınıfı dinler.


<table>
    <thead>
        <tr>
            <th>Olay</th>
            <th>Açıklama</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>OnBuilder</td>
            <td>RouteCollection sınıfının yaratıldığı olaydır. Bu metot içerisinden özelleştirmeler yapılabilir.</td>
        </tr>
        <tr>
            <td>OnMatch</td>
            <td>Route eşleşmesinin gerçekleştiği olaydır.Metot eşleşmenin olduğu `Route` nesnesine geri döner böylece bu metot içerisinden özelleştirmeler yapılabilir.</td>
        </tr>
    </tbody>
</table>


```php
class RouteListener implements ListenerAggregateInterface,ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ListenerAggregateTrait;

    public function attach(EventManagerInterface $events, $priority = null)
    {
        $this->listeners[] = $events->attach('route.builder', [$this, 'onBuilder']);
        $this->listeners[] = $events->attach('route.match', [$this, 'onMatch']);
    }

    public function onBuilder(EventInterface $e) : RouteCollection
    {   
        $context = $e->getParam('context');
        $types = [
            new IntType('<int:id>'),
            new IntType('<int:page>'),
            new StrType('<str:name>'),
            new TranslationType('<locale:locale>'),
        ];
        $collection = new RouteCollection(array(
            'types' => $types
        ));
        $collection->setContext($context);
        $builder = new Builder($collection);

        $routes = $this->getContainer()
            ->get('loader')
            ->load(ROOT, '/config/routes.yaml')
            ->toArray();

        return $builder->build($routes);        
    }

    public function onMatch(EventInterface $e)
    {
        /*
        $route = $e->getParams();
        $route->getName();
        */
    }
}
```

Detaylı dökümentasyona <a href="http://router.obullo.com/tr/">http://router.obullo.com/tr/</a> bağlantısından ulaşabilirsiniz.