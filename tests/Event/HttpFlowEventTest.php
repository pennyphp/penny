<?php

namespace PennyTest;

use Exception;
use Penny\Event\HttpFlowEvent;
use Penny\Route\RouteInfoInterface;
use PHPUnit_Framework_TestCase;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\Uri;

class HttpFlowEventTest extends PHPUnit_Framework_TestCase
{
    private $event;

    public function setUp()
    {
        $request = (new Request())
        ->withUri(new Uri('/'))
        ->withMethod('GET');
        $response = new Response();

        $this->event = new HttpFlowEvent('http_flow_event', $request, $response);
    }

    public function testGetResponse()
    {
        $response = new Response();
        $this->assertSame($response, $this->event->getResponse());
    }

    public function testGetRequest()
    {
        $request = (new Request())
        ->withUri(new Uri('/'))
        ->withMethod('GET');

        $this->assertSame($request, $this->event->getRequest());
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
