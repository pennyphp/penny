<?php
namespace Penny\Event;

/**
 * If your event implements this interface
 * it supports runtime override of $request and $response
 * into the app->run method
 */
interface AllowRuntimeOverrideInterface
{
    /**
     * Response setter.
     *
     * @param  mixed $response Representation of an outgoing, server-side response.
     */
    public function setResponse($response);

    /**
     * Response getter.
     *
     * @return mixed
     */
    public function getResponse();

    /**
     * Request setter.
     *
     * @param mixed $request Representation of an outgoing, client-side request.
     * @return null
     */
    public function setRequest($request);

    /**
     * Request getter.
     *
     * @return mixed
     */
    public function getRequest();
}
