<?php

namespace GianArb\Groot;

use Zend\Diactoros\Response;

class App
{
    private $router;
    private $request;
    private $response;
    private $container;
    private $routeInfo;

    public function __construct($router)
    {
        $this->router = $router;
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse($r)
    {
        $this->response = $r;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getRouteInfo()
    {
        return $this->routeInfo;
    }

    public function setRouteInfo($r)
    {
        $this->routeInfo = $r;
    }

    public function run($request, $response)
    {
        $this->request = $request;
        $this->response = $response;

        $dispatched = $this->getContainer()->get("dispatcher")->dispatch($this->request, $this);

        if($dispatched->getResponse() instanceof Response && $dispatched->getRouteInfo() == null){
            return $dispatched->getResponse();
        }

        $controller = $this->getContainer()->get($dispatched->getRouteInfo()[1][0]);
        $dispatched->response = call_user_func([$controller, $dispatched->getRouteInfo()[1][1]], $this->request, new Response());

        if (!$this->getResponse() instanceof Response) {
            throw \Exception("dead");
        }

        return $dispatched->getResponse();
    }
}
