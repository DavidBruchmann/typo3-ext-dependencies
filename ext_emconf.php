<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Dependency Analyzer',
    'description' => 'Analyzes Composer dependencies and displays version, categories, and tags in the TYPO3 backend.',
    'category' => 'module',
    'author' => 'David Bruchmann',
    'author_email' => 'david.bruchmann@gmail.com',
    'state' => 'beta',
    'clearCacheOnLoad' => true,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
