<?php
namespace Penny\Route;

interface RouteInfoInterface
{
    public function getName();
    public function getCallable();
    public function getParams();
}
