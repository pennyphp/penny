<?php

namespace GianArb\PennyTest;

use DI\ContainerBuilder;
use GianArb\Penny\Config\Loader;
use FastRoute;
use GianArb\Penny\App;
use GianArb\Penny\Container;
use PHPUnit_Framework_TestCase;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\Uri;

class DiTest extends PHPUnit_Framework_TestCase
{
    private $container;
    private $router;

    public function setUp()
    {
        $config = Loader::load();
        $config['router'] = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/', ['TestApp\Controller\Index', 'diTest']);
        });

        $this->container = Container\PHPDiFactory::buildContainer($config);
    }

    public function testInjectionHttpFlow()
    {
        $this->container->set('troyan', 'call me');
        $app = new App($this->container);

        $request = (new Request())
        ->withUri(new Uri('/'))
        ->withMethod('GET');
        $response = new Response();

        $response = $app->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('call me', $response->getBody()->__toString());
    }
}
