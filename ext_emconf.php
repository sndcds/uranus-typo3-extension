<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Uranus Events',
    'description' => 'Display events from Uranus Public API in TYPO3',
    'category' => 'plugin',
    'author' => 'Oklab Flensburg',
    'author_email' => 'info@oklab-flensburg.de',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.1.0-14.4.99',
            'php' => '8.1.0-8.3.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'OklabFlensburg\\UranusEvents\\' => 'Classes/',
        ],
    ],
    'autoload-dev' => [
        'psr-4' => [
            'OklabFlensburg\\UranusEvents\\Tests\\' => 'Tests/',
        ],
    ],
];