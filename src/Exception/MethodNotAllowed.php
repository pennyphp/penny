<?php

namespace GianArb\Penny\Exception;

use Exception;

final class MethodNotAllowed extends Exception
{
    /**
     * Exception code.
     *
     * @var integer
     */
    protected $code = 405;
}
