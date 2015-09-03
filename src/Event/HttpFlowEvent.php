<?php

namespace GianArb\Penny\Event;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\EventManager\Event;

class HttpFlowEvent extends Event
{
    /**
     * Representation of an outgoing, client-side request.
     *
     * @var RequestInterface|mixed
     */
    private $request;

    /**
     * Representation of an outgoing, server-side response.
     *
     * @var ResponseInterface|mixed
     */
    private $response;

    /**
     * Exception thrown during execution.
     *
     * @var Exception
     */
    private $exception;

    /**
     * Routing information.
     *
     * @var array
     */
    private $routeInfo = [];

    /**
     * Class constructor.
     *
     * @param string $name     Event name.
     * @param mixed  $request  Representation of an outgoing, client-side request.
     * @param mixed  $response Representation of an outgoing, server-side response.
     */
    public function __construct($name, $request, $response)
    {
        $this->setName($name);
        $this->response = $response;
        $this->request = $request;
    }

    /**
     * Response getter.
     *
     * @return ResponseInterface|mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Response setter.
     *
     * @param ResponseInterface $response Representation of an outgoing, server-side response.
     *
     * @return void
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * Request setter.
     *
     * @param RequestInterface $request Representation of an outgoing, client-side request.
     *
     * @return void
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Request getter.
     *
     * @return RequestInterface|mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Route info getter.
     *
     * @return array
     */
    public function getRouteInfo()
    {
        return $this->routeInfo;
    }

    /**
     * Route info setter.
     *
     * @param array $routerInfo Routing information.
     *
     * @return void
     */
    public function setRouteInfo(array $routerInfo)
    {
        $this->routeInfo = $routerInfo;
    }

    /**
     * Exception setter.
     *
     * @param Exception $exception Exception thrown during execution.
     *
     * @return void
     */
    public function setException(Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * Exception getter.
     *
     * @return Exception
     */
    public function getException()
    {
        return $this->exception;
    }
}
