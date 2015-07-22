<?php
namespace GianArb\PennyTest;

use GianArb\Penny\App;
use DI\ContainerBuilder;
use TestApp\Controller\Index;
use Zend\EventManager\EventManager;

class DiTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        $router = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $router) {
            $router->addRoute('GET', '/', ['TestApp\Controller\Index', 'index']);
            $router->addRoute('GET', '/fail', ['TestApp\Controller\Index', 'failed']);
        });


        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        $mnapoliDiC = $builder->build();

        $container = $this->getMockBuilder(get_class($mnapoliDiC))->disableOriginalConstructor()->getMock();

        $container->expects($this->any())
            ->method("get")
            ->with($this->logicalOr(
                $this->equalTo('http.flow'),
                $this->equalTo('troyan'),
                $this->equalTo('TestApp\Controller\Index')
            ))
            ->will($this->returnCallback(function ($arg) use ($mnapoliDiC) {
                if ($arg == "tryan") {
                    $stub = $this->getMock("stdClass");
                    return $stub;
                }
                if ($arg == "http.flow") {
                    return new EventManager();
                }
                return $mnapoliDiC->get("TestApp\\Controller\\Index");
            }));

        $mnapoliDiC->set('dispatcher', \DI\object('GianArb\Penny\Dispatcher')
            ->method("setRouter", [$router]));

        $mnapoliDiC->set('di', $container);

        $this->app = new App($router, $container);

    }

    public function testInjectionHttpFlow()
    {
        $index = $this->app->getContainer()->get("TestApp\Controller\Index");
        $request = $this->getMock("Zend\\Diactoros\\Request");
        $response = $this->getMock("Zend\\Diactoros\\Response");
        $index->diTest($request, $response);
    }
}
