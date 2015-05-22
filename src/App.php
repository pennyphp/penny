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

        try {
            $this->getContainer()->get("dispatcher")->dispatch($this->request, $this);
        } catch (\Exception $e) {
            if (404 === $e->getCode()) {
                $this->getContainer()->get("http.flow")
                    ->trigger("ROUTE_NOT_FOUND", $this);
            }
            if (405 === $e->getCode()) {
                $this->getContainer()->get("http.flow")
                    ->trigger("METHOD_NOT_ALLOWED", $this);
            }
        }

        if($this->getResponse() instanceof Response && $this->getRouteInfo() == null){
            return $this->getResponse();
        }

        $controller = $this->getContainer()->get($this->getRouteInfo()[1][0]);
        $this->response = call_user_func([$controller, $this->getRouteInfo()[1][1]], $this->request, new Response());

        if (!$this->getResponse() instanceof Response) {
            throw \Exception("dead");
        }

        return $this->getResponse();
    }
}
