<?php

namespace GianArb\Penny\Config;

use Zend\Stdlib\Glob;
use Zend\Stdlib\ArrayUtils;

class Loader
{
    public static function load($pathRole = './config/{{*}}{{,*.local}}.php')
    {
        $config = [];
        foreach (Glob::glob($pathRole, Glob::GLOB_BRACE) as $file) {
            $config = ArrayUtils::merge($config, include $file);
        }
        return $config;
    }
}
