<?php

use Wdb\Dependencies\Controller\DependencyModuleController;

return [
    'Dependencies' => [
        'parent' => 'tools',
        'access' => 'admin',
        'iconIdentifier' => 'module-dependencies',
        'labels' => 'LLL:EXT:dependencies/Resources/Private/Language/locallang_mod.xlf',
        /*
        'routes' => [
            '_default' => [
                'controller' => \Wdb\Dependencies\Controller\DependencyModuleController::class,
                'action' => 'index',
            ],
        ],
        */
        'extensionName' => 'Dependencies',
        'path' => '/module/tools/Dependencies',
        'controllerActions' => [
            DependencyModuleController::class => 'index',
        ],
    ],
];
