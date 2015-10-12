<?php
namespace Penny\Event;

use Exception;
use Zend\EventManager\EventInterface;

interface PennyEventInterface extends EventInterface
{
    public function __construct($name, $request, $response);
    public function getResponse();
    public function getRequest();
    public function getRouteInfo();
    public function setRouteInfo($routerInfo);
    public function setException(Exception $exception);
    public function getException();
}
