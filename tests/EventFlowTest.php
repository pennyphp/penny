<?php

namespace PennyTest;

use FastRoute;
use Penny\Config\Loader;
use Penny\App;
use Penny\Container;
use PHPUnit_Framework_TestCase;
use TestApp\Controller\IndexController;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response;
use Zend\Diactoros\Uri;

class EventFlowTest extends PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        $config = Loader::load();
        $config['router'] = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/', [IndexController::class, 'index']);
        });

        $this->app = new App(Container\PHPDiFactory::buildContainer($config));
    }

    public function testStopEventFlow()
    {
        $request = (new ServerRequest())
        ->withUri(new Uri('/'))
        ->withMethod('GET');
        $response = new Response();

        $this->app->getContainer()->get('event_manager')->attach(IndexController::class.'.index', function ($e) {
            $response = $e->getResponse();
            $response = $response->withStatus(201);
            $e->setResponse($response);
        }, 100);

        $this->app->getContainer()->get('event_manager')->attach(IndexController::class.'.index', function ($e) {
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
