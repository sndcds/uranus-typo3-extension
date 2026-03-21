<?php
declare(strict_types=1);

namespace OklabFlensburg\UranusEvents\Service;

use OklabFlensburg\UranusEvents\Domain\Dto\FilterParameters;
use OklabFlensburg\UranusEvents\Domain\Model\EventResponse;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EventService
{
    private ApiClientService $apiClient;
    private FrontendInterface $cache;
    private int $cacheLifetime;
    private Logger $logger;
    private bool $debugMode;
    private ConfigurationService $configurationService;
    
    public function __construct(
        ApiClientService $apiClient,
        CacheManager $cacheManager,
        ConfigurationService $configurationService
    ) {
        $this->apiClient = $apiClient;
        $this->cache = $cacheManager->getCache('uranus_events');
        $this->configurationService = $configurationService;
        
        // Create logger instance directly
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        
        // Get configuration
        $config = $this->configurationService->getMergedConfiguration();
        $this->cacheLifetime = (int)($config['cacheLifetime'] ?? 3600);
        $this->debugMode = (bool)($config['debugMode'] ?? false);
    }
    
    public function getEvents(FilterParameters $filter): EventResponse
    {
        $cacheKey = $this->generateCacheKey($filter);
        
        // Cache lookup
        $cachedData = $this->cache->get($cacheKey);
        if ($cachedData !== false) {
            $this->logger->debug('Cache hit for events', [
                'cacheKey' => $cacheKey,
                'filter' => $filter->toQueryArray(),
            ]);
            return $cachedData;
        }
        
        $this->logger->debug('Cache miss for events, fetching from API', [
            'cacheKey' => $cacheKey,
            'filter' => $filter->toQueryArray(),
        ]);
        
        try {
            // API call
            $queryParams = $filter->toQueryArray();
            $apiData = $this->apiClient->getEvents($queryParams);
            
            // Map to domain models
            $eventResponse = EventResponse::fromApiResponse($apiData, $filter);
            
            // Store in cache
            $this->cache->set(
                $cacheKey,
                $eventResponse,
                $this->generateCacheTags($filter),
                $this->cacheLifetime
            );
            
            $this->logger->info('Successfully fetched and cached events', [
                'eventCount' => $eventResponse->getTotalCount(),
                'cacheKey' => $cacheKey,
                'cacheLifetime' => $this->cacheLifetime,
            ]);
            
            return $eventResponse;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get events', [
                'error' => $e->getMessage(),
                'filter' => $filter->toQueryArray(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Return empty response on error
            return new EventResponse([], 0, $filter->getLimit(), $filter->getOffset());
        }
    }
    
    public function getEventDetail(int $eventId, int $dateId, string $language = 'de'): ?array
    {
        $cacheKey = 'event_detail_' . $eventId . '_' . $dateId . '_' . $language;
        
        // Cache lookup
        $cachedData = $this->cache->get($cacheKey);
        if ($cachedData !== false) {
            $this->logger->debug('Cache hit for event detail', [
                'cacheKey' => $cacheKey,
                'eventId' => $eventId,
                'dateId' => $dateId,
            ]);
            return $cachedData;
        }
        
        try {
            $eventData = $this->apiClient->getEventDetail($eventId, $dateId, $language);
            
            // Store in cache
            $this->cache->set(
                $cacheKey,
                $eventData,
                ['event_detail'],
                $this->cacheLifetime
            );
            
            $this->logger->info('Successfully fetched and cached event detail', [
                'eventId' => $eventId,
                'dateId' => $dateId,
                'cacheKey' => $cacheKey,
            ]);
            
            return $eventData;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get event detail', [
                'error' => $e->getMessage(),
                'eventId' => $eventId,
                'dateId' => $dateId,
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
    
    public function getLookup(string $language = 'de'): array
    {
        $cacheKey = 'uranus_type_genre_lookup_' . $language;
        $cached = $this->cache->get($cacheKey);
        if ($cached !== false) {
            return $cached;
        }
        $lookup = $this->apiClient->getLookup();
        $langLookup = $lookup[$language]['types'] ?? [];
        $this->cache->set($cacheKey, $langLookup, ['uranus_events'], 86400);
        return $langLookup;
    }

    public function enrichEventWithLookup(\OklabFlensburg\UranusEvents\Domain\Model\Event $event, string $language = 'de'): void
    {
        $lookup = $this->getLookup($language);
        if (empty($lookup)) {
            return;
        }
        foreach ($event->getEventTypes() as $eventType) {
            $typeId = (string)$eventType->getTypeId();
            if ($eventType->getTypeName() === null) {
                $typeName = $lookup[$typeId]['name'] ?? null;
                $eventType->setTypeName($typeName);
            }
            $genreId = $eventType->getGenreId();
            if ($genreId !== null && $genreId > 0) {
                $genreName = $lookup[$typeId]['genres'][(string)$genreId] ?? null;
                $eventType->setGenreName($genreName);
            }
        }
    }

    public function getEventsWithFallback(FilterParameters $filter): EventResponse
    {
        try {
            return $this->getEvents($filter);
        } catch (\Exception $e) {
            $this->logger->warning('Using fallback for events due to error', [
                'error' => $e->getMessage(),
                'filter' => $filter->toQueryArray(),
            ]);
            
            // Try to get stale cache data
            $staleCacheKey = $this->generateStaleCacheKey($filter);
            $staleData = $this->cache->get($staleCacheKey);
            
            if ($staleData !== false) {
                $this->logger->info('Using stale cache data as fallback');
                return $staleData;
            }
            
            // Return empty response as last resort
            return new EventResponse([], 0, $filter->getLimit(), $filter->getOffset());
        }
    }
    
    public function clearCacheForFilter(FilterParameters $filter): void
    {
        $cacheKey = $this->generateCacheKey($filter);
        $this->cache->remove($cacheKey);
        
        $this->logger->info('Cleared cache for filter', [
            'cacheKey' => $cacheKey,
            'filter' => $filter->toQueryArray(),
        ]);
    }
    
    public function clearAllCache(): void
    {
        $this->cache->flush();
        $this->logger->info('Cleared all Uranus Events cache');
    }
    
    private function generateCacheKey(FilterParameters $filter): string
    {
        $keyData = [
            'start' => $filter->getStart()?->format('Y-m-d'),
            'end' => $filter->getEnd()?->format('Y-m-d'),
            'search' => $filter->getSearch(),
            'categories' => $filter->getCategories(),
            'organizations' => $filter->getOrganizations(),
            'venues' => $filter->getVenues(),
            'city' => $filter->getCity(),
            'countries' => $filter->getCountries(),
            'language' => $filter->getLanguage(),
            'limit' => $filter->getLimit(),
            'offset' => $filter->getOffset(),
            'lastEventDateId' => $filter->getLastEventDateId(),
            'lastEventStartAt' => $filter->getLastEventStartAt()?->format('Y-m-d\TH:i'),
            'past' => $filter->isPast(),
        ];
        
        // Remove null values for consistent hashing
        $keyData = array_filter($keyData, function ($value) {
            return $value !== null;
        });
        
        $keyString = json_encode($keyData, JSON_THROW_ON_ERROR);
        return 'uranus_events_' . md5($keyString);
    }
    
    private function generateStaleCacheKey(FilterParameters $filter): string
    {
        return $this->generateCacheKey($filter) . '_stale';
    }
    
    private function generateCacheTags(FilterParameters $filter): array
    {
        $tags = ['uranus_events'];
        
        // Add filter-specific tags
        if ($filter->getCity()) {
            $tags[] = 'uranus_events_city_' . md5($filter->getCity());
        }
        
        if ($filter->getCountries()) {
            foreach ($filter->getCountries() as $country) {
                $tags[] = 'uranus_events_country_' . $country;
            }
        }
        
        if ($filter->getCategories()) {
            foreach ($filter->getCategories() as $category) {
                $tags[] = 'uranus_events_category_' . $category;
            }
        }
        
        // Add date range tags
        if ($filter->getStart()) {
            $tags[] = 'uranus_events_start_' . $filter->getStart()->format('Y-m-d');
        }
        
        if ($filter->getEnd()) {
            $tags[] = 'uranus_events_end_' . $filter->getEnd()->format('Y-m-d');
        }
        
        return $tags;
    }
    
    public function warmupCache(array $commonFilters = []): void
    {
        if (empty($commonFilters)) {
            // Default common filters
            $commonFilters = [
                ['limit' => 20],
                ['limit' => 50],
                ['city' => 'Flensburg', 'limit' => 50],
                ['countries' => ['DEU'], 'limit' => 100],
                ['past' => false, 'limit' => 30],
            ];
        }
        
        $this->logger->info('Starting cache warmup', [
            'filterCount' => count($commonFilters),
        ]);
        
        foreach ($commonFilters as $filterData) {
            try {
                $filter = new FilterParameters($filterData);
                $this->getEvents($filter);
                
                $this->logger->debug('Warmed up cache for filter', [
                    'filter' => $filterData,
                ]);
            } catch (\Exception $e) {
                $this->logger->warning('Failed to warmup cache for filter', [
                    'filter' => $filterData,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        $this->logger->info('Cache warmup completed');
    }
    
    public function getApiStatus(): array
    {
        $status = [
            'cacheEnabled' => true,
            'cacheLifetime' => $this->cacheLifetime,
            'apiConnection' => false,
            'lastError' => null,
        ];
        
        try {
            $status['apiConnection'] = $this->apiClient->testConnection();
        } catch (\Exception $e) {
            $status['lastError'] = $e->getMessage();
        }
        
        return $status;
    }
}
