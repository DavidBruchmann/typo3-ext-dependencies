<?php

return [
    // Register all PHP classes in Classes/ as services (wildcard)
    'Wdb\\Dependencies\\' => [
        'classNamePattern' => 'Wdb\\Dependencies\\.*',
        'autoload' => true,
        'public' => true,
    ],
    // Example: Explicit public service
    Wdb\Dependencies\Service\DependencyAnalyzerService::class => [
        'public' => true,
    ],
];
