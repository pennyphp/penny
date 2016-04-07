<?php

namespace Penny\Event;

use Exception;
use Penny\Route\RouteInfoInterface;

class FastHttpFlowEvent implements EventInterface
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
     * @var RouteInfoInterface
     */
    private $routeInfo;

    /**
     * Event Name
     * @var string
     */
    private $name;

    public function __construct($name, $request, $response)
    {
        $this->setName($name);
        $this->setRequest($request);
        $this->setResponse($response);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return '/' . $this->name . '/';
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * {@inheritDoc}
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * {@inheritDoc}
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Route info getter.
     *
     * @return RouteInfoInterface
     */
    public function getRouteInfo()
    {
        return $this->routeInfo;
    }

    /**
     * Route info setter.
     *
     * @param RouteInfoInterface $routerInfo Routing information.
     */
    public function setRouteInfo(RouteInfoInterface $routerInfo)
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

    /**
     * {@inheritDoc}
     */
    public function stopPropagation($flag = true)
    {
        if ($flag === true) {
            throw new Exception();
        }
        return;
    }
}
