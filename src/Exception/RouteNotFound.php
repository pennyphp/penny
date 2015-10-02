<?php

namespace Penny\Exception;

use Exception;

final class RouteNotFound extends Exception
{
    /**
     * Exception code.
     *
     * @var int
     */
    protected $code = 404;
}
