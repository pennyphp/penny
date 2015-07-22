<?php

namespace GianArb\Penny;

use Zend\Diactoros\Response;
use GianArb\Penny\Event\HttpFlowEvent;
use GianArb\Penny\Event\HttpErrorEvent;
use DI\ContainerBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use GianArb\Penny\Config\Loader;

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
            $config = Loader::load();
            $container = $this->buildContainer($config);
        }

        $container->set("http.flow", \DI\object('Zend\EventManager\EventManager'));
        $container->set('dispatcher', \DI\object('GianArb\Penny\Dispatcher')
            ->constructor([$this->router]));
        $container->set('di', $container);
        $this->container = $container;
    }

    private function buildContainer($config)
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        $builder->addDefinitions($config);
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
            $errorEvent = new HttpErrorEvent("ERROR_DISPATCH", $request, $response);
            $errorEvent->setException($e);
            $this->getContainer()->get("http.flow")->trigger($errorEvent);
            return $errorEvent->getResponse();
        }

        $controller = $this->getContainer()->get($routerInfo[1][0]);
        $method = $routerInfo[1][1];
        $function = new \ReflectionClass($controller);
        $name = strtolower($function->getShortName());

        $eventName = "{$name}.{$method}";
        $flowEvent = new HttpFlowEvent($eventName, $request, $response);
        $flowEvent->setRouteInfo($routerInfo);

        $this->getContainer()->get("http.flow")->attach("{$name}.{$method}", function ($flowEvent) use ($controller, $method) {
            $response = call_user_func_array(
                [$controller, $method],
                [$flowEvent->getRequest(),
                $flowEvent->getResponse()]+$flowEvent->getRouteInfo()[2]
            );
            $flowEvent->setResponse($response);
        }, 0);


        try {
            $this->getContainer()->get("http.flow")->trigger($flowEvent);
        } catch (\Exception $exception) {
            $errorEvent = new HttpErrorEvent($eventName."_error", $request, $response);
            $errorEvent->setException($exception);
            $this->getContainer()->get("http.flow")->trigger($errorEvent);
            throw $exception;
        }

        return $flowEvent->getResponse();
    }
}
