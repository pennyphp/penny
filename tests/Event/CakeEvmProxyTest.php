<?php

namespace PennyTest\Event;

use Penny\Event\CakeHttpFlowEvent;
use Penny\Event\CakeEvmProxy;
use PHPUnit_Framework_TestCase;

class CakeEmvProxyTest extends PHPUnit_Framework_TestCase
{
    /** @var CakeEmvProxy */
    protected $evmProxy;

    protected function setUp()
    {
        $this->evmProxy = new CakeEvmProxy();
    }

    public function testAttachTrigger()
    {
        $listener1 = function() {
            echo 'triggered1';
        };
        $listener2 = function() {
            echo 'triggered2';
        };

        $eventKey = 'foo';

        $this->evmProxy->attach($eventKey, $listener1, 102);
        $this->evmProxy->attach($eventKey, $listener2, 101);

        $cakeEvent = new CakeHttpFlowEvent($eventKey);

        ob_start();
        $this->evmProxy->trigger($cakeEvent);
        $content = ob_get_clean();

        $this->assertEquals('triggered2triggered1', $content);
    }
}
