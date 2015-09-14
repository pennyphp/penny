<?php

namespace TestApp\Controller;

use InvalidArgumentException;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;

class Index
{
    /**
      * @Inject("di")
      */
     private $di;

    public function index(Request $request, Response $response)
    {
        $response->getBody()->write(' a beautiful job');

        return $response;
    }

    public function getSingle(Request $request, Response $response, $id)
    {
        $response->getBody()->write("id=$id");

        return $response;
    }

    public function failed(Request $request, Response $response)
    {
        return $response->withStatus(502);
    }

    public function diTest(Request $request, Response $response)
    {
        $this->di->get('troyan');
        $response->getBody()->write($this->di->get('troyan'));

        return $response;
    }

    public function loadedParams(Request $request, Response $response)
    {
        $response->getBody()->write($this->di->get('fromFile'));

        return $response;
    }

    public function dummy(Request $request, Response $response)
    {
        throw new InvalidArgumentException("it doesn't run");
    }
}
