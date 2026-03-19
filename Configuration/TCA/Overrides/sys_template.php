<?php
defined('TYPO3') or die();

// Add FlexForm configuration for the plugin
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:uranus_events/Configuration/FlexForms/Events.xml',
    'uranusevents_events'
);