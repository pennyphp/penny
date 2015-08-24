<?php

namespace GianArb\Penny\Exception;

final class RouteNotFound extends \Exception
{
    /**
     * Exception code.
     *
     * @var integer
     */
    protected $code = 404;
}
