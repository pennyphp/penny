<?php

namespace GianArb\Groot;

use Zend\Diactoros\Response;
use GianArb\Groot\Event\HttpFlowEvent;

class App
{
    private $router;
    private $container;

    public function __construct($router)
    {
        $this->router = $router;
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function run($request, $response)
    {
        $evt = new HttpFlowEvent();
        $evt->setRequest($request);
        $evt->setResponse($response);

        try {
            $this->getContainer()->get("dispatcher")
                ->dispatch($request, $evt);
        } catch (\Exception $e) {
            if (404 === $e->getCode()) {
                $this->getContainer()->get("http.flow")
                    ->trigger("ROUTE_NOT_FOUND", $evt);
            }
            if (405 === $e->getCode()) {
                $this->getContainer()->get("http.flow")
                    ->trigger("METHOD_NOT_ALLOWED", $evt);
            }
        }

        if($evt->getResponse() instanceof Response && $evt->getRouteInfo() == null){
            return $evt->getResponse();
        }

        $controller = $this->getContainer()->get($evt->getRouteInfo()[1][0]);
        $evt->setResponse(
            call_user_func([$controller, $evt->getRouteInfo()[1][1]], $evt->getRequest(), $evt->getResponse())
        );

        if (!$evt->getResponse() instanceof Response) {
            throw \Exception("dead");
        }

        return $evt->getResponse();
    }
}
