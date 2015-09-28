<?php

namespace GianArb\Penny;

use DI;
use Exception;
use GianArb\Penny\Config\Loader;
use GianArb\Penny\Event\HttpFlowEvent;
use ReflectionClass;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Interop\Container\ContainerInterface;

class App
{
    /**
     * Dependency Injection container.
     *
     * @var ContainerInterface
     */
    private $container;

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
     * Application initialization.
     *
     * @param ContainerInterface $container Dependency Injection container.
     *
     * @throws Exception If no router is defined.
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container ?: static::buildContainer(Loader::load());
        $container = &$this->container;

        if ($container->has('router') == false) {
            throw new Exception('Define router config');
        }

        $container->set('di', $container);
    }

    /**
     * Container compilation.
     *
     * @param mixed $config Configuration file/array.
     *
     * @link http://php-di.org/doc/php-definitions.html
     *
     * @return ContainerInterface
     */
    public static function buildContainer($config = [])
    {
        $builder = new DI\ContainerBuilder();
        $builder->useAnnotations(true);
        $builder->addDefinitions([
            "event_manager" =>  DI\object('Zend\EventManager\EventManager'),
            "dispatcher" => DI\object('GianArb\Penny\Dispatcher')
                ->constructor(DI\get('router')),
        ]);
        $builder->addDefinitions($config);

        return $builder->build();
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
        $container = $this->container;

        return $container->get('dispatcher');
    }

    /**
     * Penny HTTP flow event getter.
     *
     * @return HttpFlowEvent
     */
    private function getEventManager()
    {
        $container = $this->container;

        return $container->get('event_manager');
    }

    /**
     * Application execution.
     *
     * @param mixed|null  $request  Representation of an outgoing, client-side request.
     * @param mixed|null $response Representation of an outgoing, server-side response.
     *
     * @return mixed
     */
    public function run($request = null, $response = null)
    {
        ($request != null) ?: $request = ServerRequestFactory::fromGlobals();
        ($response != null) ?: $response = new Response();
        $event = new HttpFlowEvent('bootstrap', $request, $response);

        $container = $this->getContainer();
        $dispatcher = $this->getDispatcher();
        $httpFlow = $this->getEventManager();

        try {
            $routerInfo = $dispatcher->dispatch($request);
        } catch (Exception $exception) {
            $event->setName('ERROR_DISPATCH');
            $event->setException($exception);
            $httpFlow->trigger($event);

            return $event->getResponse();
        }

        $controller = $container->get($routerInfo[1][0]);
        $method = $routerInfo[1][1];
        $function = (new ReflectionClass($controller))->getShortName();

        $eventName = sprintf('%s.%s', strtolower($function), $method);
        $event->setName($eventName);
        $event->setRouteInfo($routerInfo);

        $httpFlow->attach($eventName, function ($event) use ($controller, $method) {
            $event->setResponse(call_user_func_array(
                [$controller, $method],
                [$event->getRequest(), $event->getResponse()] + $event->getRouteInfo()[2]
            ));
        }, 0);

        try {
            $httpFlow->trigger($event);
        } catch (Exception $exception) {
            $event->setName($eventName.'_error');
            $event->setException($exception);
            $httpFlow->trigger($event);
        }

        return $event->getResponse();
    }
}
