<?php

namespace GianArb\Penny;

use Zend\Diactoros\Response;
use GianArb\Penny\Event\HttpFlowEvent;
use DI\ContainerBuilder;
use Acclimate\Container\CompositeContainer;
use Acclimate\Container\ContainerAcclimator;

class App
{
    private $router;
    private $container;

    public function __construct($router, $container = null)
    {
        $this->router = $router;

        if ($container == null) {
            $this->buildContainer();
        }

    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function buildContainer()
    {
        $acclimate = new ContainerAcclimator();
        $mnapoliDiCBuilder = new ContainerBuilder();
        $mnapoliDiC = $acclimate->acclimate($mnapoliDiCBuilder->build());
        $container = new CompositeContainer([$mnapoliDiC]);
        $mnapoliDiC->set("http.flow", \DI\object('Zend\EventManager\EventManager'));
        $mnapoliDiC->set('dispatcher', \DI\object('GianArb\Penny\Dispatcher')
            ->method("setRouter", [$this->router]));
        $mnapoliDiC->set('di', $container);
        $this->setContainer($container);
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
        $evt->setResponse(call_user_func([$controller, $method], $evt->getRequest(), $evt->getResponse()));
        $this->getContainer()->get("http.flow")->trigger("{$name}.{$method}.post", $evt);

        if (!$evt->getResponse() instanceof Response) {
            throw new \Exception("dead");
        }

        return $evt->getResponse();
    }
}
