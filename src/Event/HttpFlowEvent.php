<?php
namespace GianArb\Penny\Event;

use Zend\EventManager\Event;

class HttpFlowEvent extends Event
{

    private $request;
    private $response;
    private $routeInfo;

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getRouteInfo()
    {
        return $this->routeInfo;
    }

    public function setRouteInfo($routerInfo)
    {
        $this->routeInfo = $routerInfo;
    }
}
