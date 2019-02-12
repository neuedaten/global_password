<?php

return [
    'frontend' => [
        'neuedaten/global-password/check-password' => [
            'target' => \Neuedaten\GlobalPassword\Middleware\CheckPassword::class,
            'before' => [
                'typo3/cms-frontend/authentication'
            ]
        ],
    ],
];