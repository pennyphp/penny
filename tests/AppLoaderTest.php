<?php
namespace GianArb\PennyTest;

use GianArb\Penny\App;
use GianArb\Penny\Config\Loader;
use DI\ContainerBuilder;

class AppLoaderTest extends \PHPUnit_Framework_TestCase
{
    private $router;

    public function setUp()
    {
        chdir(dirname(__DIR__."/tests/"));
        $this->router = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/load', ['TestApp\Controller\Index', 'loadedParams'], [
                "name" => "load"
            ]);
        });
    }

    public function testCorrectInjection()
    {
        $app = new App($this->router);

        $request = (new \Zend\Diactoros\Request())
        ->withUri(new \Zend\Diactoros\Uri('/load'))
        ->withMethod("GET");
        $response = new \Zend\Diactoros\Response();

        $response = $app->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("eureka", $response->getBody()->__toString());
    }

    public function testCorrectInjectionWithExternalContainer()
    {

        $builder = new ContainerBuilder();
        $builder->addDefinitions(Loader::load());
        $builder->useAnnotations(true);

        $app = new App($this->router, $builder->build());

        $request = (new \Zend\Diactoros\Request())
        ->withUri(new \Zend\Diactoros\Uri('/load'))
        ->withMethod("GET");
        $response = new \Zend\Diactoros\Response();

        $response = $app->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("eureka", $response->getBody()->__toString());
    }
}
