<?php

namespace GianArb\PennyTest;

use FastRoute;
use GianArb\Penny\Dispatcher;
use PHPUnit_Framework_TestCase;
use Zend\Diactoros\Request;
use Zend\Diactoros\Uri;

class DispatcherTest extends PHPUnit_Framework_TestCase
{
    private $router;

    public function setUp()
    {
        $this->router = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $manager) {
            $manager->addRoute('GET', '/', ['TestApp\Controller\Index', 'index'], [
                'name' => 'index',
            ]);
            $manager->addRoute('GET', '/fail', ['TestApp\Controller\Index', 'none'], [
                'name' => 'fail',
            ]);
            $manager->addRoute('GET', '/dummy', ['TestApp\Controller\Index', 'dummy'], [
                'name' => 'dummy',
            ]);
        });
    }

    public function testSetRouter()
    {
        $dispatcher = new Dispatcher($this->router);
        $this->assertInstanceof("FastRoute\Dispatcher\GroupCountBased", \PHPUnit_Framework_Assert::readAttribute($dispatcher, 'router'));
    }

    public function testDispatchRouteNotFoundRequest()
    {
        $this->setExpectedException('GianArb\Penny\Exception\RouteNotFound');
        $request = (new Request())
        ->withUri(new Uri('/doh'))
        ->withMethod('GET');

        $dispatcher = new Dispatcher($this->router);
        $dispatcher->dispatch($request);
    }

    public function testDispatchMethodNotAllowedRequest()
    {
        $this->setExpectedException('GianArb\Penny\Exception\MethodNotAllowed');
        $request = (new Request())
        ->withUri(new Uri('/'))
        ->withMethod('POST');

        $dispatcher = new Dispatcher($this->router);
        $dispatcher->dispatch($request);
    }
}
