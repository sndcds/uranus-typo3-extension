<?php
declare(strict_types=1);

namespace OklabFlensburg\UranusEvents\Controller\Backend;

use OklabFlensburg\UranusEvents\Service\ConfigurationService;
use OklabFlensburg\UranusEvents\Service\CssGeneratorService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Backend Configuration Controller for Uranus Events Extension
 */
class ConfigurationController extends ActionController
{
    private ConfigurationService $configurationService;
    private CssGeneratorService $cssGeneratorService;
    private ModuleTemplateFactory $moduleTemplateFactory;
    private FlashMessageService $flashMessageService;
    
    public function __construct(
        ConfigurationService $configurationService,
        CssGeneratorService $cssGeneratorService,
        ModuleTemplateFactory $moduleTemplateFactory,
        FlashMessageService $flashMessageService
    ) {
        $this->configurationService = $configurationService;
        $this->cssGeneratorService = $cssGeneratorService;
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->flashMessageService = $flashMessageService;
    }
    
    /**
     * Main configuration action
     */
    public function indexAction(): ResponseInterface
    {
        $moduleTemplate = $this->initializeModuleTemplate();
        
        // Get current configuration
        $configuration = $this->configurationService->getBackendConfiguration();
        $mergedConfiguration = $this->configurationService->getMergedConfiguration();
        
        // Generate preview CSS
        $previewCss = $this->cssGeneratorService->generateCssForPreview($mergedConfiguration);
        
        // Get configuration by categories for tabbed interface
        $apiConfig = $this->configurationService->getConfigurationByCategory('api');
        $cacheConfig = $this->configurationService->getConfigurationByCategory('cache');
        $designConfig = $this->configurationService->getConfigurationByCategory('design');
        $displayConfig = $this->configurationService->getConfigurationByCategory('display');
        $debugConfig = $this->configurationService->getConfigurationByCategory('debug');
        
        // Assign variables to the backend module template view
        $moduleTemplate->assignMultiple([
            'configuration' => $configuration,
            'apiConfig' => $apiConfig,
            'cacheConfig' => $cacheConfig,
            'designConfig' => $designConfig,
            'displayConfig' => $displayConfig,
            'debugConfig' => $debugConfig,
            'previewCss' => $previewCss,
            'exportData' => $this->configurationService->exportConfiguration(),
        ]);

        return $moduleTemplate->renderResponse('Backend/Configuration/Index');
    }
    
    /**
     * Save configuration action
     */
    public function saveAction(array $configuration): ResponseInterface
    {
        try {
            // Save configuration
            $this->configurationService->saveBackendConfiguration($configuration);
            
            // Clear CSS cache
            $this->cssGeneratorService->clearCache();
            
            // Add success message
            $this->addFlashMessage(
                $this->translateLabel('configuration.saved', 'Configuration saved successfully'),
                $this->translateLabel('configuration.saved.title', 'Success'),
                ContextualFeedbackSeverity::OK,
                true
            );
            
        } catch (\InvalidArgumentException $e) {
            // Add error message
            $this->addFlashMessage(
                $e->getMessage(),
                $this->translateLabel('configuration.error', 'Error'),
                ContextualFeedbackSeverity::ERROR,
                true
            );
        }
        
        return new RedirectResponse($this->uriBuilder->uriFor('index'));
    }
    
    /**
     * Reset configuration to defaults
     */
    public function resetAction(): ResponseInterface
    {
        // Get default configuration
        $defaults = [
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
        
        $this->configurationService->saveBackendConfiguration($defaults);
        $this->cssGeneratorService->clearCache();
        
        $this->addFlashMessage(
            $this->translateLabel('configuration.reset', 'Configuration reset to defaults'),
            $this->translateLabel('configuration.reset.title', 'Reset Complete'),
            ContextualFeedbackSeverity::OK,
            true
        );
        
        return new RedirectResponse($this->uriBuilder->uriFor('index'));
    }
    
    /**
     * Export configuration as JSON file
     */
    public function exportAction(): ResponseInterface
    {
        $json = $this->configurationService->exportConfiguration();
        
        $response = new \TYPO3\CMS\Core\Http\Response();
        $response->getBody()->write($json);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->withHeader(
            'Content-Disposition',
            'attachment; filename="uranus-events-config-' . date('Y-m-d') . '.json"'
        );
        
        return $response;
    }
    
    /**
     * Import configuration from JSON
     */
    public function importAction(array $import): ResponseInterface
    {
        if (empty($import['json'])) {
            $this->addFlashMessage(
                $this->translateLabel('configuration.import.empty', 'No configuration data provided'),
                $this->translateLabel('configuration.import.error', 'Import Error'),
                ContextualFeedbackSeverity::ERROR,
                true
            );
            return new RedirectResponse($this->uriBuilder->uriFor('index'));
        }
        
        try {
            $this->configurationService->importConfiguration($import['json']);
            $this->cssGeneratorService->clearCache();
            
            $this->addFlashMessage(
                $this->translateLabel('configuration.import.success', 'Configuration imported successfully'),
                $this->translateLabel('configuration.import.success.title', 'Import Complete'),
                ContextualFeedbackSeverity::OK,
                true
            );
            
        } catch (\InvalidArgumentException $e) {
            $this->addFlashMessage(
                $e->getMessage(),
                $this->translateLabel('configuration.import.error', 'Import Error'),
                ContextualFeedbackSeverity::ERROR,
                true
            );
        }
        
        return new RedirectResponse($this->uriBuilder->uriFor('index'));
    }
    
    /**
     * Preview action for live preview of design changes
     */
    public function previewAction(array $configuration): ResponseInterface
    {
        // Generate CSS for preview
        $css = $this->cssGeneratorService->generateCssForPreview($configuration);
        
        $response = new \TYPO3\CMS\Core\Http\Response();
        $response->getBody()->write($css);
        $response = $response->withHeader('Content-Type', 'text/css');
        
        return $response;
    }
    
    /**
     * Initialize module template
     */
    private function initializeModuleTemplate(): ModuleTemplate
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $title = $this->translateLabel('module.title', 'Uranus Events Configuration');
        $moduleTemplate->setTitle(
            $title
        );
        
        // Note: JavaScript for color pickers and live preview would be added here
        // In TYPO3 14.1+, use $moduleTemplate->getJavaScriptRenderer() if available
        // or add via PageRenderer service
        
        // Add CSS for backend module (if file exists)
        // $moduleTemplate->addCssFile('EXT:uranus_events/Resources/Public/Css/backend-configuration.css');
        
        return $moduleTemplate;
    }
    
    /**
     * Add flash message
     */
    public function addFlashMessage(
        string $message,
        string $title = '',
        ContextualFeedbackSeverity $severity = ContextualFeedbackSeverity::OK,
        bool $storeInSession = false
    ): void {
        $flashMessage = new FlashMessage($message, $title, $severity, $storeInSession);
        $this->flashMessageService->getMessageQueueByIdentifier()->addMessage($flashMessage);
    }

    private function translateLabel(string $key, string $fallback): string
    {
        return LocalizationUtility::translate($key, 'uranus_events') ?? $fallback;
    }
}
