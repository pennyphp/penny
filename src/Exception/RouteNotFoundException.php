<?php

namespace Penny\Exception;

final class RouteNotFoundException extends Exception
{
    /**
     * Exception code.
     *
     * @var int
     */
    protected $code = 404;
}
