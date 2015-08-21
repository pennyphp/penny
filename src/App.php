<?php

namespace GianArb\Penny;

use Zend\Diactoros\Response;
use GianArb\Penny\Event\HttpFlowEvent;
use DI\ContainerBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use GianArb\Penny\Config\Loader;

class App
{
    private $container;
    private $request;
    private $response;

    public function __construct($router = null, $container = null)
    {
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

        if ($router == null && $container->has("router") == false) {
            throw new \Exception("Define router config");
            $container->set("router", $config['router']);
        } elseif ($container->has("router") == false) {
            $container->set("router", $router);
        }

        $container->set("http.flow", \DI\object('Zend\EventManager\EventManager'));
        $container->set('dispatcher', \DI\object('GianArb\Penny\Dispatcher')
            ->constructor($container->get("router")));
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
            $errorEvent = new HttpFlowEvent("ERROR_DISPATCH", $request, $response);
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
            $errorEvent = new HttpFlowEvent($eventName."_error", $request, $response);
            $errorEvent->setException($exception);
            $this->getContainer()->get("http.flow")->trigger($errorEvent);
        }

        return $flowEvent->getResponse();
    }
}
