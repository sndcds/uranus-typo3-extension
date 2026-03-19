<?php
declare(strict_types=1);

namespace OklabFlensburg\UranusEvents\Service;

use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LoggingService
{
    private Logger $logger;
    
    public function __construct()
    {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)
            ->getLogger(__CLASS__);
    }
    
    public function logApiRequest(string $endpoint, array $params, float $duration, bool $success): void
    {
        $context = [
            'endpoint' => $endpoint,
            'params' => $params,
            'duration' => round($duration * 1000, 2) . 'ms',
            'success' => $success,
        ];
        
        if ($success) {
            $this->logger->info('API request completed', $context);
        } else {
            $this->logger->error('API request failed', $context);
        }
    }
    
    public function logCacheHit(string $cacheKey, array $filter): void
    {
        $this->logger->debug('Cache hit', [
            'cacheKey' => $cacheKey,
            'filter' => $filter,
        ]);
    }
    
    public function logCacheMiss(string $cacheKey, array $filter): void
    {
        $this->logger->debug('Cache miss', [
            'cacheKey' => $cacheKey,
            'filter' => $filter,
        ]);
    }
    
    public function logCacheWrite(string $cacheKey, int $itemCount, int $lifetime): void
    {
        $this->logger->debug('Cache write', [
            'cacheKey' => $cacheKey,
            'itemCount' => $itemCount,
            'lifetime' => $lifetime,
        ]);
    }
    
    public function logError(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }
    
    public function logWarning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }
    
    public function logInfo(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }
    
    public function logDebug(string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }
    
    public function getPerformanceMetrics(): array
    {
        // This would typically collect metrics from a metrics service
        // For now, return empty array
        return [
            'apiRequests' => 0,
            'cacheHits' => 0,
            'cacheMisses' => 0,
            'averageResponseTime' => 0,
        ];
    }
}