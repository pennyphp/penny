<?php
namespace GianArb\GrootTest;

use GianArb\Groot\App;
use DI\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyDiCBuilder;
use Acclimate\Container\CompositeContainer;
use Acclimate\Container\ContainerAcclimator;

class AppTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        $router = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/', ['TestApp\Controller\Index', 'index']);
            $r->addRoute('GET', '/fail', ['TestApp\Controller\Index', 'failed']);
        });

        $this->app = new App($router);
        $acclimate = new ContainerAcclimator();

        $mnapoliDiCBuilder = new ContainerBuilder();
        $mnapoliDiC = $acclimate->acclimate($mnapoliDiCBuilder->build());
        $symfonyDiC = new SymfonyDiCBuilder();
        $syAcclimate = $acclimate->acclimate($symfonyDiC);
        $container = new CompositeContainer([$syAcclimate, $mnapoliDiC]);
        $symfonyDiC->set("http.flow", new \Zend\EventManager\EventManager());
        $symfonyDiC->register('dispatcher', "GianArb\\Groot\\Dispatcher")
            ->addMethodCall('setRouter', [$router]);
        $mnapoliDiC->set('di', $container);
        $this->app->setContainer($container);

        $this->app->getContainer()->get("http.flow")->attach("ROUTE_NOT_FOUND", function($e){
            $response = $e->getTarget()->getResponse()->withStatus(404);
            $e->getTarget()->setResponse($response);
        });

        $this->app->getContainer()->get("http.flow")->attach("METHOD_NOT_ALLOWED", function($e){
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
