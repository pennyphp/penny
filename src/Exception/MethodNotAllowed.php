<?php

namespace GianArb\Penny\Exception;

use Exception;

final class MethodNotAllowed extends Exception
{
    protected $code = 405;
}
