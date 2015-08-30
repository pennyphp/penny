<?php

namespace GianArb\Penny\Exception;

use Exception;

final class RouteNotFound extends Exception
{
    /**
     * Exception code.
     *
     * @var integer
     */
    protected $code = 404;
}
