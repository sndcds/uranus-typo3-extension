<?php
defined('TYPO3') or die();

call_user_func(function () {
    // Register plugin
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'UranusEvents',
        'Events',
        'LLL:EXT:uranus_events/Resources/Private/Language/locallang_db.xlf:plugin.events.title',
        'uranus-events-plugin',
        'special'
    );

    // Configure plugin
    $GLOBALS['TCA']['tt_content']['types']['uranusevents_events'] = [
        'showitem' => '
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                --palette--;;general,
                --palette--;;headers,
            --div--;LLL:EXT:uranus_events/Resources/Private/Language/locallang_db.xlf:plugin.events.tab.filter,
                pi_flexform,
            --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
                --palette--;;frames,
                --palette--;;appearanceLinks,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                --palette--;;language,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;hidden,
                --palette--;;access,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
                categories,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                rowDescription,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
        ',
        'columnsOverrides' => [],
    ];

    // PageTS configuration is in Configuration/TsConfig/Page/Plugin.tsconfig
    // This registers the plugin in the new content element wizard
});