# Configuration Guide for Uranus Events Extension

## Overview

The Uranus Events Extension now features a comprehensive configuration system that allows administrators to customize all aspects of the extension through both the Extension Manager and a dedicated Backend Module.

## Configuration Sources and Priority

The extension uses a multi-layer configuration system with the following priority (highest to lowest):

1. **Plugin FlexForm Settings** (page-specific)
2. **Backend Module Configuration** (global, stored in `sys_registry`)
3. **Extension Configuration** (global, from `ext_conf_template.txt`)
4. **TypoScript Constants** (fallback, legacy)

## Configuration Options

### 1. API Settings

| Setting | Default | Description | Validation |
|---------|---------|-------------|------------|
| `apiBaseUrl` | `https://uranus2.oklabflensburg.de` | Base URL of the Uranus API | Valid URL |
| `apiEndpoint` | `/api/events` | API endpoint for events | String |
| `httpTimeout` | `30` | HTTP timeout in seconds (1-300) | Integer 1-300 |
| `maxRetries` | `3` | Maximum retry attempts (0-10) | Integer 0-10 |

### 2. Cache Settings

| Setting | Default | Description | Validation |
|---------|---------|-------------|------------|
| `cacheLifetime` | `3600` | Cache lifetime in seconds (0-86400) | Integer 0-86400 |

### 3. Design Settings

| Setting | Default | Description | Validation |
|---------|---------|-------------|------------|
| `defaultPrimaryColor` | `#0066cc` | Primary brand color | CSS color |
| `defaultSecondaryColor` | `#333333` | Secondary color | CSS color |
| `defaultAccentColor` | `#ff6600` | Accent color | CSS color |
| `defaultFontFamily` | `'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif` | Font family stack | String |
| `defaultBorderRadius` | `8` | Border radius in pixels (0-50) | Integer 0-50 |

### 4. Display Settings

| Setting | Default | Description | Validation |
|---------|---------|-------------|------------|
| `defaultDateFormat` | `d.m.Y` | PHP date format | String |
| `defaultTimeFormat` | `H:i` | PHP time format | String |
| `defaultEventsPerPage` | `20` | Events per page (1-100) | Integer 1-100 |
| `defaultShowImages` | `true` | Show event images | Boolean |
| `defaultShowVenueMap` | `true` | Show venue map | Boolean |
| `defaultShowOrganization` | `true` | Show organization info | Boolean |
| `defaultShowTags` | `true` | Show event tags | Boolean |
| `defaultShowExcerpt` | `true` | Show description excerpt | Boolean |
| `defaultShowReadMoreLink` | `true` | Show "Read More" link | Boolean |

### 5. Debug Settings

| Setting | Default | Description | Validation |
|---------|---------|-------------|------------|
| `debugMode` | `false` | Enable debug mode | Boolean |

## Configuration Methods

### Method 1: Extension Manager

Basic configuration is available through the TYPO3 Extension Manager:

1. Go to **Admin Tools → Extensions**
2. Find **Uranus Events** and click the gear icon
3. Configure basic settings in the form
4. Save and clear caches

### Method 2: Backend Module (Recommended)

For full configuration with live preview:

1. Go to **Uranus Events → Configuration** in the main menu
2. Use the tabbed interface to configure all settings
3. Use the live preview to see design changes in real-time
4. Save, export, or reset configuration as needed

## Backend Module Features

### Tabbed Interface
- **API Settings**: Configure API connection parameters
- **Cache Settings**: Configure caching behavior
- **Design Settings**: Customize colors, fonts, and styling with live preview
- **Display Settings**: Configure how events are displayed
- **Debug Settings**: Enable debug mode and logging

### Advanced Features
- **Live Preview**: See design changes in real-time
- **Import/Export**: Backup and restore configurations
- **Reset to Defaults**: Restore factory settings
- **Validation**: Input validation with helpful error messages
- **Color Pickers**: Visual color selection for design settings

## Dynamic CSS Generation

The extension generates CSS dynamically based on configuration:

### CSS Variables
```css
:root {
    --uranus-primary: #0066cc;
    --uranus-secondary: #333333;
    --uranus-accent: #ff6600;
    --uranus-border-radius: 8px;
    --uranus-font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    --uranus-primary-light: #3388ff;
    --uranus-primary-dark: #0055aa;
}
```

### Generated Classes
- `.uranus-event-card` - Event container
- `.uranus-event-title` - Event title
- `.uranus-event-date` - Event date
- `.uranus-button` - Primary button
- `.uranus-tag` - Event tag
- `.uranus-pagination` - Pagination container

## Integration with Frontend

### Template Variables
The following variables are available in Fluid templates:

```html
<!-- Dynamic CSS -->
<style>{dynamicCss}</style>

<!-- Configuration values -->
<f:if condition="{configuration.defaultShowImages}">
    <img src="{event.imageUrl}" alt="{event.title}" />
</f:if>

<!-- Using CSS variables -->
<div class="uranus-event-card" style="border-left-color: var(--uranus-primary);">
    <!-- Event content -->
</div>
```

### Available Configuration in Templates
- `{configuration}` - Complete configuration array
- `{configuration.defaultPrimaryColor}` - Primary color value
- `{configuration.defaultDateFormat}` - Date format string
- `{dynamicCss}` - Generated CSS for inclusion

## Migration from Previous Versions

### TypoScript to Configuration Migration
Existing TypoScript settings are automatically migrated when:
1. The Backend Module is accessed for the first time
2. Or when configuration is reset to defaults

### Plugin Settings Compatibility
Plugin FlexForm settings continue to work and have the highest priority:
- `limit` → `defaultEventsPerPage`
- `showImages` → `defaultShowImages`
- `dateFormat` → `defaultDateFormat`
- etc.

## Testing

### Unit Tests
Run the test suite to verify configuration functionality:

```bash
./vendor/bin/phpunit Tests/Unit/Service/ConfigurationServiceTest.php
./vendor/bin/phpunit Tests/Unit/Service/CssGeneratorServiceTest.php
```

### Manual Testing
1. Access the Backend Module and verify all tabs load
2. Test saving configuration
3. Verify live preview updates
4. Test import/export functionality
5. Verify frontend displays configured styles

## Troubleshooting

### Common Issues

1. **Configuration not saving**
   - Check user permissions (admin required)
   - Clear TYPO3 and PHP caches
   - Check `sys_registry` table for existing entries

2. **CSS not updating**
   - Clear TYPO3 cache (`uranus_events_css` cache)
   - Check browser cache
   - Verify CSS generation in Backend Module preview

3. **Color validation errors**
   - Use valid CSS colors: hex (`#rrggbb`), rgb(`rgb(r,g,b)`), or named colors
   - Color picker in Backend Module helps with selection

4. **Backend Module not appearing**
   - Clear TYPO3 cache
   - Check extension is active
   - Verify user has admin privileges

### Debug Mode
Enable debug mode to get detailed logging:
1. Set `debugMode = 1` in Extension Manager or Backend Module
2. Check TYPO3 system log for configuration-related messages
3. View browser console for CSS generation errors

## Best Practices

### Configuration Management
1. **Use Backend Module** for most configuration changes
2. **Export configuration** before major updates
3. **Test changes** in staging environment first
4. **Document custom configurations** for team reference

### Performance Considerations
1. CSS is cached for 24 hours
2. Configuration is cached per request
3. Consider CDN for generated CSS in production

### Security Considerations
1. API URLs are validated
2. Configuration stored in `sys_registry` is protected
3. Admin privileges required for configuration changes

## API Reference

### ConfigurationService Methods

```php
// Get merged configuration
$config = $configurationService->getMergedConfiguration($pluginSettings);

// Get configuration by category
$apiConfig = $configurationService->getConfigurationByCategory('api');

// Save backend configuration
$configurationService->saveBackendConfiguration($config);

// Export/import configuration
$json = $configurationService->exportConfiguration();
$configurationService->importConfiguration($json);
```

### CssGeneratorService Methods

```php
// Generate CSS
$css = $cssGeneratorService->generateCss();

// Get CSS for inclusion in page
$css = $cssGeneratorService->getCssForInclusion();

// Generate preview CSS
$previewCss = $cssGeneratorService->generateCssForPreview($config);

// Clear CSS cache
$cssGeneratorService->clearCache();
```

## Examples

### Example Configuration JSON
```json
{
    "apiBaseUrl": "https://api.example.com",
    "apiEndpoint": "/v2/events",
    "cacheLifetime": 7200,
    "defaultPrimaryColor": "#2c5282",
    "defaultSecondaryColor": "#4a5568",
    "defaultAccentColor": "#d69e2e",
    "defaultFontFamily": "'Inter', -apple-system, BlinkMacSystemFont, sans-serif",
    "defaultBorderRadius": 12,
    "defaultDateFormat": "F j, Y",
    "defaultTimeFormat": "g:i A",
    "defaultEventsPerPage": 15,
    "defaultShowImages": true,
    "defaultShowVenueMap": false,
    "defaultShowOrganization": true,
    "defaultShowTags": true,
    "defaultShowExcerpt": true,
    "defaultShowReadMoreLink": true,
    "debugMode": false
}
```

### Example TypoScript Override
```typoscript
plugin.tx_uranusevents.settings {
    defaultPrimaryColor = #2c5282
    defaultEventsPerPage = 30
    defaultShowVenueMap = 0
}
```

## Support

For issues with configuration:
1. Check this documentation
2. Review TYPO3 system log
3. Test with default configuration
4. Contact extension maintainers if issues persist

## Changelog

### Version 1.1.0
- Added comprehensive configuration system
- Added Backend Module with live preview
- Added dynamic CSS generation
- Added import/export functionality
- Added validation for all settings
- Maintained backward compatibility with existing configurations