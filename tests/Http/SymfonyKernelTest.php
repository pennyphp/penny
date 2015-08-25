<?php
namespace GianArb\PennyTest\Http;

use GianArb\Penny\App;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SymfonyKernelTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        $router = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/', [SymfonyKernelTest::class, 'index']);
        });

        $this->app = new App($router);
    }

    public function testTrue()
    {
        $this->app->getContainer()->get("http.flow")->attach("ERROR_DISPATCH", function ($e) {
            throw $e->getException();
        });
        $request = Request::create("/", "GET");
        $response = new Response();
        $this->app->run($request, $response);
    }
}
