<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in(__DIR__)
    ->exclude('docs')
;

return Symfony\CS\Config\Config::create()
    ->fixers(array(
        '-psr0',
    ))
    ->finder($finder)
;
