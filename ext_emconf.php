<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Global password',
    'description' => 'Password protection for a complete TYPO3 frontend. Useful for development and staging servers.',
    'category' => 'frontend',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Bastian Schwabe',
    'author_email' => 'bas@neuedaten.de',
    'author_company' => 'NEUEDATEN',
    'version' => '4.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
