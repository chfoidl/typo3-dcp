<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Dynamic Content Plugin',
    'description' => 'Adds a plugin to display content elements based on category and storage.',
    'category' => 'fe',
    'author' => 'Sethorax',
    'author_email' => 'info@sethorax.com',
    'state' => 'stable',
    'uploadfolder' => 0,
    'clearCacheOnLoad' => 1,
    'author_company' => '',
    'version' => '1.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-8.7.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
