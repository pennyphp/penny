<?php

namespace PennyTest\Event;

use Exception;
use Penny\Event\ZendHttpFlowEvent;
use Penny\Route\RouteInfoInterface;
use PHPUnit_Framework_TestCase;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\Uri;

class ZendHttpFlowEventTest extends PHPUnit_Framework_TestCase
{
    private $event;

    public function setUp()
    {
        $request = (new Request())
        ->withUri(new Uri('/'))
        ->withMethod('GET');
        $response = new Response();

        $this->event = new ZendHttpFlowEvent('http_flow_event', $request, $response);
    }

    public function testGetResponse()
    {
        $this->assertInstanceOf(Response::class, $this->event->getResponse());
    }

    public function testGetRequest()
    {
        $this->assertInstanceOf(Request::class, $this->event->getRequest());
    }

    public function testSetGetRouteInfo()
    {
        $routeInfo = $this->prophesize(RouteInfoInterface::class);
        $this->event->setRouteInfo($routeInfo->reveal());

        $this->assertInstanceOf(RouteInfoInterface::class, $this->event->getRouteInfo());
    }

    public function testSetGetException()
    {
        $exception = new Exception();
        $this->event->setException($exception);
        $this->assertSame($exception, $this->event->getException());
    }
}
