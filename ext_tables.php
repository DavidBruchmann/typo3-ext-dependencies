<?php

defined('TYPO3') or die();

call_user_func(function () {
    
    /*
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Dependencies', // Extension Name in CamelCase
        'tools', // the main module
        'dependencies', // Submodule key
        'bottom', // Position
        [
            \Wdb\Dependencies\Controller\DependencyModuleController::class => 'index'
        ],
        [
            'access' => 'admin',
            'icon' => 'EXT:dependencies/Resources/Public/Icons/Module.svg',
            'labels' => 'LLL:EXT:dependencies/Resources/Private/Language/locallang_mod.xlf',
        ]
    );
    */
});
