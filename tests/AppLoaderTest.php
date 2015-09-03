<?php

namespace GianArb\PennyTest;

use DI\ContainerBuilder;
use FastRoute;
use GianArb\Penny\App;
use GianArb\Penny\Config\Loader;
use PHPUnit_Framework_TestCase;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\Uri;

class AppLoaderTest extends PHPUnit_Framework_TestCase
{
    private $router;

    public function setUp()
    {
        chdir(__DIR__.'/app/');
        $this->router = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/load', ['TestApp\Controller\Index', 'loadedParams'], [
                'name' => 'load',
            ]);
        });
    }

    public function testCorrectInjection()
    {
        $app = new App($this->router);

        $request = (new Request())
        ->withUri(new Uri('/load'))
        ->withMethod('GET');
        $response = new Response();

        $response = $app->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('eureka', $response->getBody()->__toString());
    }

    public function testCorrectInjectionWithExternalContainer()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(Loader::load());
        $builder->useAnnotations(true);

        $app = new App($this->router, $builder->build());

        $request = (new Request())
        ->withUri(new Uri('/load'))
        ->withMethod('GET');
        $response = new Response();

        $response = $app->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('eureka', $response->getBody()->__toString());
    }
}
