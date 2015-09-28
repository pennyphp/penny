<?php

namespace GianArb\PennyTest;

use FastRoute;
use GianArb\Penny\Config\Loader;
use GianArb\Penny\App;
use GianArb\Penny\Exception\MethodNotAllowed;
use GianArb\Penny\Exception\RouteNotFound;
use PHPUnit_Framework_TestCase;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\Uri;

class EventFlowTest extends PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        $config = Loader::load();
        $config['router'] = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/', ['TestApp\Controller\Index', 'index']);
        });

        $this->app = new App(App::buildContainer($config));
    }

    public function testStopEventFlow() {
        $request = (new Request())
        ->withUri(new Uri('/'))
        ->withMethod('GET');
        $response = new Response();

        $this->app->getContainer()->get('event_manager')->attach('index.index', function ($e) {
            $response = $e->getResponse();
            $response = $response->withStatus(201);
            $e->setResponse($response);
        }, 100);

        $this->app->getContainer()->get('event_manager')->attach('index.index', function ($e) {
            $response = $e->getResponse();
            $response = $response->withStatus(205);
            $e->setResponse($response);
            $e->stopPropagation();
            return $response;
        }, 200);

        $response = $this->app->run($request, $response);
        $this->assertEquals(205, $response->getStatusCode());
    }
}
