<?php
namespace GianArb\PennyTest\Http;

use GianArb\Penny\App;
use GianArb\PennyTest\Utils\FastSymfonyDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SymfonyKernelTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        $router = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/', [get_class($this), 'index']);
        });

        $this->app = new App($router);
        $dispatcher = new FastSymfonyDispatcher($router);
        $this->app->getContainer()->set("dispatcher", $dispatcher);
    }

    public function testRunErrorReturnSameHttpObjects()
    {
        $requestTest = null;
        $responseTest = null;

        $this->app->getContainer()->get("event_manager")->attach("symfonykerneltest.index_error", function ($e) use (&$requestTest, &$responseTest) {
            $requestTest = $e->getRequest();
            $responseTest = $e->getResponse();
        });

        $request = Request::create("/", "GET");
        $response = new Response();
        $this->app->run($request, $response);
        $this->assertSame($request, $requestTest);
        $this->assertSame($response, $responseTest);
    }
}
