<?php
declare(strict_types=1);

namespace OklabFlensburg\UranusEvents\Tests\Unit\Service;

use OklabFlensburg\UranusEvents\Service\ConfigurationService;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ConfigurationServiceTest extends UnitTestCase
{
    private ConfigurationService $configurationService;
    private ExtensionConfiguration $extensionConfigurationMock;
    private Registry $registryMock;
    private ConfigurationManagerInterface $configurationManagerMock;
    private TypoScriptService $typoScriptServiceMock;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->extensionConfigurationMock = $this->createMock(ExtensionConfiguration::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->configurationManagerMock = $this->createMock(ConfigurationManagerInterface::class);
        $this->typoScriptServiceMock = $this->createMock(TypoScriptService::class);
        
        $this->configurationService = new ConfigurationService(
            $this->extensionConfigurationMock,
            $this->registryMock,
            $this->configurationManagerMock,
            $this->typoScriptServiceMock
        );
    }
    
    public function testGetMergedConfigurationReturnsDefaultValues(): void
    {
        // Mock empty configuration from all sources
        $this->extensionConfigurationMock->method('get')
            ->with('uranus_events')
            ->willReturn([]);
        
        $this->registryMock->method('get')
            ->with('tx_uranusevents', 'configuration', [])
            ->willReturn([]);
        
        $this->configurationManagerMock->method('getConfiguration')
            ->willReturn([]);
        
        $config = $this->configurationService->getMergedConfiguration();
        
        // Check that default values are present
        $this->assertArrayHasKey('apiBaseUrl', $config);
        $this->assertArrayHasKey('defaultPrimaryColor', $config);
        $this->assertArrayHasKey('defaultEventsPerPage', $config);
        
        $this->assertEquals('https://uranus2.oklabflensburg.de', $config['apiBaseUrl']);
        $this->assertEquals('#0066cc', $config['defaultPrimaryColor']);
        $this->assertEquals(20, $config['defaultEventsPerPage']);
    }
    
    public function testGetMergedConfigurationWithExtensionConfiguration(): void
    {
        $extensionConfig = [
            'apiBaseUrl' => 'https://custom-api.example.com',
            'defaultPrimaryColor' => '#ff0000',
            'cacheLifetime' => 7200,
        ];
        
        $this->extensionConfigurationMock->method('get')
            ->with('uranus_events')
            ->willReturn($extensionConfig);
        
        $this->registryMock->method('get')
            ->with('tx_uranusevents', 'configuration', [])
            ->willReturn([]);
        
        $this->configurationManagerMock->method('getConfiguration')
            ->willReturn([]);
        
        $config = $this->configurationService->getMergedConfiguration();
        
        $this->assertEquals('https://custom-api.example.com', $config['apiBaseUrl']);
        $this->assertEquals('#ff0000', $config['defaultPrimaryColor']);
        $this->assertEquals(7200, $config['cacheLifetime']);
    }
    
    public function testGetMergedConfigurationWithBackendConfiguration(): void
    {
        $backendConfig = [
            'apiBaseUrl' => 'https://backend-config.example.com',
            'defaultPrimaryColor' => '#00ff00',
            'defaultEventsPerPage' => 50,
        ];
        
        $this->extensionConfigurationMock->method('get')
            ->with('uranus_events')
            ->willReturn([]);
        
        $this->registryMock->method('get')
            ->with('tx_uranusevents', 'configuration', [])
            ->willReturn($backendConfig);
        
        $this->configurationManagerMock->method('getConfiguration')
            ->willReturn([]);
        
        $config = $this->configurationService->getMergedConfiguration();
        
        // Backend config should override extension config
        $this->assertEquals('https://backend-config.example.com', $config['apiBaseUrl']);
        $this->assertEquals('#00ff00', $config['defaultPrimaryColor']);
        $this->assertEquals(50, $config['defaultEventsPerPage']);
    }
    
    public function testGetMergedConfigurationWithPluginSettings(): void
    {
        $pluginSettings = [
            'limit' => 30,
            'showImages' => 0,
            'dateFormat' => 'Y-m-d',
        ];
        
        $this->extensionConfigurationMock->method('get')
            ->with('uranus_events')
            ->willReturn([]);
        
        $this->registryMock->method('get')
            ->with('tx_uranusevents', 'configuration', [])
            ->willReturn([]);
        
        $this->configurationManagerMock->method('getConfiguration')
            ->willReturn([]);
        
        $config = $this->configurationService->getMergedConfiguration($pluginSettings);
        
        // Plugin settings should be mapped and override other configs
        $this->assertEquals(30, $config['defaultEventsPerPage']);
        $this->assertEquals(false, $config['defaultShowImages']);
        $this->assertEquals('Y-m-d', $config['defaultDateFormat']);
    }
    
    public function testGetConfigurationByCategory(): void
    {
        $fullConfig = [
            'apiBaseUrl' => 'https://example.com',
            'apiEndpoint' => '/api/events',
            'httpTimeout' => 60,
            'maxRetries' => 5,
            'cacheLifetime' => 7200,
            'defaultPrimaryColor' => '#ff0000',
            'defaultSecondaryColor' => '#00ff00',
            'defaultAccentColor' => '#0000ff',
            'defaultFontFamily' => 'Arial, sans-serif',
            'defaultBorderRadius' => 10,
            'defaultDateFormat' => 'Y-m-d',
            'defaultTimeFormat' => 'H:i:s',
            'defaultEventsPerPage' => 30,
            'defaultShowImages' => true,
            'defaultShowVenueMap' => false,
            'defaultShowOrganization' => true,
            'defaultShowTags' => false,
            'defaultShowExcerpt' => true,
            'defaultShowReadMoreLink' => false,
            'debugMode' => true,
        ];
        
        // Mock the service to return our test config
        $this->extensionConfigurationMock->method('get')
            ->with('uranus_events')
            ->willReturn($fullConfig);
        
        $this->registryMock->method('get')
            ->with('tx_uranusevents', 'configuration', [])
            ->willReturn([]);
        
        $this->configurationManagerMock->method('getConfiguration')
            ->willReturn([]);
        
        // Test API category
        $apiConfig = $this->configurationService->getConfigurationByCategory('api');
        $this->assertArrayHasKey('apiBaseUrl', $apiConfig);
        $this->assertArrayHasKey('apiEndpoint', $apiConfig);
        $this->assertArrayHasKey('httpTimeout', $apiConfig);
        $this->assertArrayHasKey('maxRetries', $apiConfig);
        $this->assertArrayNotHasKey('defaultPrimaryColor', $apiConfig);
        
        // Test design category
        $designConfig = $this->configurationService->getConfigurationByCategory('design');
        $this->assertArrayHasKey('defaultPrimaryColor', $designConfig);
        $this->assertArrayHasKey('defaultSecondaryColor', $designConfig);
        $this->assertArrayHasKey('defaultAccentColor', $designConfig);
        $this->assertArrayHasKey('defaultFontFamily', $designConfig);
        $this->assertArrayHasKey('defaultBorderRadius', $designConfig);
        $this->assertArrayNotHasKey('apiBaseUrl', $designConfig);
        
        // Test display category
        $displayConfig = $this->configurationService->getConfigurationByCategory('display');
        $this->assertCount(9, $displayConfig); // 9 display settings
        $this->assertArrayHasKey('defaultDateFormat', $displayConfig);
        $this->assertArrayHasKey('defaultEventsPerPage', $displayConfig);
    }
    
    public function testSaveBackendConfiguration(): void
    {
        $configuration = [
            'apiBaseUrl' => 'https://test.example.com',
            'defaultPrimaryColor' => '#123456',
            'defaultEventsPerPage' => 25,
            'defaultShowImages' => true,
        ];
        
        $this->registryMock->expects($this->once())
            ->method('set')
            ->with('tx_uranusevents', 'configuration', $this->callback(function($arg) {
                // Check that boolean values are normalized to 0/1 for storage
                return $arg['defaultShowImages'] === 1;
            }));
        
        $this->configurationService->saveBackendConfiguration($configuration);
    }
    
    public function testSaveBackendConfigurationWithInvalidColor(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid color format');
        
        $configuration = [
            'defaultPrimaryColor' => 'invalid-color',
        ];
        
        $this->configurationService->saveBackendConfiguration($configuration);
    }
    
    public function testSaveBackendConfigurationWithInvalidNumericValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be between');
        
        $configuration = [
            'cacheLifetime' => 100000, // Too high
        ];
        
        $this->configurationService->saveBackendConfiguration($configuration);
    }
    
    public function testExportImportConfiguration(): void
    {
        $configuration = [
            'apiBaseUrl' => 'https://export.example.com',
            'defaultPrimaryColor' => '#abcdef',
            'defaultEventsPerPage' => 40,
        ];
        
        // Mock backend configuration
        $this->registryMock->method('get')
            ->with('tx_uranusevents', 'configuration', [])
            ->willReturn($configuration);
        
        // Test export
        $json = $this->configurationService->exportConfiguration();
        $this->assertJson($json);
        
        $decoded = json_decode($json, true);
        $this->assertEquals('https://export.example.com', $decoded['apiBaseUrl']);
        $this->assertEquals('#abcdef', $decoded['defaultPrimaryColor']);
        
        // Test import
        $newJson = '{"apiBaseUrl":"https://import.example.com","defaultPrimaryColor":"#654321"}';
        
        $this->registryMock->expects($this->once())
            ->method('set')
            ->with('tx_uranusevents', 'configuration', $this->callback(function($arg) {
                return $arg['apiBaseUrl'] === 'https://import.example.com';
            }));
        
        $this->configurationService->importConfiguration($newJson);
    }
    
    public function testImportConfigurationWithInvalidJson(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid JSON format');
        
        $this->configurationService->importConfiguration('invalid json');
    }
}