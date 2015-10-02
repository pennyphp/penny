<?php

namespace PennyTest;

use DI\ContainerBuilder;
use FastRoute;
use Penny\App;
use Penny\Container;
use Penny\Config\Loader;
use PHPUnit_Framework_TestCase;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\Uri;

class AppLoaderTest extends PHPUnit_Framework_TestCase
{
    private $container;

    public function setUp()
    {
        chdir(__DIR__.'/app/');
        $config = Loader::load();
        $config['router'] = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/load', ['TestApp\Controller\Index', 'loadedParams'], [
                'name' => 'load',
            ]);
        });
        $this->container = Container\PHPDiFactory::buildContainer($config);
    }

    public function testCorrectInjection()
    {
        $app = new App($this->container);

        $request = (new Request())
        ->withUri(new Uri('/load'))
        ->withMethod('GET');
        $response = new Response();

        $response = $app->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('eureka', $response->getBody()->__toString());
    }

}
