<?php
namespace GianArb\PennyTest;

use GianArb\Penny\Dispatcher;

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testSetRouter()
    {
        $router = $this->getMock("FastRoute\\RouteParser");
        $dispatcher = new Dispatcher($router);
        $this->assertInstanceof("FastRoute\\RouteParser", \PHPUnit_Framework_Assert::readAttribute($dispatcher, 'router'));
    }
}
