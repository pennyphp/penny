<?php

namespace PennyTest\Http;

use Penny\App;
use Penny\Container;
use Penny\Config\Loader;
use FastRoute;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SymfonyKernelTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        $config = Loader::load();
        $config['router'] = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/', [get_class($this), 'index']);
        });
        $config['dispatcher'] = \Di\object('PennyTest\Utils\FastSymfonyDispatcher')
            ->constructor(\Di\get('router'), \Di\get('di'));

        $this->app = new App(Container\PHPDiFactory::buildContainer($config));
    }

    public function testRunErrorReturnSameHttpObjects()
    {
        $requestTest = null;
        $responseTest = null;

        $this->app->getContainer()->get('event_manager')->attach('ERROR_DISPATCH', function ($e) use (&$requestTest, &$responseTest) {
            $requestTest = $e->getRequest();
            $responseTest = $e->getResponse();
        });

        $request = Request::create('/', 'GET');
        $response = new Response();
        $this->app->run($request, $response);
        $this->assertSame($request, $requestTest);
        $this->assertSame($response, $responseTest);
    }
}
