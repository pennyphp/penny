<?php

namespace Penny\Exception;

final class MethodNotAllowedException extends Exception
{
    /**
     * Exception code.
     *
     * @var int
     */
    protected $code = 405;
}
