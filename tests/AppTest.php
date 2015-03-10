<?php
namespace GianArb\GrootTest;

use GianArb\Groot\App;

class AppTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        $loader = require_once __DIR__.'/../vendor/autoload.php';
        $router = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/', ['TestApp\Controller\Index', 'index']);
        });

        $this->app = new App($router);
    }

    public function testRouteFound()
    {
        $request = (new \Phly\Http\Request())
        ->withUri(new \Phly\Http\Uri('http://example.com'))
        ->withMethod("GET");
        $response = new \Phly\Http\Response();

        $this->app->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRouteNotFound()
    {
        $request = (new \Phly\Http\Request())
        ->withUri(new \Phly\Http\Uri('http://example.com/doh'))
        ->withMethod("GET");
        $response = new \Phly\Http\Response();

        $response = $this->app->run($request, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testMethodNotAllowed()
    {
        $request = (new \Phly\Http\Request())
        ->withUri(new \Phly\Http\Uri('http://example.com/'))
        ->withMethod("POST");
        $response = new \Phly\Http\Response();

        $response = $this->app->run($request, $response);
        $this->assertEquals(405, $response->getStatusCode());
    }
}
