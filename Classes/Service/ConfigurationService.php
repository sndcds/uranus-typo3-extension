<?php
declare(strict_types=1);

namespace OklabFlensburg\UranusEvents\Service;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Configuration Service for Uranus Events Extension
 *
 * This service provides centralized configuration management with priority merging:
 * 1. Plugin FlexForm settings (highest priority, page-specific)
 * 2. Backend Module settings (from sys_registry)
 * 3. Extension Configuration (from ext_conf_template.txt)
 * 4. TypoScript Constants (lowest priority, fallback)
 */
class ConfigurationService
{
    private ExtensionConfiguration $extensionConfiguration;
    private Registry $registry;
    private ConfigurationManagerInterface $configurationManager;
    private TypoScriptService $typoScriptService;
    
    private array $defaultConfiguration = [
        'apiBaseUrl' => 'https://uranus2.oklabflensburg.de',
        'apiEndpoint' => '/api/events',
        'cacheLifetime' => 3600,
        'httpTimeout' => 30,
        'maxRetries' => 3,
        'debugMode' => false,
        'defaultPrimaryColor' => '#0066cc',
        'defaultSecondaryColor' => '#333333',
        'defaultAccentColor' => '#ff6600',
        'defaultFontFamily' => "'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif",
        'defaultBorderRadius' => 8,
        'defaultDateFormat' => 'd.m.Y',
        'defaultTimeFormat' => 'H:i',
        'defaultEventsPerPage' => 20,
        'defaultShowImages' => true,
        'defaultShowVenueMap' => true,
        'defaultShowOrganization' => true,
        'defaultShowTags' => true,
        'defaultShowExcerpt' => true,
        'defaultShowReadMoreLink' => true,
    ];
    
    public function __construct(
        ExtensionConfiguration $extensionConfiguration,
        Registry $registry,
        ConfigurationManagerInterface $configurationManager,
        TypoScriptService $typoScriptService
    ) {
        $this->extensionConfiguration = $extensionConfiguration;
        $this->registry = $registry;
        $this->configurationManager = $configurationManager;
        $this->typoScriptService = $typoScriptService;
    }
    
    /**
     * Get merged configuration with proper priority
     *
     * @param array $pluginSettings Optional plugin-specific settings from FlexForm
     * @return array Complete configuration array
     */
    public function getMergedConfiguration(array $pluginSettings = []): array
    {
        // 1. Get TypoScript settings (lowest priority)
        $typoScriptSettings = $this->getTypoScriptSettings();
        
        // 2. Get Extension Configuration (medium priority)
        $extensionConfig = $this->getExtensionConfiguration();
        
        // 3. Get Backend Module settings (high priority)
        $backendConfig = $this->getBackendConfiguration();
        
        // 4. Merge with priority: backend > extension > typoScript
        $mergedConfig = array_merge(
            $this->defaultConfiguration,
            $typoScriptSettings,
            $extensionConfig,
            $backendConfig
        );
        
        // 5. Apply plugin-specific overrides (highest priority)
        if (!empty($pluginSettings)) {
            $mergedConfig = array_merge($mergedConfig, $this->normalizePluginSettings($pluginSettings));
        }
        
        // Ensure boolean values are properly cast
        $mergedConfig = $this->normalizeBooleanValues($mergedConfig);
        
        return $mergedConfig;
    }
    
    /**
     * Get configuration for backend module (all settings)
     */
    public function getBackendConfiguration(): array
    {
        $config = $this->registry->get('tx_uranusevents', 'configuration', []);
        
        // Ensure all keys exist with null values if not set
        foreach ($this->defaultConfiguration as $key => $defaultValue) {
            if (!array_key_exists($key, $config)) {
                $config[$key] = null;
            }
        }
        
        return array_filter($config, function ($value) {
            return $value !== null;
        });
    }
    
    /**
     * Save configuration from backend module
     */
    public function saveBackendConfiguration(array $configuration): void
    {
        // Validate configuration
        $this->validateConfiguration($configuration);
        
        // Normalize boolean values for storage
        $configuration = $this->normalizeBooleanValues($configuration, true);
        
        // Save to registry
        $this->registry->set('tx_uranusevents', 'configuration', $configuration);
        
        // Clear configuration cache
        $this->clearConfigurationCache();
    }
    
    /**
     * Get extension configuration from ext_conf_template.txt
     */
    private function getExtensionConfiguration(): array
    {
        try {
            $config = $this->extensionConfiguration->get('uranus_events');
            
            // Map legacy keys to new keys if needed
            $mappedConfig = [];
            foreach ($config as $key => $value) {
                // Remove 'default' prefix for backward compatibility
                $newKey = str_replace('default', '', lcfirst($key));
                $mappedConfig[$newKey] = $value;
            }
            
            return $mappedConfig;
        } catch (\Exception $e) {
            // Return empty array if extension configuration is not available
            return [];
        }
    }
    
    /**
     * Get TypoScript settings
     */
    private function getTypoScriptSettings(): array
    {
        try {
            $typoScript = $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
            );
            
            $settings = $typoScript['plugin.']['tx_uranusevents.']['settings.'] ?? [];
            
            if (!empty($settings)) {
                // Convert TypoScript array to plain array
                $settings = $this->typoScriptService->convertTypoScriptArrayToPlainArray($settings);
            }
            
            return $settings;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Normalize plugin settings from FlexForm
     */
    private function normalizePluginSettings(array $pluginSettings): array
    {
        $normalized = [];
        
        // Map common plugin setting keys
        $mapping = [
            'limit' => 'defaultEventsPerPage',
            'past' => 'showPastEvents',
            'showImages' => 'defaultShowImages',
            'showVenueMap' => 'defaultShowVenueMap',
            'showOrganization' => 'defaultShowOrganization',
            'showTags' => 'defaultShowTags',
            'dateFormat' => 'defaultDateFormat',
            'timeFormat' => 'defaultTimeFormat',
        ];
        
        foreach ($pluginSettings as $key => $value) {
            if (isset($mapping[$key])) {
                $normalized[$mapping[$key]] = $value;
            } else {
                $normalized[$key] = $value;
            }
        }
        
        return $normalized;
    }
    
    /**
     * Normalize boolean values in configuration
     */
    private function normalizeBooleanValues(array $config, bool $forStorage = false): array
    {
        $booleanKeys = [
            'debugMode',
            'defaultShowImages',
            'defaultShowVenueMap',
            'defaultShowOrganization',
            'defaultShowTags',
            'defaultShowExcerpt',
            'defaultShowReadMoreLink',
        ];
        
        foreach ($booleanKeys as $key) {
            if (array_key_exists($key, $config)) {
                if ($forStorage) {
                    // Convert to 0/1 for storage
                    $config[$key] = $config[$key] ? 1 : 0;
                } else {
                    // Convert to boolean for usage
                    $config[$key] = (bool)$config[$key];
                }
            }
        }
        
        return $config;
    }
    
    /**
     * Validate configuration values
     *
     * @throws \InvalidArgumentException
     */
    private function validateConfiguration(array $configuration): void
    {
        // Validate colors
        $colorKeys = ['defaultPrimaryColor', 'defaultSecondaryColor', 'defaultAccentColor'];
        foreach ($colorKeys as $key) {
            if (isset($configuration[$key]) && !$this->isValidColor($configuration[$key])) {
                throw new \InvalidArgumentException(
                    sprintf('Invalid color format for %s: %s', $key, $configuration[$key])
                );
            }
        }
        
        // Validate numeric values
        $numericKeys = [
            'cacheLifetime' => ['min' => 0, 'max' => 86400], // 0-24 hours
            'httpTimeout' => ['min' => 1, 'max' => 300], // 1-300 seconds
            'maxRetries' => ['min' => 0, 'max' => 10],
            'defaultBorderRadius' => ['min' => 0, 'max' => 50],
            'defaultEventsPerPage' => ['min' => 1, 'max' => 100],
        ];
        
        foreach ($numericKeys as $key => $limits) {
            if (isset($configuration[$key])) {
                $value = (int)$configuration[$key];
                if ($value < $limits['min'] || $value > $limits['max']) {
                    throw new \InvalidArgumentException(
                        sprintf('%s must be between %d and %d', $key, $limits['min'], $limits['max'])
                    );
                }
            }
        }
        
        // Validate URLs
        if (isset($configuration['apiBaseUrl']) && !filter_var($configuration['apiBaseUrl'], FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid API Base URL');
        }
    }
    
    /**
     * Check if a string is a valid CSS color
     */
    private function isValidColor(string $color): bool
    {
        // Check hex color (#fff, #ffffff)
        if (preg_match('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $color)) {
            return true;
        }
        
        // Check rgb/rgba color
        if (preg_match('/^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/', $color) ||
            preg_match('/^rgba\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3}),\s*(0|1|0?\.\d+)\)$/', $color)) {
            return true;
        }
        
        // Check named colors (basic set)
        $namedColors = [
            'black', 'white', 'red', 'green', 'blue', 'yellow', 'orange', 
            'purple', 'pink', 'brown', 'gray', 'grey', 'transparent'
        ];
        
        if (in_array(strtolower($color), $namedColors)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Clear configuration cache
     */
    private function clearConfigurationCache(): void
    {
        $cacheManager = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class);
        try {
            $cacheManager->getCache('uranus_events_configuration')->flush();
        } catch (\Exception $e) {
            // Cache might not exist yet, that's okay
        }
    }
    
    /**
     * Get configuration for specific category
     */
    public function getConfigurationByCategory(string $category): array
    {
        $allConfig = $this->getMergedConfiguration();
        
        $categories = [
            'api' => ['apiBaseUrl', 'apiEndpoint', 'httpTimeout', 'maxRetries'],
            'cache' => ['cacheLifetime'],
            'design' => [
                'defaultPrimaryColor', 'defaultSecondaryColor', 'defaultAccentColor',
                'defaultFontFamily', 'defaultBorderRadius'
            ],
            'display' => [
                'defaultDateFormat', 'defaultTimeFormat', 'defaultEventsPerPage',
                'defaultShowImages', 'defaultShowVenueMap', 'defaultShowOrganization',
                'defaultShowTags', 'defaultShowExcerpt', 'defaultShowReadMoreLink'
            ],
            'debug' => ['debugMode'],
        ];
        
        if (!isset($categories[$category])) {
            return [];
        }
        
        return array_intersect_key($allConfig, array_flip($categories[$category]));
    }
    
    /**
     * Export configuration as JSON
     */
    public function exportConfiguration(): string
    {
        $config = $this->getBackendConfiguration();
        return json_encode($config, JSON_PRETTY_PRINT);
    }
    
    /**
     * Import configuration from JSON
     */
    public function importConfiguration(string $json): void
    {
        $config = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON format');
        }
        
        $this->saveBackendConfiguration($config);
    }
}