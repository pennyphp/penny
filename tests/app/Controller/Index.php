<?php
namespace TestApp\Controller;
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
        return $response;
    }

    public function failed(Request $request, Response $response)
    {
        return $response->withStatus(502);
    }

    public function diTest(Request $request, Response $response)
    {
        $this->di->get("troyan");
        return $response;
    }
}
