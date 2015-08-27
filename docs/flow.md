# Application Flow

This is the flowchart of penny. It is a middleware based on event. There is only one event that turns into the application flow.  
Dispather try to match router and request, if this metch exists return the result but if there are a problem trigger an event `ERROR_DISPATH`.  
There are two possibile problems:
* Route doesn't exist, penny return an exception instance of `GianArb\Penny\Exception\RouteNotFound`
* Route exists but the HTTP Method is not metched, penny return an exception instance of `GianArb\Penny\Exception\MethodNotAllowed`
After this event penny returns a response.

If route is matched there is a callable to call for this example IndexController, index function.
```php
$r->addRoute('GET', '/', ['PennyApp\Controller\IndexController', 'index']);
```
The system triggers an event called `indexcontroller.index` an with priority zero exec a index function.
All listeners attached before and next it run correcly the framework returns response,
if there is an exception it triggers the last event `indexcontroller.index_error`.

The basically way to manage all exceptions is:

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
