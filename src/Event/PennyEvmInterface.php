<?php
namespace Penny\Event;

interface PennyEvmInterface
{
    public function trigger(PennyEventInterface $event);
    public function attach($eventName, callable $listener);
}
