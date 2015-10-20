<?php

namespace PennyTest\Route;

use PHPUnit_Framework_TestCase;
use Penny\Route\RouteInfoInterface;
use TestApp\Controller\IndexController;
use Penny\Route\RouteInfo;

class RouteInfoTest extends PHPUnit_Framework_TestCase
{
    public function testRouteInfoImplementInterface()
    {
        $routeInfo = new RouteInfo('', function(){}, []);
        $this->assertInstanceOf(RouteInfoInterface::class, $routeInfo);
    }

    public function testMatched()
    {
        $routeInfo = new RouteInfo('index.try', [new IndexController(), 'index'], ['id' => 5]);
        $this->assertSame('index.try', $routeInfo->getName());
    }
}
