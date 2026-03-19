<?php
defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

call_user_func(function () {
    ExtensionManagementUtility::addStaticFile(
        'uranus_events',
        'Configuration/TypoScript',
        'Uranus Events'
    );

    ExtensionUtility::registerPlugin(
        'UranusEvents',
        'Events',
        'LLL:EXT:uranus_events/Resources/Private/Language/locallang_db.xlf:plugin.events.title',
        'uranus-events-plugin'
    );
});