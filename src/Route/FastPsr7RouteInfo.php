<?php
namespace Penny\Route;

use InvalidArgumentException;

class FastPsr7RouteInfo implements RouteInfoInterface
{
    private $name;
    private $callable;
    private $params;

    public static function matched($name, $callable, $params = [])
    {
        if (!is_callable($callable)) {
            throw new InvalidArgumentException('$callable could be only a callable');
        }
        $obj = new self();
        $obj->name = $name;
        $obj->callable = $callable;
        $obj->params = $params;
        return $obj;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCallable()
    {
        return $this->callable;
    }

    public function getParams()
    {
        return $this->params;
    }
}
