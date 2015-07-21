<?php

namespace GianArb\Penny;

use Zend\Diactoros\Response;
use GianArb\Penny\Event\HttpFlowEvent;
use DI\ContainerBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

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
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        return $builder->build();
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function run(RequestInterface $request = null, ResponseInterface $response = null)
    {

        if ($request == null) {
            $request = $this->request;
        }
        if ($response == null) {
            $response = $this->response;
        }

        try {
            $routerInfo = $this->getContainer()->get("dispatcher")
                ->dispatch($request);
        } catch (\Exception $e) {
            if (404 === $e->getCode()) {
                $evt = new HttpFlowEvent("ROUTE_NOT_FOUND");
                $evt->setRequest($request);
                $evt->setResponse($response);
                $this->getContainer()->get("http.flow")->trigger($evt);
            }
            if (405 === $e->getCode()) {
                $evt = new HttpFlowEvent("METHOD_NOT_ALLOWED");
                $evt->setRequest($request);
                $evt->setResponse($response);
                $this->getContainer()->get("http.flow")->trigger($evt);
            }
            return $evt->getResponse();
        }

        $controller = $this->getContainer()->get($routerInfo[1][0]);
        $method = $routerInfo[1][1];
        $function = new \ReflectionClass($controller);
        $name = strtolower($function->getShortName());

        $evt = new HttpFlowEvent("{$name}.{$method}");
        $evt->setRequest($request);
        $evt->setResponse($response);
        $evt->setRouteInfo($routerInfo);

        $this->getContainer()->get("http.flow")->attach("{$name}.{$method}", function ($evt) use ($controller, $method) {
            $response = call_user_func_array(
                [$controller, $method],
                [$evt->getRequest(),
                $evt->getResponse()]+$evt->getRouteInfo()[2]
            );
            $evt->setResponse($response);
        }, 0);


        $this->getContainer()->get("http.flow")->trigger($evt);

        if (!$evt->getResponse() instanceof Response) {
            throw new \Exception("dead");
        }

        return $evt->getResponse();
    }
}
