<?php

namespace Penny;

use Exception;
use RuntimeException;
use Penny\Config\Loader;
use Penny\Event\PennyEventInterface;
use Penny\Route\RouteInfoInterface;
use Interop\Container\ContainerInterface;
use Zend\EventManager\EventManager;

class App
{
    /**
     * Dependency Injection container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Application initialization.
     *
     * @param ContainerInterface $container Dependency Injection container.
     *
     * @throws Exception If no router is defined.
     */
    public function __construct(ContainerInterface $container = null)
    {
        if ($container === null) {
            $container = Container\PHPDiFactory::buildContainer(Loader::load());
        }

        if ($container->has('router') === false) {
            throw new Exception('Define router config');
        }

        $this->container = $container;
    }

    /**
     * Container getter.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Penny dispatcher getter.
     *
     * @return Dispatcher
     */
    private function getDispatcher()
    {
        $dispatcher = $this->container->get('dispatcher');
        if (!is_callable($dispatcher)) {
            throw new \RuntimeException('Dispatcher must be a callable');
        }

        return $dispatcher;
    }

    /**
     * Penny HTTP flow event getter.
     *
     * @return EventManager
     */
    private function getEventManager()
    {
        return $this->container->get('event_manager');
    }

    /**
     * Application execution.
     *
     * @param mixed|null $request  Representation of an outgoing,
     *  client-side request.
     * @param mixed|null $response Representation of an incoming,
     *  server-side response.
     *
     * @return mixed
     */
    public function run($request = null, $response = null)
    {
        $event = $this->getContainer()->get('http_flow_event');
        if (!($event instanceof PennyEventInterface)) {
            throw new RuntimeException('This event did not supported');
        }
        if ($request !== null) {
            $event->setRequest($request);
        }
        if ($response !== null) {
            $event->setResponse($response);
        }

        $dispatcher = $this->getDispatcher();
        $eventManager = $this->getEventManager();

        try {
            $routeInfo = call_user_func($dispatcher, $event->getRequest());

            if (!($routeInfo instanceof RouteInfoInterface)) {
                throw new RuntimeException('Dispatch does not return RouteInfo object');
            }

            $event->setRouteInfo($routeInfo);
            $event->setName($routeInfo->getName());
        } catch (Exception $exception) {
            return $this->triggerWithException($eventManager, $event, 'dispatch_error', $exception)
                        ->getResponse();
        }

        $eventManager->attach($event->getName(), function ($event) use ($routeInfo) {
            $event->setResponse(call_user_func_array(
                $routeInfo->getCallable(),
                [$event->getRequest(), $event->getResponse()] + $routeInfo->getParams()
            ));
        }, 0);

        try {
            $eventManager->trigger($event);
        } catch (Exception $exception) {
            $this->triggerWithException($eventManager, $event, $routeInfo->getName().'_error', $exception);
        }

        return $event->getResponse();
    }

    /**
     * Event Manager trigger with exception
     *
     * @param \Zend\EventManager\EventManager|\Cake\Event\EventManager $eventManager
     * @param PennyEventInterface|\Zend\EventManager\EventInterface|\Cake\Event\Event $event
     * @param string $name
     * @param Exception $exception
     *
     * @return PennyEventInterface|\Zend\EventManager\EventInterface|\Cake\Event\Event
     */
    private function triggerWithException($eventManager, $event, $name, $exception)
    {
        $event->setName($name);
        $event->setException($exception);
        $eventManager->trigger($event);

        return $event;
    }
}
