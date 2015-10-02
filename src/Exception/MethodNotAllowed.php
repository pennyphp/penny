<?php

namespace Penny\Exception;

use Exception;

final class MethodNotAllowed extends Exception
{
    /**
     * Exception code.
     *
     * @var int
     */
    protected $code = 405;
}
