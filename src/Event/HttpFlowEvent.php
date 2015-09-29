<?php

namespace GianArb\Penny\Event;

use Exception;
use Zend\EventManager\Event;

class HttpFlowEvent extends Event
{
    /**
     * Representation of an outgoing, client-side request.
     *
     * @var mixed
     */
    private $request;

    /**
     * Representation of an outgoing, server-side response.
     *
     * @var mixed
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
        $this->setRequest($request);
        $this->setResponse($response);
    }

    /**
     * Response getter.
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Response setter.
     *
     * @param mixed $response Representation of an outgoing, server-side response.
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * Request setter.
     *
     * @param mixed $request Representation of an outgoing, client-side request.
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * Request getter.
     *
     * @return mixed
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
     */
    public function setRouteInfo(array $routerInfo)
    {
        $this->routeInfo = $routerInfo;
    }

    /**
     * Exception setter.
     *
     * @param Exception $exception Exception thrown during execution.
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
