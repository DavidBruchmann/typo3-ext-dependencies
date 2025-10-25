<?php

defined('TYPO3') or die();

call_user_func(function () {
    // Register a cache for version lookups (file backend, 1-day lifetime)
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['dependencies_version_cache'] = [
        'backend' => \TYPO3\CMS\Core\Cache\Backend\FileBackend::class,
        'options' => ['defaultLifetime' => 86400], // 1 day
    ];
});
