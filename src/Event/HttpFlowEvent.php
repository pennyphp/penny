<?php
namespace GianArb\Penny\Event;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\EventManager\Event;

class HttpFlowEvent extends Event
{
    private $request;
    private $response;
    private $exception;
    private $routeInfo = [];

    public function __construct($name, $request, $response)
    {
        $this->name = $name;
        $this->response = $response;
        $this->request = $request;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function setRequest(RequestInterface $request)
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

    public function setRouteInfo(array $routerInfo)
    {
        $this->routeInfo = $routerInfo;
    }


    public function setException(\Exception $exception)
    {
        $this->exception = $exception;
    }

    public function getException()
    {
        return $this->exception;
    }
}
