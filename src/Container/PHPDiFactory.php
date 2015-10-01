<?php

namespace GianArb\Penny\Container;

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
        $builder->addDefinitions([
            "event_manager" =>  DI\object('Zend\EventManager\EventManager'),
            "dispatcher" => DI\object('GianArb\Penny\Dispatcher')
                ->constructor(DI\get('router')),
        ]);
        $builder->addDefinitions($config);

        return $builder->build();
    }
}
