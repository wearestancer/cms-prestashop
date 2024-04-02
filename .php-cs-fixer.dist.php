<?php

$config = new PrestaShop\CodingStandards\CsFixer\Config();

$config->setUsingCache(true)->setCacheFile(__DIR__ . '/vendor/.cache/php-cs-fixer.cache');

/** @var Symfony\Component\Finder\Finder $finder */
$finder = $config->getFinder();
$finder->in(__DIR__)
    ->exclude('.devcontainer')
    ->exclude('.git')
    ->exclude('node_modules')
    ->exclude('translations')
    ->exclude('vendor')
;

return $config;
