<?php

namespace TestApp\Controller;

use InvalidArgumentException;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response;

class IndexController
{
    /**
      * @Inject("di")
      */
     private $di;

    public function index(ServerRequest $request, Response $response)
    {
        $response->getBody()->write(' a beautiful job');

        return $response;
    }

    public function getSingle(ServerRequest $request, Response $response, $id)
    {
        $response->getBody()->write("id=$id");

        return $response;
    }

    public function failed(ServerRequest $request, Response $response)
    {
        return $response->withStatus(502);
    }

    public function diTest(ServerRequest $request, Response $response)
    {
        $this->di->get('troyan');
        $response->getBody()->write($this->di->get('troyan'));

        return $response;
    }

    public function loadedParams(ServerRequest $request, Response $response)
    {
        $response->getBody()->write($this->di->get('fromFile'));

        return $response;
    }

    public function dummy(ServerRequest $request, Response $response)
    {
        throw new InvalidArgumentException("it doesn't run");
    }
}
