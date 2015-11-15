<?php

namespace PennyTest\Event;

use Exception;
use Penny\Event\CakeEvmProxy;
use Cake\Event\EventManager;
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
        $listener = function() {
            echo 'triggered';
        };
        $eventName = 'foo';

        $this->evmProxy->attach($eventName, $listener);

        ob_start();
        $this->evmProxy->trigger($eventName);
        $content = ob_get_clean();

        $this->assertEquals('triggered', $content);
    }
}
