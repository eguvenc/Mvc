
## Olaylar

Olay sınıfı uygulama içerisinde olaylar ilan edip ve bu olayları bağımsız olarak belirlediğiniz dinleyici sınıflar içerisinden yönetmenizi sağlar. Çerçeve içerisinde olay paketi harici olarak kullanılır ve bunun için `Zend/EventManager` tercih edilmiştir.

### Olay servisi

```php
$container->setFactory('events', 'Services\EventManagerFactory');
```

### Olay yaratmak

Trigger fonksiyonu olayları `başlatmayı` ve dinleyicilere `veri` göndermeyi sağlar.

    
```php
$result = $events->trigger('route.builder', $this, ['context' => $context]);
```

if (! in_array($request->getMethod(), $methods)) {
    
    $events->trigger('http.method.notAllowed', null, $methods);
    $result = $events->trigger('http.method.notAllowed.message', null, $methods);
    $message = $result->last();

    $error = new Error(
        '405',
        $message,
        ['Allow' => implode(', ', $methods)]
    );
    return $handler->process($error);
    
}