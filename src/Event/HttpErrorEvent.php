<?php
namespace GianArb\Penny\Event;

use Zend\EventManager\Event;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

class HttpErrorEvent extends HttpFlowEvent
{
    private $exception;

    public function setException(\Exception $exception)
    {
        $this->exception = $exception;
    }

    public function getException()
    {
        return $this->exception;
    }
}
