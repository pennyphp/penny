<?php
namespace GianArb\PennyTest;

use GianArb\Penny\App;

class AppLoaderTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        chdir(dirname(__DIR__."/tests/"));
        $router = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/load', ['TestApp\Controller\Index', 'loadedParams'], [
                "name" => "load"
            ]);
        });

        $this->app = new App($router);
    }

    public function testThatControllerResumeCorrentParamFromDIConfigurationedWithLoader()
    {
        $request = (new \Zend\Diactoros\Request())
        ->withUri(new \Zend\Diactoros\Uri('/load'))
        ->withMethod("GET");
        $response = new \Zend\Diactoros\Response();

        $response = $this->app->run($request, $response);
        $this->assertSame("euroka", $response->getContent()->__toString());
    }
}
