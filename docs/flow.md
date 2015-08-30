# Application Flow

![Penny Framework Flow](https://raw.githubusercontent.com/gianarb/penny/db53c546d9ac0cb24fdd352e487a24ae3fe14469/docs/assets/img/event_flow.png)

This is the Penny's flowchart.
It is a event-based middleware. There is only one main event that turns into the application flow.  

Dispatcher tries to match router and request, if this metch exists it returns the result, if not, or in case of problems it triggers an `ERROR_DISPATCH` event.

There are two possibile kind of problems:

* Route doesn't exist, an `GianArb\Penny\Exception\RouteNotFound` Exception is thrown;
* Route exists but the HTTP Method hasn't been matched, an `GianArb\Penny\Exception\MethodNotAllowed` Exception is thrown;

If no exception are thrown, a response is returned back.

If a route matches, the corresponding callback is invoked, in this case the callable is the `PennyApp\Controller\IndexController`'s  `index` method.

```php
$r->addRoute('GET', '/', ['PennyApp\Controller\IndexController', 'index']);
```

At this point the system triggers an event called `indexcontroller.index` with zero priority and execute the route callback.

All listeners attached after and before it will be called correcly until the framework returns response,
if an exception is thrown it will trigger an event named `indexcontroller.index_error`.

The most common way to manage all exceptions is:

```php
<?php
use DI\ContainerBuilder;

chdir(dirname(__DIR__));

require_once "./vendor/autoload.php";

$app = new \GianArb\Penny\App();

$app->getContainer()->get("http.flow")->attach("*", function ($event) {
    $e = $event->getException();
    if ($e instanceof Exception) {
        throw $e;
    }
});

$emitter = new \Zend\Diactoros\Response\SapiEmitter();
$emitter->emit($app->run());
```
