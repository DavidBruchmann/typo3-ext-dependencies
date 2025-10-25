<?php

defined('TYPO3') or die();

use Wdb\Dependencies\Controller\DependencyModuleController;

call_user_func(function () {
    // Only register for TYPO3 < v12
    if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 12000000) {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'Dependencies', // Extension Name in CamelCase
            'tools', // the main module
            'dependencies', // Submodule key
            'bottom', // Position
            [
                DependencyModuleController::class => 'index'
            ],
            [
                'access' => 'admin',
                'icon' => 'EXT:dependencies/Resources/Public/Icons/Module.svg',
                'labels' => 'LLL:EXT:dependencies/Resources/Private/Language/locallang_mod.xlf',
            ]
        );
    }
});
