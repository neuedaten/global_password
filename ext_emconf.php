<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Global password',
    'description' => 'Login form to protect a complete TYPO3 System from unauthorized visitors. Useful for development and staging servers.',
    'category' => 'frontend',
    'state' => 'beta',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Bastian Schwabe',
    'author_email' => 'bas@neuedaten.de',
    'author_company' => 'NEUEDATEN',
    'version' => '0.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-9.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
