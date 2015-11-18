<?php
namespace Penny\Event;

use Zend\EventManager\EventInterface as ZendEventInterface;
use Zend\EventManager\EventManager;

class ZendEvmProxy implements PennyEvmInterface
{
    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * Proxy Zend\EventManager\EventManager
     */
    public function __construct()
    {
        $this->eventManager = new EventManager();
    }

    /**
     * {@inheritDoc}
     */
    public function trigger(PennyEventInterface $event)
    {
        if ($event instanceof ZendEventInterface) {
            $this->eventManager->trigger($event);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function attach($eventName, callable $listener, $priority = 0)
    {
        $this->eventManager->attach($eventName, $listener, $priority);
        return $this;
    }
}
