<?php
namespace Penny\Event;

use Cake\Event\EventManager;

class CakeEvmProxy implements PennyEvmInterface
{
    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * Proxy EventManager
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
        $this->eventManager->dispatch($event);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function attach($eventName, callable $listener, $priority = 1)
    {
        $options['priority'] = $priority;
        $this->eventManager->attach($listener, $eventName, $options);
        return $this;
    }
}
