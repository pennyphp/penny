<?php
namespace Penny\Event;

use Cake\Event\Event as BaseCakeEvent;
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
        if ($event instanceof BaseCakeEvent) {
            $this->eventManager->dispatch($event);
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function attach($eventName, callable $listener, $priority = 1)
    {
        $options = [];
        $options['priority'] = $priority;
        $this->eventManager->on($eventName, $options, $listener);
        return $this;
    }
}
