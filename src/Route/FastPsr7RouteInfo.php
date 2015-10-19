<?php
namespace Penny\Route;


class FastPsr7RouteInfo implements RouteInfoInterface
{
    private $name;
    private $callable;
    private $params;

    public static function matched($name, callable $callable, $params = [])
    {
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
