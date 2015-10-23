<?php

namespace Penny\Exception;

use Exception;

final class RouteNotFoundException extends Exception
{
    /**
     * Exception code.
     *
     * @var int
     */
    protected $code = 404;
}
