<?php
defined('TYPO3') or die();

use OklabFlensburg\UranusEvents\Controller\EventController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

// Register cache
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['uranus_events'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['uranus_events'] = [
        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
        'backend' => \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class,
        'options' => [
            'defaultLifetime' => 3600,
        ],
        'groups' => ['pages', 'all'],
    ];
}

// Register Extbase plugin actions
ExtensionUtility::configurePlugin(
    'UranusEvents',
    'Events',
    [
        EventController::class => 'list,detail,loadMore',
    ],
    [
        EventController::class => 'detail,loadMore',
    ]
);

// Register view configuration via TypoScript to ensure paths are always available
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
    'plugin.tx_uranusevents.view {' . LF .
    '    templateRootPaths {' . LF .
    '        10 = EXT:uranus_events/Resources/Private/Templates/' . LF .
    '    }' . LF .
    '    partialRootPaths {' . LF .
    '        10 = EXT:uranus_events/Resources/Private/Templates/Partial/' . LF .
    '    }' . LF .
    '    layoutRootPaths {' . LF .
    '        10 = EXT:uranus_events/Resources/Private/Templates/Layouts/' . LF .
    '        20 = EXT:uranus_events/Resources/Private/Layouts/' . LF .
    '    }' . LF .
    '}' . LF
);

// Icons are registered via Configuration/Icons.php (TYPO3 14.1+)

// Extension defaults
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['uranus_events'] = [
    'apiBaseUrl' => 'https://uranus2.oklabflensburg.de',
    'apiEndpoint' => '/api/events',
    'cacheLifetime' => 3600,
    'httpTimeout' => 30,
    'maxRetries' => 3,
    'debugMode' => false,
];