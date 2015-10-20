<?php

namespace Penny\Container;

use DI;

class PHPDiFactory
{
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
        $builder->addDefinitions(
            [
                'request' => \Zend\Diactoros\ServerRequestFactory::fromGlobals(),
                'response' => DI\object('Zend\Diactoros\Response'),
                'http_flow_event' => DI\object('Penny\Event\HttpFlowEvent')
                    ->constructor(
                        'bootstrap',
                        DI\get('request'),
                        DI\get('response')
                    ),
                'event_manager' => DI\object('Zend\EventManager\EventManager'),
                'dispatcher' => DI\factory(function ($container) {
                    return new \Penny\Dispatcher($container->get('router'), $container);
                }),
            ]
        );
        $builder->addDefinitions($config);
        $container = $builder->build();
        $container->set('di', $container);

        return $container;
    }
}
