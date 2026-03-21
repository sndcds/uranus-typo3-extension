<?php
declare(strict_types=1);

defined('TYPO3') or die();

// Backend module registration for TYPO3 14.1+
// This file is automatically loaded by TYPO3 core

return [
    'uranus_events' => [
        'parent' => 'web',
        'position' => ['after' => 'web_info'],
        'access' => 'admin',
        'workspaces' => 'live',
        'iconIdentifier' => 'uranus-events-module',
        'labels' => 'LLL:EXT:uranus_events/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'UranusEvents',
        'controllerActions' => [
            \OklabFlensburg\UranusEvents\Controller\Backend\ConfigurationController::class => [
                'index',
                'save', 
                'reset',
                'export',
                'import',
                'preview',
            ],
        ],
        'routeTarget' => \OklabFlensburg\UranusEvents\Controller\Backend\ConfigurationController::class . '::indexAction',
        'route' => 'uranus_events',
    ],
];