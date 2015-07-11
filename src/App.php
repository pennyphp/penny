<?php

namespace GianArb\Penny;

use Zend\Diactoros\Response;
use GianArb\Penny\Event\HttpFlowEvent;
use DI\ContainerBuilder;

class App
{
    private $router;
    private $container;
    private $request;
    private $response;

    public function __construct($router, $container = null)
    {
        $this->router = $router;
        $this->container = $container;

        $this->response = new \Zend\Diactoros\Response();

        $this->request = \Zend\Diactoros\ServerRequestFactory::fromGlobals(
            $_SERVER,
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES
        );

        if ($this->container == null) {
            $this->container = $this->buildContainer();
        }

        $this->container->set("http.flow", \DI\object('Zend\EventManager\EventManager'));
        $this->container->set('dispatcher', \DI\object('GianArb\Penny\Dispatcher')
            ->method("setRouter", [$this->router]));
        $this->container->set('di', $container);
    }

    private function buildContainer()
    {
        $mnapoliDiCBuilder = new ContainerBuilder();
        return $mnapoliDiCBuilder->build();
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function run($request = null, $response = null)
    {
        $evt = new HttpFlowEvent();

        if ($request == null) {
            $request = $this->request;
        }
        if ($response == null) {
            $response = $this->response;
        }

        $evt->setRequest($request);
        $evt->setResponse($response);

        try {
            $this->getContainer()->get("dispatcher")
                ->dispatch($request, $evt);
        } catch (\Exception $e) {
            if (404 === $e->getCode()) {
                $this->getContainer()->get("http.flow")->trigger("ROUTE_NOT_FOUND", $evt);
            }
            if (405 === $e->getCode()) {
                $this->getContainer()->get("http.flow")->trigger("METHOD_NOT_ALLOWED", $evt);
            }
        }

        if ($evt->getResponse() instanceof Response && $evt->getRouteInfo() == null) {
            return $evt->getResponse();
        }

        $controller = $this->getContainer()->get($evt->getRouteInfo()[1][0]);
        $method = $evt->getRouteInfo()[1][1];
        $function = new \ReflectionClass($controller);
        $name = strtolower($function->getShortName());

        $this->getContainer()->get("http.flow")->trigger("{$name}.{$method}.pre", $evt);
        $evt->setResponse(call_user_func_array(
            [$controller, $method],
            [$evt->getRequest(),
            $evt->getResponse()]+$evt->getRouteInfo()[2]
        ));
        $this->getContainer()->get("http.flow")->trigger("{$name}.{$method}.post", $evt);

        if (!$evt->getResponse() instanceof Response) {
            throw new \Exception("dead");
        }

        return $evt->getResponse();
    }
}
