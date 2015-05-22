<?php
namespace TestApp\Controller;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;

class Index
{
    public function index(Request $request, Response $response)
    {
        return $response;
    }

    public function failed(Request $request, Response $response)
    {
        return $response->withStatus(502);
    }
}
