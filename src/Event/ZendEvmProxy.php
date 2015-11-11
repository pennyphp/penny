<?php
namespace Penny\Event;

use Zend\EventManager\EventManager;

class ZendEvmProxy implements PennyEvmInterface
{
    private $eventManager;

    public function __construct()
    {
        $this->eventManager = new EventManager();
    }

    public function trigger(PennyEventInterface $event)
    {
        $this->eventManager->trigger($event);
        return $this;
    }

    public function attach($eventName, callable $listener, $priority = 0)
    {
        $this->eventManager->attach($eventName, $listener, $priority);
        return $this;
    }
}
