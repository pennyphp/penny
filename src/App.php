<?php

namespace GianArb\Groot;

class App
{
    private $router;
    private $request;
    private $response;

    public function __construct($router)
    {
        $this->router = $router;
    }

    public function run($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
        $routeInfo = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());

        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                 $this->response = $response->withStatus(404);
            break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $this->response = $response->withStatus(405);
            break;
            case \FastRoute\Dispatcher::FOUND:
                $this->response = $response->withStatus(200);

                $controller = new $routeInfo[1][0];
                $controller->$routeInfo[1][1]();
            break;
        }
        return $this->response;
    }
}
