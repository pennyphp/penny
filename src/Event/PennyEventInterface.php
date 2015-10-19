<?php
namespace Penny\Event;

use Exception;
use Penny\Route\RouteInfoInterface;
use Zend\EventManager\EventInterface;

interface PennyEventInterface extends EventInterface
{
    public function __construct($name, $request, $response);
    public function setResponse($response);
    public function getResponse();
    public function setRequest($request);
    public function getRequest();
    public function getRouteInfo();
    public function setRouteInfo(RouteInfoInterface $routerInfo);
    public function setException(Exception $exception);
    public function getException();
}
