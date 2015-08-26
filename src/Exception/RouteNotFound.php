<?php

namespace GianArb\Penny\Exception;

use Exception;

final class RouteNotFound extends Exception
{
    protected $code = 404;
}
