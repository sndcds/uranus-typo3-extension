<?php
declare(strict_types=1);

namespace OklabFlensburg\UranusEvents\Service;

use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * CSS Generator Service for Uranus Events Extension
 *
 * Generates dynamic CSS based on configuration settings
 * with caching for performance.
 */
class CssGeneratorService
{
    private ConfigurationService $configurationService;
    private ?FrontendInterface $cache;
    
    private const CACHE_KEY = 'uranus_events_css';
    private const CACHE_TAG = 'uranus_events';
    
    public function __construct(ConfigurationService $configurationService)
    {
        $this->configurationService = $configurationService;
        
        // Initialize cache
        try {
            $cacheManager = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class);
            $this->cache = $cacheManager->getCache('uranus_events_css');
        } catch (\Exception $e) {
            $this->cache = null;
        }
    }
    
    /**
     * Generate CSS based on current configuration
     */
    public function generateCss(): string
    {
        // Try to get from cache first
        $cachedCss = $this->getCachedCss();
        if ($cachedCss !== null) {
            return $cachedCss;
        }
        
        // Generate fresh CSS
        $config = $this->configurationService->getMergedConfiguration();
        $css = $this->buildCss($config);
        
        // Cache the result
        $this->cacheCss($css);
        
        return $css;
    }
    
    /**
     * Generate CSS with specific configuration (for preview)
     */
    public function generateCssForPreview(array $config): string
    {
        return $this->buildCss($config);
    }
    
    /**
     * Build CSS from configuration array
     */
    private function buildCss(array $config): string
    {
        // Extract design settings with fallbacks
        $primaryColor = $config['defaultPrimaryColor'] ?? '#0066cc';
        $secondaryColor = $config['defaultSecondaryColor'] ?? '#333333';
        $accentColor = $config['defaultAccentColor'] ?? '#ff6600';
        $fontFamily = $config['defaultFontFamily'] ?? "'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif";
        $borderRadius = (int)($config['defaultBorderRadius'] ?? 8);
        
        // Build CSS variables
        $css = <<<CSS
/* Uranus Events - Dynamic CSS generated from configuration */
/* Generated on: {$this->getCurrentTimestamp()} */

:root {
    /* Color Variables */
    --uranus-primary: {$primaryColor};
    --uranus-secondary: {$secondaryColor};
    --uranus-accent: {$accentColor};
    
    /* Design Variables */
    --uranus-border-radius: {$borderRadius}px;
    --uranus-font-family: {$fontFamily};
    
    /* Derived Colors */
    --uranus-primary-light: {$this->lightenColor($primaryColor, 20)};
    --uranus-primary-dark: {$this->darkenColor($primaryColor, 20)};
    --uranus-secondary-light: {$this->lightenColor($secondaryColor, 20)};
    --uranus-accent-light: {$this->lightenColor($accentColor, 20)};
    
    /* Spacing */
    --uranus-spacing-xs: 4px;
    --uranus-spacing-sm: 8px;
    --uranus-spacing-md: 16px;
    --uranus-spacing-lg: 24px;
    --uranus-spacing-xl: 32px;
}

/* Event List Styles */
.uranus-events-list {
    font-family: var(--uranus-font-family);
}

.uranus-event-card {
    border: 1px solid #e0e0e0;
    border-radius: var(--uranus-border-radius);
    border-left: 4px solid var(--uranus-primary);
    padding: var(--uranus-spacing-md);
    margin-bottom: var(--uranus-spacing-md);
    background-color: white;
    transition: box-shadow 0.3s ease;
}

.uranus-event-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.uranus-event-title {
    color: var(--uranus-primary);
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: var(--uranus-spacing-sm);
}

.uranus-event-meta {
    color: var(--uranus-secondary);
    font-size: 0.875rem;
    margin-bottom: var(--uranus-spacing-sm);
}

.uranus-event-date {
    color: var(--uranus-accent);
    font-weight: 500;
}

.uranus-event-excerpt {
    color: var(--uranus-secondary);
    line-height: 1.5;
    margin-bottom: var(--uranus-spacing-md);
}

/* Button Styles */
.uranus-button {
    background-color: var(--uranus-primary);
    color: white;
    border: none;
    border-radius: calc(var(--uranus-border-radius) / 2);
    padding: var(--uranus-spacing-sm) var(--uranus-spacing-md);
    font-family: var(--uranus-font-family);
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.uranus-button:hover {
    background-color: var(--uranus-primary-dark);
}

.uranus-button-secondary {
    background-color: var(--uranus-secondary);
}

.uranus-button-secondary:hover {
    background-color: var(--uranus-secondary-light);
}

.uranus-button-accent {
    background-color: var(--uranus-accent);
}

.uranus-button-accent:hover {
    background-color: var(--uranus-accent-light);
}

/* Tag Styles */
.uranus-tag {
    display: inline-block;
    background-color: var(--uranus-primary-light);
    color: var(--uranus-primary-dark);
    border-radius: 12px;
    padding: 2px 8px;
    font-size: 0.75rem;
    margin-right: var(--uranus-spacing-xs);
    margin-bottom: var(--uranus-spacing-xs);
}

/* Pagination Styles */
.uranus-pagination {
    display: flex;
    justify-content: center;
    gap: var(--uranus-spacing-sm);
    margin-top: var(--uranus-spacing-lg);
}

.uranus-pagination-item {
    padding: var(--uranus-spacing-sm) var(--uranus-spacing-md);
    border: 1px solid #e0e0e0;
    border-radius: calc(var(--uranus-border-radius) / 2);
    text-decoration: none;
    color: var(--uranus-secondary);
}

.uranus-pagination-item.active {
    background-color: var(--uranus-primary);
    color: white;
    border-color: var(--uranus-primary);
}

.uranus-pagination-item:hover:not(.active) {
    background-color: #f5f5f5;
}

/* Filter Styles */
.uranus-filter-container {
    background-color: #f9f9f9;
    border-radius: var(--uranus-border-radius);
    padding: var(--uranus-spacing-md);
    margin-bottom: var(--uranus-spacing-lg);
}

.uranus-filter-label {
    color: var(--uranus-secondary);
    font-weight: 500;
    margin-bottom: var(--uranus-spacing-xs);
}

.uranus-filter-select {
    border: 1px solid #ddd;
    border-radius: calc(var(--uranus-border-radius) / 2);
    padding: var(--uranus-spacing-sm);
    font-family: var(--uranus-font-family);
    width: 100%;
}

/* Responsive Design */
@media (max-width: 768px) {
    .uranus-event-card {
        padding: var(--uranus-spacing-sm);
    }
    
    .uranus-event-title {
        font-size: 1.125rem;
    }
    
    .uranus-pagination {
        flex-wrap: wrap;
    }
}

/* Accessibility */
.uranus-event-card:focus-within {
    outline: 2px solid var(--uranus-accent);
    outline-offset: 2px;
}

.uranus-button:focus {
    outline: 2px solid var(--uranus-accent);
    outline-offset: 2px;
}

CSS;
        
        return $css;
    }
    
    /**
     * Get cached CSS
     */
    private function getCachedCss(): ?string
    {
        if ($this->cache === null) {
            return null;
        }
        
        try {
            return $this->cache->get(self::CACHE_KEY);
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Cache generated CSS
     */
    private function cacheCss(string $css): void
    {
        if ($this->cache === null) {
            return;
        }
        
        try {
            $this->cache->set(
                self::CACHE_KEY,
                $css,
                [self::CACHE_TAG],
                86400 // 24 hours
            );
        } catch (\Exception $e) {
            // Silently fail if caching is not available
        }
    }
    
    /**
     * Clear CSS cache
     */
    public function clearCache(): void
    {
        if ($this->cache === null) {
            return;
        }
        
        try {
            $this->cache->flushByTag(self::CACHE_TAG);
        } catch (\Exception $e) {
            // Silently fail if cache doesn't exist
        }
    }
    
    /**
     * Lighten a color by percentage
     */
    private function lightenColor(string $color, int $percent): string
    {
        return $this->adjustColorBrightness($color, $percent);
    }
    
    /**
     * Darken a color by percentage
     */
    private function darkenColor(string $color, int $percent): string
    {
        return $this->adjustColorBrightness($color, -$percent);
    }
    
    /**
     * Adjust color brightness
     */
    private function adjustColorBrightness(string $color, int $percent): string
    {
        // Handle hex colors
        if (strpos($color, '#') === 0) {
            return $this->adjustHexColor($color, $percent);
        }
        
        // Handle rgb/rgba colors
        if (strpos($color, 'rgb') === 0) {
            return $this->adjustRgbColor($color, $percent);
        }
        
        // Return original color for named colors
        return $color;
    }
    
    /**
     * Adjust hex color brightness
     */
    private function adjustHexColor(string $hex, int $percent): string
    {
        // Remove # if present
        $hex = str_replace('#', '', $hex);
        
        // Handle short hex notation
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        // Convert to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        // Adjust brightness
        $r = max(0, min(255, $r + ($r * $percent / 100)));
        $g = max(0, min(255, $g + ($g * $percent / 100)));
        $b = max(0, min(255, $b + ($b * $percent / 100)));
        
        // Convert back to hex
        $rHex = str_pad(dechex((int)$r), 2, '0', STR_PAD_LEFT);
        $gHex = str_pad(dechex((int)$g), 2, '0', STR_PAD_LEFT);
        $bHex = str_pad(dechex((int)$b), 2, '0', STR_PAD_LEFT);
        
        return '#' . $rHex . $gHex . $bHex;
    }
    
    /**
     * Adjust RGB color brightness
     */
    private function adjustRgbColor(string $rgb, int $percent): string
    {
        // Extract RGB values
        preg_match('/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/', $rgb, $matches);
        
        if (empty($matches)) {
            return $rgb;
        }
        
        $r = (int)$matches[1];
        $g = (int)$matches[2];
        $b = (int)$matches[3];
        $a = isset($matches[4]) ? (float)$matches[4] : 1.0;
        
        // Adjust brightness
        $r = max(0, min(255, $r + ($r * $percent / 100)));
        $g = max(0, min(255, $g + ($g * $percent / 100)));
        $b = max(0, min(255, $b + ($b * $percent / 100)));
        
        if ($a < 1.0) {
            return sprintf('rgba(%d, %d, %d, %.2f)', $r, $g, $b, $a);
        }
        
        return sprintf('rgb(%d, %d, %d)', $r, $g, $b);
    }
    
    /**
     * Get current timestamp for CSS comment
     */
    private function getCurrentTimestamp(): string
    {
        return date('Y-m-d H:i:s');
    }
    
    /**
     * Get CSS for inclusion in page
     */
    public function getCssForInclusion(): string
    {
        $css = $this->generateCss();
        
        // Minify in production mode
        if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['production'] ?? false) {
            $css = $this->minifyCss($css);
        }
        
        return $css;
    }
    
    /**
     * Minify CSS
     */
    private function minifyCss(string $css): string
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remove whitespace
        $css = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $css);
        
        // Remove unnecessary spaces
        $css = preg_replace('/\s*([{}|:;,])\s*/', '$1', $css);
        $css = preg_replace('/;}/', '}', $css);
        
        return trim($css);
    }
    
    /**
     * Validate CSS color
     */
    public function isValidColor(string $color): bool
    {
        // Check hex color
        if (preg_match('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $color)) {
            return true;
        }
        
        // Check rgb/rgba color
        if (preg_match('/^rgb\(\d{1,3},\s*\d{1,3},\s*\d{1,3}\)$/', $color) ||
            preg_match('/^rgba\(\d{1,3},\s*\d{1,3},\s*\d{1,3},\s*(0|1|0?\.\d+)\)$/', $color)) {
            return true;
        }
        
        // Check named colors (basic set)
        $namedColors = [
            'black', 'white', 'red', 'green', 'blue', 'yellow', 'orange',
            'purple', 'pink', 'brown', 'gray', 'grey', 'transparent'
        ];
        
        return in_array(strtolower($color), $namedColors);
    }
}