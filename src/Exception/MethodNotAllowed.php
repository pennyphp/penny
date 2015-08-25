<?php

namespace GianArb\Penny\Exception;

final class MethodNotAllowed extends \Exception
{
    /**
     * Exception code.
     *
     * @var integer
     */
    protected $code = 405;
}
