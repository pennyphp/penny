<?php

namespace Penny\Route;

class RouteInfo implements RouteInfoInterface
{
    private $name;
    private $callable;
    private $params;

    public function __construct($name, callable $callable, $params = [])
    {
        $this->name = $name;
        $this->callable = $callable;
        $this->params = $params;
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
