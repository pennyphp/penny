# Dispatcher and custom HTTP implementation

## HTTP implementation
With "PHP implementation" I mean a layer that helps you to work with Request and Response, it helps you to read a request, to write a response and
send it.

In PHP there are a lot of libraries with this purpose:

* [Zend\Http](https://github.com/zendframework/zend-http)
* [Zend\Diactoros](https://github.com/zendframework/zend-diactoros)
* [Symfony\HttpFoundation](https://github.com/symfony/HttpFoundation)
* [guzzle/psr7](https://github.com/guzzle/psr7)

## Penny implementation

At the moment Zend\Diactoros is our default library to manage this topic in penny.

* It is supported by Zend Framework community
* It follows PSR-7 standard. [(what is PSR-7?)](http://www.php-fig.org/psr/psr-7/)

But it follows the same philosophy of other components, we can replace it with your best library.
The core of this process is [Dispatcher](https://github.com/gianarb/penny/blob/master/src/Dispatcher.php) (click link to show current implementation), it is the blob between router and
request and response.

We decided to remove PSR-7 because at the moment it useless, you change our Dispatcher with your implementation to manage different Router or HTTP implementation library.

## Penny, FastRouter and Symfony\HttpFoundation
In this chapter we replace Diactoros with Symfony\HttpFoundation.

1. Install it.

```
composer require symfony/http-foundation
```

2. Write your dispatcher that metch FastRouter route by HttpFoundation\Request
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

3. Create custom endpoint that consum Symfony\Component\HttpFoundation\Request and Response
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

Now your application works with Symfony\HttpFoundation library.

## Why PSR-7?
PSR-7 is a standard promotes by [php-fig](http://www.php-fig.org) groups. This solution was developed
from a strong team of developers that rapresenting most important PHP projects, this standard wants to be
a common layer to increase interoperability between different libraries and framework, we promote it because
it is a good common started point to build interoperable applications.
