<?php

namespace GianArb\PennyTest;

use DI\ContainerBuilder;
use GianArb\Penny\App;

class DiTest extends \PHPUnit_Framework_TestCase
{
    private $container;
    private $router;

    public function setUp()
    {
        $this->router = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $router) {
            $router->addRoute('GET', '/', ['TestApp\Controller\Index', 'diTest']);
        });


        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        $dic = $builder->build();

        $this->container = $dic;

    }

    public function testInjectionHttpFlow()
    {
        $this->container->set("troyan", "call me");
        $app = new App($this->router, $this->container);

        $request = (new \Zend\Diactoros\Request())
        ->withUri(new \Zend\Diactoros\Uri('/'))
        ->withMethod("GET");
        $response = new \Zend\Diactoros\Response();

        $response = $app->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("call me", $response->getBody()->__toString());
    }
}
