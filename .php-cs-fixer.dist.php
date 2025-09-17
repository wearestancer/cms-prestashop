<?php
class PhpCsConfig extends PrestaShop\CodingStandards\CsFixer\Config
{
    function getRules(): array
    {
        // to pass the PS validator we have to disable this rule
        // It make our files less clean but we do not have a choice to pass.
        return array_merge(
            parent::getRules(),
            [
                'blank_line_after_opening_tag' => false,
            ]
        );
    }
}
$config = new PhpCsConfig();

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
