# The Dispatcher concept

## HTTP Libraries

By "HTTP implementation" I mean: *a layer that helps you to work with Request and Response in terms of reading a request, create a response and send it back to the client.*

In PHP there are a lot of libraries that do that:

* [Zend\Http](https://github.com/zendframework/zend-http)
* [Zend\Diactoros](https://github.com/zendframework/zend-diactoros)
* [Symfony\HttpFoundation](https://github.com/symfony/HttpFoundation)
* [guzzle/psr7](https://github.com/guzzle/psr7)

## Dispatcher

The [Dispatcher](https://github.com/gianarb/penny/blob/master/src/Dispatcher.php) (click link to show current implementation), in penny represents the link between: router,
request and response.

The default Penny Dispatcher implementation uses `Zend\Diactoros`. You can write your own dispatcher that makes use of your favorite HTTP library 

Main advantages gained by using `Zend\Diactoros` are:
* It is supported by the Zend Framework community
* It follows PSR-7 standard. [(what is PSR-7?)](http://www.php-fig.org/psr/psr-7/)

## Penny, FastRouter and Symfony\HttpFoundation
Here we are going to see how to write a dispatcher to use with the `Symfony\HttpFoundation` component.

1. Install it.

```
composer require symfony/http-foundation
```

2. Write your dispatcher that uses the `HttpFoundation\Request`

```php
<?php

namespace YourApp\Dispatcher;

use Symfony\Component\HttpFoundation\Request;

class FastSymfonyDispatcher
{
    private $router;

    public function __construct($router)
    {
        $this->router = $router;
    }

    public function dispatch(Request $request)
    {
        $routeInfo = $this->router->dispatch($request->getMethod(), $request->getPathInfo());
        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                throw new \GianArb\Penny\Exception\RouteNotFound();
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                throw new \GianArb\Penny\Exception\MethodNotAllowed();
                break;
            case \FastRoute\Dispatcher::FOUND:
                return $routeInfo;
                break;
            default:
                throw new \Exception(null, 500);
                break;
        }
    }
}

```

3. Create custom endpoint that consume `Symfony\Component\HttpFoundation\Request` and `Response`

```php
<?php
use GianArb\Penny\App;
use YourApp\Dispatcher\FastSymfonyDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$this->app = new App($router);
$dispatcher = new FastSymfonyDispatcher($router);
$this->app->getContainer()->set("dispatcher", $dispatcher);
$this->app->run($request, $response);
```

Now your application runs using the  `Symfony\HttpFoundation` instead of `Zend\Diactoros`.

