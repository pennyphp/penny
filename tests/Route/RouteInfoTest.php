<?php

namespace PennyTest\Route;

use PHPUnit_Framework_TestCase;
use Penny\Route\FastPsr7RouteInfo;
use Penny\Route\RouteInfoInterface;
use TestApp\Controller\IndexController;

class RouteInfoTest extends PHPUnit_Framework_TestCase
{
    public function testRouteInfoImplementInterface()
    {
        $routeInfo = new FastPsr7RouteInfo();
        $this->assertInstanceOf(RouteInfoInterface::class, $routeInfo);
    }

    public function testMatched()
    {
        $fastRouteInfo = [
            [],
            [new IndexController(), 'index'],
            ['id' => 5],
        ];
        $routeInfo = FastPsr7RouteInfo::matched('index.try', $fastRouteInfo[1], $fastRouteInfo[2]);
        $this->assertSame('index.try', $routeInfo->getName());
    }
}
