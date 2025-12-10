<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

return [
    'prefix' => 'Stancer\\Scoped\\Isolated',
    'finders' => [
        Finder::create()->files()->in('vendor/psr'),
    ],
];
