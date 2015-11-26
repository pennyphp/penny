<?php
namespace Penny\Event;

interface PennyEvmInterface
{
    /**
     * Triggerer event
     *
     * @param PennyEventInterface $event Trigger specific event
     */
    public function trigger(PennyEventInterface $event);

    /**
     * Attach new listener at specific event
     *
     * @param string $eventName Specific event name
     * @param callable $listener Function to call
     * @param int $priority listener call priority
     */
    public function attach($eventName, callable $listener, $priority = 0);
}
