<?php
namespace GianArb\Groot\Event;

class HttpFlowEvent
{
    private $request;
    private $response;
    private $routeInfo;

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse($r)
    {
        $this->response = $r;
    }

    public function setRequest($r)
    {
        $this->request = $r;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getRouteInfo()
    {
        return $this->routeInfo;
    }

    public function setRouteInfo($r)
    {
        $this->routeInfo = $r;
    }
}
