<?php

namespace PennyTest;

use FastRoute;
use Penny\App;
use Penny\Container;
use Penny\Exception\MethodNotAllowed;
use Penny\Exception\RouteNotFound;
use Penny\Config\Loader;
use PHPUnit_Framework_TestCase;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\Uri;

class AppTest extends PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        $config = Loader::load();
        $config['router'] = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/', ['TestApp\Controller\Index', 'index']);
            $r->addRoute('GET', '/{id:\d+}', ['TestApp\Controller\Index', 'getSingle']);
            $r->addRoute('GET', '/fail', ['TestApp\Controller\Index', 'failed']);
            $r->addRoute('GET', '/dummy', ['TestApp\Controller\Index', 'dummy']);
        });

        $this->app = new App(Container\PHPDiFactory::buildContainer($config));

        $this->app->getContainer()->get('event_manager')->attach('ERROR_DISPATCH', function ($e) {
            if ($e->getException() instanceof RouteNotFound) {
                $response = $e->getResponse()->withStatus(404);
                $e->setResponse($response);
            }

            if (405 == $e->getException() instanceof MethodNotAllowed) {
                $response = $e->getResponse()->withStatus(405);
                $e->setResponse($response);
            }
        });
    }

    public function testContainerInstanceOfInteropContainerInterface()
    {
        $this->assertInstanceOf('Interop\Container\ContainerInterface', $this->app->getContainer());
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
        $request = (new Request())
        ->withUri(new Uri('/fail'))
        ->withMethod('GET');
        $response = new Response();

        $response = $this->app->run($request, $response);
        $this->assertEquals(502, $response->getStatusCode());
    }

    public function testRouteFound()
    {
        $request = (new Request())
        ->withUri(new Uri('/'))
        ->withMethod('GET');
        $response = new Response();

        $response = $this->app->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRouteParamIntoTheSignatureofMethod()
    {
        $request = (new Request())
        ->withUri(new Uri('/10'))
        ->withMethod('GET');
        $response = new Response();

        $response = $this->app->run($request, $response);
        $this->assertRegExp('/id=10/', $response->getBody()->__toString());
    }

    public function testEventPostExecuted()
    {
        $request = (new Request())
        ->withUri(new Uri('/'))
        ->withMethod('GET');
        $response = new Response();

        $this->app->getContainer()->get('event_manager')->attach('index.index', function ($e) {
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
        $request = (new Request())
        ->withUri(new Uri('/'))
        ->withMethod('GET');
        $response = new Response();

        $this->app->getContainer()->get('event_manager')->attach('index.index', function ($e) {
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
        $request = (new Request())
        ->withUri(new Uri('/dummy'))
        ->withMethod('GET');
        $response = new Response();
        $count = 0;

        $this->app->getContainer()->get('event_manager')->attach('index.dummy_error', function ($e) use (&$count) {
            $count = &$count + 1;
            throw $e->getException();
        }, 10);

        $response = $this->app->run($request, $response);
    }

    public function testRouteNotFound()
    {
        $request = (new Request())
        ->withUri(new Uri('/doh'))
        ->withMethod('GET');
        $response = new Response();

        $response = $this->app->run($request, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testMethodNotAllowed()
    {
        $request = (new Request())
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
        $request = (new Request())
        ->withUri(new Uri('/'))
        ->withMethod('POST');
        $response = new Response();

        $config = Loader::load();
        $config['router'] = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/', ['TestApp\Controller\Index', 'index']);
        });
        $config['dispatcher'] = new \StdClass();

        $app = new App(Container\PHPDiFactory::buildContainer($config));
        $app->run($request, $response);
    }

    public function testWithInternalContainerFactory()
    {
        chdir(__DIR__.'/app');
        $app = new App();

        $request = (new Request())
        ->withUri(new Uri('/'))
        ->withMethod('GET');
        $response = new Response();

        $response = $app->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
