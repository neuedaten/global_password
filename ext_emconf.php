<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Global password',
    'description' => 'Password protection for a complete TYPO3 frontend. Useful for development and staging servers.',
    'category' => 'frontend',
    'state' => 'beta',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Bastian Schwabe',
    'author_email' => 'bas@neuedaten.de',
    'author_company' => 'NEUEDATEN',
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-9.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
