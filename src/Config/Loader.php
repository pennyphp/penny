<?php
namespace GianArb\Penny\Config;

use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Glob;

class Loader
{
    /**
     * Configuration loader.
     *
     * @param string $pathRole Glob expression.
     *
     * @return array
     */
    public static function load($pathRole = './config/{{*}}{{,*.local}}.php')
    {
        $config = [];
        foreach (Glob::glob($pathRole, Glob::GLOB_BRACE) as $file) {
            $config = ArrayUtils::merge($config, include $file);
        }

        return $config;
    }
}
