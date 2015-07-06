<?php
namespace GianArb\PennyTest;

use GianArb\Penny\App;

class AppTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        $router = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/', ['TestApp\Controller\Index', 'index'], [
                "name" => "index"
            ]);
            $r->addRoute('GET', '/fail', ['TestApp\Controller\Index', 'failed'], [
                "name" => "fail"
            ]);
        });

        $this->app = new App($router);

        $this->app->getContainer()->get("http.flow")->attach("ROUTE_NOT_FOUND", function ($e) {
            $response = $e->getTarget()->getResponse()->withStatus(404);
            $e->getTarget()->setResponse($response);
        });

        $this->app->getContainer()->get("http.flow")->attach("METHOD_NOT_ALLOWED", function ($e) {
            $response = $e->getTarget()->getResponse()->withStatus(405);
            $e->getTarget()->setResponse($response);
        });

    }

    public function testChangeResponseStatusCode()
    {
        $request = (new \Zend\Diactoros\Request())
        ->withUri(new \Zend\Diactoros\Uri('/fail'))
        ->withMethod("GET");
        $response = new \Zend\Diactoros\Response();

        $response = $this->app->run($request, $response);
        $this->assertEquals(502, $response->getStatusCode());
    }

    public function testRouteFound()
    {
        $request = (new \Zend\Diactoros\Request())
        ->withUri(new \Zend\Diactoros\Uri('/'))
        ->withMethod("GET");
        $response = new \Zend\Diactoros\Response();

        $response = $this->app->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEventPostExecuted()
    {
        $request = (new \Zend\Diactoros\Request())
        ->withUri(new \Zend\Diactoros\Uri('/'))
        ->withMethod("GET");
        $response = new \Zend\Diactoros\Response();

        $this->app->getContainer()->get("http.flow")->attach("index.index.post", function ($e) {
            $response = $e->getTarget()->getResponse();
            $response->getBody()->write("I'm very happy!");
            $e->getTarget()->setResponse($response);
        });

        $response = $this->app->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertRegExp('/very happy/', $response->getBody()->__toString());
    }

    public function testEventPreExecuted()
    {
        $request = (new \Zend\Diactoros\Request())
        ->withUri(new \Zend\Diactoros\Uri('/'))
        ->withMethod("GET");
        $response = new \Zend\Diactoros\Response();

        $this->app->getContainer()->get("http.flow")->attach("index.index.pre", function ($e) {
            $response = $e->getTarget()->getResponse();
            $response->getBody()->write("This is");
            $e->getTarget()->setResponse($response);
        });

        $response = $this->app->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertRegExp('/This is a beautiful/', $response->getBody()->__toString());
    }

    public function testRouteNotFound()
    {
        $request = (new \Zend\Diactoros\Request())
        ->withUri(new \Zend\Diactoros\Uri('/doh'))
        ->withMethod("GET");
        $response = new \Zend\Diactoros\Response();

        $response = $this->app->run($request, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testMethodNotAllowed()
    {
        $request = (new \Zend\Diactoros\Request())
        ->withUri(new \Zend\Diactoros\Uri('/'))
        ->withMethod("POST");
        $response = new \Zend\Diactoros\Response();

        $response = $this->app->run($request, $response);
        $this->assertEquals(405, $response->getStatusCode());
    }
}
