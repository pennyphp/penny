<?php

namespace Penny\Container;

use DI;
use Penny\Dispatcher;
use Penny\Event\ZendHttpFlowEvent;
use Penny\Event\ZendEvmProxy;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

class PHPDiFactory
{
    /**
     * Container compilation.
     *
     * @param mixed $config Configuration file/array.
     * @param bool $annotation annotation usage.
     *
     * @link http://php-di.org/doc/php-definitions.html
     *
     * @return DI\Container
     */
    public static function buildContainer($config = [], $annotation = true)
    {
        $builder = static::initialSetupContainerBuilder($annotation);
        $builder->addDefinitions($config);

        $container = $builder->build();
        $container->set('di', $container);

        return $container;
    }

    /**
     * Initial setup of DI\ContainerBuilder.
     *
     * @param bool $annotation annotation usage.
     *
     * @return DI\ContainerBuilder
     */
    protected static function initialSetupContainerBuilder($annotation)
    {
        $builder = new DI\ContainerBuilder();
        $builder->useAnnotations($annotation);
        $builder->addDefinitions([
            'request' => ServerRequestFactory::fromGlobals(),
            'response' => DI\object(Response::class),
            'http_flow_event' => DI\object(ZendHttpFlowEvent::class)
                ->constructor('bootstrap', DI\get('request'), DI\get('response')),
            'event_manager' => DI\object(ZendEvmProxy::class),
            'dispatcher' => DI\factory(function ($container) {
                return new Dispatcher($container->get('router'), $container);
            }),
        ]);

        return $builder;
    }
}
