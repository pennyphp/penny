<?php
namespace Penny\Event;

use Exception;
use Penny\Route\RouteInfoInterface;

interface EventInterface
{
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
}
