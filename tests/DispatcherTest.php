<?php
namespace GianArb\PennyTest;

use GianArb\Penny\Dispatcher;
use Psr\Http\Message\Request;

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    private $router;

    public function setUp()
    {
        $this->router = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $manager) {
            $manager->addRoute('GET', '/', ['TestApp\Controller\Index', 'index'], [
                "name" => "index"
            ]);
            $manager->addRoute('GET', '/fail', ['TestApp\Controller\Index', 'failed'], [
                "name" => "fail"
            ]);
            $manager->addRoute('GET', '/dummy', ['TestApp\Controller\Index', 'dummy'], [
                "name" => "dummy"
            ]);
        });
    }

    public function testSetRouter()
    {
        $dispatcher = new Dispatcher([$this->router]);
        $this->assertInstanceof("FastRoute\\RouteParser", \PHPUnit_Framework_Assert::readAttribute($dispatcher, 'router'));
    }
}
