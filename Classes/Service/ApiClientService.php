<?php
declare(strict_types=1);

namespace OklabFlensburg\UranusEvents\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Log\LogManager;

class ApiClientService
{
    private Client $client;
    private string $baseUrl;
    private string $endpoint;
    private int $timeout;
    private int $maxRetries;
    private LoggerInterface $logger;
    private bool $debugMode;
    
    public function __construct(
        ExtensionConfiguration $extensionConfiguration
    ) {
        $config = $extensionConfiguration->get('uranus_events');
        $this->baseUrl = rtrim($config['apiBaseUrl'] ?? 'https://uranus2.oklabflensburg.de', '/');
        $this->endpoint = $config['apiEndpoint'] ?? '/api/events';
        $this->timeout = (int)($config['httpTimeout'] ?? 30);
        $this->maxRetries = (int)($config['maxRetries'] ?? 3);
        $this->debugMode = (bool)($config['debugMode'] ?? false);
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'TYPO3-Uranus-Events/1.0',
            ],
        ]);
        
        // Create logger instance directly
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }
    
    public function getEvents(array $queryParams = []): array
    {
        $retryCount = 0;
        
        $this->logger->debug('Fetching events from Uranus API', [
            'endpoint' => $this->endpoint,
            'params' => $queryParams,
            'baseUrl' => $this->baseUrl,
        ]);
        
        while ($retryCount <= $this->maxRetries) {
            try {
                $response = $this->client->get($this->endpoint, [
                    'query' => $queryParams,
                ]);
                
                $data = $this->parseResponse($response);
                
                $this->logger->info('Successfully fetched events from Uranus API', [
                    'eventCount' => count($data['events'] ?? []),
                    'statusCode' => $response->getStatusCode(),
                ]);
                
                return $data;
                
            } catch (RequestException $e) {
                $retryCount++;
                $this->logger->error(
                    'API request failed (attempt ' . $retryCount . '/' . $this->maxRetries . ')',
                    [
                        'error' => $e->getMessage(),
                        'params' => $queryParams,
                        'endpoint' => $this->endpoint,
                    ]
                );
                
                if ($retryCount > $this->maxRetries) {
                    $this->logger->critical('Max retries exceeded for Uranus API request');
                    throw new \RuntimeException(
                        'Failed to fetch events from Uranus API after ' . $this->maxRetries . ' attempts',
                        0,
                        $e
                    );
                }
                
                // Exponential backoff
                $delay = 1000000 * (2 ** ($retryCount - 1)); // 1s, 2s, 4s in microseconds
                usleep($delay);
            } catch (\Exception $e) {
                $this->logger->error('Unexpected error during API request', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        }
        
        return [];
    }
    
    private function parseResponse(ResponseInterface $response): array
    {
        $body = (string)$response->getBody();
        
        if ($this->debugMode) {
            $this->logger->debug('API response body', ['body' => $body]);
        }
        
        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->logger->error('Failed to parse JSON response from Uranus API', [
                'error' => $e->getMessage(),
                'body' => substr($body, 0, 500),
            ]);
            throw new \RuntimeException('Invalid JSON response from Uranus API', 0, $e);
        }
        
        if (!is_array($data)) {
            $this->logger->error('Invalid API response format (not an array)', [
                'data' => $data,
            ]);
            throw new \RuntimeException('Invalid API response format');
        }
        
        if (!isset($data['events']) || !is_array($data['events'])) {
            $this->logger->warning('API response missing events array', [
                'data' => $data,
            ]);
            $data['events'] = [];
        }
        
        return $data;
    }
    
    public function getEventDetail(int $eventId, int $dateId, string $language = 'de'): array
    {
        $endpoint = '/api/event/' . $eventId . '/date/' . $dateId;
        $retryCount = 0;
        
        $this->logger->debug('Fetching event detail from Uranus API', [
            'endpoint' => $endpoint,
            'eventId' => $eventId,
            'dateId' => $dateId,
            'language' => $language,
        ]);
        
        while ($retryCount <= $this->maxRetries) {
            try {
                $response = $this->client->get($endpoint, [
                    'query' => ['lang' => $language],
                ]);
                
                $data = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
                
                $this->logger->info('Successfully fetched event detail from Uranus API', [
                    'eventId' => $eventId,
                    'dateId' => $dateId,
                    'statusCode' => $response->getStatusCode(),
                ]);
                
                // Extract the event data from the API response
                if (isset($data['data']) && is_array($data['data'])) {
                    return $data['data'];
                }
                
                return $data;
                
            } catch (RequestException $e) {
                $retryCount++;
                $this->logger->error(
                    'API request failed (attempt ' . $retryCount . '/' . $this->maxRetries . ')',
                    [
                        'error' => $e->getMessage(),
                        'eventId' => $eventId,
                        'dateId' => $dateId,
                        'endpoint' => $endpoint,
                    ]
                );
                
                if ($retryCount > $this->maxRetries) {
                    throw new \RuntimeException(
                        'Failed to fetch event detail from Uranus API after ' . $this->maxRetries . ' attempts',
                        0,
                        $e
                    );
                }
                
                // Exponential backoff
                $delay = 1000000 * (2 ** ($retryCount - 1));
                usleep($delay);
            }
        }
        
        return [];
    }
    
    public function getLookup(): array
    {
        try {
            $response = $this->client->get('/api/event/type-genre-lookup');
            $data = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            return $data['data'] ?? [];
        } catch (\Exception $e) {
            $this->logger->error('Failed to fetch type-genre lookup', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function testConnection(): bool
    {
        try {
            $start = microtime(true);
            $response = $this->client->get($this->endpoint, [
                'query' => ['limit' => 1],
            ]);
            $duration = microtime(true) - $start;
            
            $this->logger->info('API connection test successful', [
                'responseTime' => round($duration * 1000, 2) . 'ms',
                'statusCode' => $response->getStatusCode(),
            ]);
            
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            $this->logger->error('API connection test failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}