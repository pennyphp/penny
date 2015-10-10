<?php
namespace Penny\Event;

/**
 * If your event implement this interface
 * and you populate the app->run with
 * request and response I use them into the app
 */
interface OverrideInterface
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
