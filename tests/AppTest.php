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
            $r->addRoute('GET', '/dummy', ['TestApp\Controller\Index', 'dummy'], [
                "name" => "dummy"
            ]);
        });

        $this->app = new App($router);

        $this->app->getContainer()->get("http.flow")->attach("ERROR_DISPATCH", function ($e) {
        });

        $this->app->getContainer()->get("http.flow")->attach("ERROR_DISPATCH", function ($e) {
            if (404 === $e->getException()->getCode()) {
                $response = $e->getResponse()->withStatus(404);
                $e->setResponse($response);
            }

            if (405 == $e->getException()->getCode()) {
                $response = $e->getResponse()->withStatus(405);
                $e->setResponse($response);
            }
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

        $this->app->getContainer()->get("http.flow")->attach("index.index", function ($e) {
            $response = $e->getResponse();
            $response->getBody()->write("I'm very happy!");
            $e->setResponse($response);
        }, -10);

        $response = $this->app->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertRegExp("/jobI'm very happy!/", $response->getBody()->__toString());
    }

    public function testEventPreExecuted()
    {
        $request = (new \Zend\Diactoros\Request())
        ->withUri(new \Zend\Diactoros\Uri('/'))
        ->withMethod("GET");
        $response = new \Zend\Diactoros\Response();

        $this->app->getContainer()->get("http.flow")->attach("index.index", function ($e) {
            $response = $e->getResponse();
            $response->getBody()->write("This is");
            $e->setResponse($response);
        }, 10);

        $response = $this->app->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertRegExp('/This is a beautiful/', $response->getBody()->__toString());
    }

    public function testEventPreThrowExceptionIsTrigger()
    {
        $this->setExpectedException('InvalidArgumentException');
        $request = (new \Zend\Diactoros\Request())
        ->withUri(new \Zend\Diactoros\Uri('/dummy'))
        ->withMethod("GET");
        $response = new \Zend\Diactoros\Response();
        $count = 0;

        $this->app->getContainer()->get("http.flow")->attach("index.dummy_error", function ($e) use (&$count) {
            $count = &$count +1;
        }, 10);

        $response = $this->app->run($request, $response);
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
