# Event Manager Introduction

Penny provides an [`EventManagerInterface`](https://github.com/pennyphp/penny/blob/master/src/Event/EventManagerInterface.php) that has 2 methods :

```php
public function trigger(EventInterface $event);
public function attach($eventName, callable $listener);
```

The trigger will execute an event based on registered listeners in our Event Manager, Our Event must implement [`EventInterface`](https://github.com/pennyphp/penny/blob/master/src/Event/EventInterface.php) that has following methods:

```php
public function getName();
public function setName($name);
public function setResponse($response);
public function getResponse();
public function setRequest($request);
public function getRequest();
public function getRouteInfo();
public function setRouteInfo(RouteInfoInterface $routerInfo);
public function setException(Exception $exception);
public function getException();
public function stopPropagation($flag = true);
```

If we want to uses our own Event Manager implementation in Penny App, there is a proxy provided by Penny for `Zend's EventManager`, named [`ZendEvmProxy`](https://github.com/pennyphp/penny/blob/master/src/Event/ZendEvmProxy.php) for sample that we can follow.
