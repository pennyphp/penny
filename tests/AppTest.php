<?php

namespace PennyTest;

use FastRoute;
use Interop\Container\ContainerInterface;
use Penny\App;
use Penny\Container;
use Penny\Event\EventInterface;
use Penny\Event\EventManagerInterface;
use Penny\Exception\MethodNotAllowedException;
use Penny\Exception\RouteNotFoundException;
use Penny\Config\Loader;
use PHPUnit_Framework_TestCase;
use TestApp\Controller\IndexController;
use stdClass;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response;
use Zend\Diactoros\Uri;
use Zend\EventManager\EventManager;

class AppTest extends PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        $config = Loader::load();
        $config['router'] = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/', [IndexController::class, 'index']);
            $r->addRoute('GET', '/{id:\d+}', [IndexController::class, 'getSingle']);
            $r->addRoute('GET', '/fail', [IndexController::class, 'failed']);
            $r->addRoute('GET', '/dummy', [IndexController::class, 'dummy']);
        });

        $this->app = new App(Container\PHPDiFactory::buildContainer($config));

        $this->app->getContainer()->get('event_manager')->attach('dispatch_error', function ($e) {
            if ($e->getException() instanceof RouteNotFoundException) {
                $response = $e->getResponse()->withStatus(404);
                $e->setResponse($response);
            }

            if (405 == $e->getException() instanceof MethodNotAllowedException) {
                $response = $e->getResponse()->withStatus(405);
                $e->setResponse($response);
            }
        });
    }

    public function testContainerInstanceOfInteropContainerInterface()
    {
        $this->assertInstanceOf('Interop\Container\ContainerInterface', $this->app->getContainer());
    }

    public function testCustomPathRole()
    {
        $app = new App(null, __DIR__ . '/CustomConfig/{{*}}{{,*.local}}.php');
        $this->assertTrue($app->getContainer()->has('router'));
    }

    public function testAppWithContainerThatDoesnotHasRouter()
    {
        $this->setExpectedException('Exception', 'Define router config');

        $container = $this->prophesize('Interop\Container\ContainerInterface');
        $container->has('router')->willReturn(false)->shouldBeCalled();
        $app = new App($container->reveal());
    }

    public function testChangeResponseStatusCode()
    {
        $request = (new ServerRequest())
        ->withUri(new Uri('/fail'))
        ->withMethod('GET');
        $response = new Response();

        $response = $this->app->run($request, $response);
        $this->assertEquals(502, $response->getStatusCode());
    }

    public function testRouteFound()
    {
        $request = (new ServerRequest())
        ->withUri(new Uri('/'))
        ->withMethod('GET');
        $response = new Response();

        $response = $this->app->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRouteParamIntoTheSignatureofMethod()
    {
        $request = (new ServerRequest())
        ->withUri(new Uri('/10'))
        ->withMethod('GET');
        $response = new Response();

        $response = $this->app->run($request, $response);
        $this->assertRegExp('/id=10/', $response->getBody()->__toString());
    }

    public function testEventPostExecuted()
    {
        $request = (new ServerRequest())
        ->withUri(new Uri('/'))
        ->withMethod('GET');
        $response = new Response();

        $this->app->getContainer()->get('event_manager')->attach(IndexController::class.'.index', function ($e) {
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
        $request = (new ServerRequest())
        ->withUri(new Uri('/'))
        ->withMethod('GET');
        $response = new Response();

        $this->app->getContainer()->get('event_manager')->attach(IndexController::class.'.index', function ($e) {
            $response = $e->getResponse();
            $response->getBody()->write('This is');
            $e->setResponse($response);
        }, 10);

        $response = $this->app->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertRegExp('/This is a beautiful/', $response->getBody()->__toString());
    }

    public function testEventPreThrowExceptionIsTrigger()
    {
        $this->setExpectedException('InvalidArgumentException');
        $request = (new ServerRequest())
        ->withUri(new Uri('/dummy'))
        ->withMethod('GET');
        $response = new Response();
        $count = 0;

        $this->app->getContainer()->get('event_manager')->attach(IndexController::class.'.dummy_error', function ($e) use (&$count) {
            $count = &$count + 1;
            throw $e->getException();
        }, 10);

        $response = $this->app->run($request, $response);
    }

    public function testRouteNotFound()
    {
        $request = (new ServerRequest())
        ->withUri(new Uri('/doh'))
        ->withMethod('GET');
        $response = new Response();

        $response = $this->app->run($request, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testMethodNotAllowed()
    {
        $request = (new ServerRequest())
        ->withUri(new Uri('/'))
        ->withMethod('POST');
        $response = new Response();

        $response = $this->app->run($request, $response);
        $this->assertEquals(405, $response->getStatusCode());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testDispatcherShouldBeCallable()
    {
        $request = (new ServerRequest())
        ->withUri(new Uri('/'))
        ->withMethod('POST');
        $response = new Response();

        $config = Loader::load();
        $config['router'] = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/', [IndexController::class, 'index']);
        });
        $config['dispatcher'] = new \StdClass();

        $app = new App(Container\PHPDiFactory::buildContainer($config));
        $app->run($request, $response);
    }

    public function testWithInternalContainerFactory()
    {
        chdir(__DIR__.'/app');
        $app = new App();

        $request = (new ServerRequest())
        ->withUri(new Uri('/'))
        ->withMethod('GET');
        $response = new Response();

        $response = $app->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testHttpFlowEventNotInstanceOfEventInterface()
    {
        $this->setExpectedException('RuntimeException');

        chdir(__DIR__.'/app');
        $app = new App();

        $request = (new ServerRequest())
        ->withUri(new Uri('/'))
        ->withMethod('GET');
        $response = new Response();

        $app->getContainer()->set('http_flow_event', new stdClass());
        $response = $app->run($request, $response);
    }

    public function testRouteInfoNotInstanceOfRouteInfoInterface()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $request = (new ServerRequest())
        ->withUri(new Uri('/'))
        ->withMethod('GET');
        $response = new Response();

        $dispatcher = function () use ($request) {
            return 'callback';
        };
        $httpFlowEvent = $this->prophesize(EventInterface::class);
        $eventManager = $this->prophesize(EventManagerInterface::class);

        $container->has('router')->willReturn(true);
        $container->get('http_flow_event')->willReturn($httpFlowEvent);
        $container->get('dispatcher')->willReturn($dispatcher);
        $container->get('event_manager')->willReturn($eventManager);

        $app = new App($container->reveal());
        $response = $app->run($request, $response);
    }

    public function testBootstrapEventTriggered()
    {
        $config = Loader::load();
        $config['router'] = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/', [IndexController::class, 'index']);
        });
        $this->app = new App(Container\PHPDiFactory::buildContainer($config));
        $this->app->getContainer()
                  ->get('event_manager')
                  ->attach('bootstrap', function() {
                       echo 'bootstrap triggered';
                  });

        ob_start();
        $this->app->run();
        $content = ob_get_clean();

        $this->assertEquals('bootstrap triggered', $content);
    }
}
