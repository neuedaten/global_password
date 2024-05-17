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
    'version' => '2.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
