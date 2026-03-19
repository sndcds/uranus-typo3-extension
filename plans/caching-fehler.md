# Caching- und Fehlerbehandlungsstrategie

## Caching-Strategie

### 1. Cache-Konfiguration

#### Cache-Backend
Die Extension verwendet den TYPO3-Cache-Manager mit einem eigenen Cache-Backend:
```php
// In ext_localconf.php
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['uranus_events'] = [
    'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
    'backend' => \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class,
    'options' => [
        'defaultLifetime' => 3600, // 1 Stunde Standard
    ],
    'groups' => ['pages', 'all'],
];
```

#### Cache-Tags
Verwendung von Cache-Tags für gezielte Invalidation:
- `uranus_events`: Allgemeiner Tag für alle Event-Daten
- `uranus_events_filter_[hash]`: Spezifischer Tag für Filter-Kombinationen
- `uranus_events_page_[pid]`: Seiten-spezifischer Cache

### 2. Cache-Schlüssel-Generierung

Cache-Schlüssel basieren auf Filter-Parametern:
```php
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
        'past' => $filter->isPast(),
    ];
    
    return 'events_' . md5(serialize($keyData));
}
```

### 3. Cache-Lebensdauer

Konfigurierbare Cache-Lebensdauern:
- **Standard**: 3600 Sekunden (1 Stunde)
- **Für häufig ändernde Daten**: 300 Sekunden (5 Minuten)
- **Für statische Filter**: 86400 Sekunden (24 Stunden)

Konfiguration über Extension-Einstellungen:
```plaintext
# cat=cache; type=int+; label=Standard Cache Lifetime (Sekunden)
cacheLifetime = 3600

# cat=cache; type=int+; label=Cache Lifetime für Suchfilter (Sekunden)
cacheLifetimeSearch = 300

# cat=cache; type=int+; label=Cache Lifetime für Datumsfilter (Sekunden)
cacheLifetimeDate = 1800
```

### 4. Cache-Invalidation

#### Manuelle Invalidation
```php
// Cache komplett leeren
$this->cache->flushByTag('uranus_events');

// Spezifischen Filter-Cache invalidieren
$this->cache->remove($cacheKey);

// Alle Caches einer Seite invalidieren
$this->cache->flushByTag('uranus_events_page_' . $pageId);
```

#### Automatische Invalidation bei:
- Änderung der Extension-Konfiguration
- Manueller Cache-Löschung im Backend
- TYPO3-Cache-Clear-Befehl

### 5. Cache-Warmup

Optionaler Cache-Warmup über Scheduler-Task:
```php
class CacheWarmupTask extends AbstractTask
{
    public function execute(): bool
    {
        // Häufige Filter-Kombinationen vorab laden
        $commonFilters = [
            new FilterParameters(['limit' => 20]),
            new FilterParameters(['city' => 'Flensburg', 'limit' => 50]),
            new FilterParameters(['countries' => ['DEU'], 'limit' => 100]),
        ];
        
        foreach ($commonFilters as $filter) {
            $this->eventService->getEvents($filter);
        }
        
        return true;
    }
}
```

## Fehlerbehandlungsstrategie

### 1. API-Fehlerbehandlung

#### HTTP-Fehler
```php
try {
    $response = $this->client->get($this->endpoint, ['query' => $queryParams]);
    
    $statusCode = $response->getStatusCode();
    
    if ($statusCode >= 400 && $statusCode < 500) {
        // Client-Fehler (4xx)
        $this->logger->warning(
            'Uranus API client error',
            ['status' => $statusCode, 'params' => $queryParams]
        );
        throw new ApiClientException('API request failed with client error');
    }
    
    if ($statusCode >= 500) {
        // Server-Fehler (5xx)
        $this->logger->error(
            'Uranus API server error',
            ['status' => $statusCode, 'params' => $queryParams]
        );
        throw new ApiServerException('API server error');
    }
    
} catch (RequestException $e) {
    // Netzwerk-Fehler, Timeout, etc.
    $this->logger->error(
        'Uranus API network error',
        ['message' => $e->getMessage(), 'params' => $queryParams]
    );
    throw new ApiNetworkException('Network error contacting Uranus API');
}
```

#### Retry-Mechanismus
```php
private function executeWithRetry(callable $request, int $maxRetries = 3): mixed
{
    $retryCount = 0;
    $lastException = null;
    
    while ($retryCount <= $maxRetries) {
        try {
            return $request();
        } catch (ApiNetworkException | ApiServerException $e) {
            $retryCount++;
            $lastException = $e;
            
            if ($retryCount > $maxRetries) {
                break;
            }
            
            // Exponential backoff
            $delay = 1000 * (2 ** ($retryCount - 1)); // 1s, 2s, 4s
            usleep($delay * 1000);
            
            $this->logger->info(
                'Retrying API request',
                ['attempt' => $retryCount, 'delay' => $delay]
            );
        }
    }
    
    throw $lastException ?? new \RuntimeException('Max retries exceeded');
}
```

### 2. Daten-Validierung

#### JSON-Parsing und Validierung
```php
private function validateApiResponse(array $data): void
{
    // Pflichtfelder prüfen
    if (!isset($data['events']) || !is_array($data['events'])) {
        throw new InvalidApiResponseException('Missing or invalid events array');
    }
    
    // Events validieren
    foreach ($data['events'] as $index => $event) {
        if (!isset($event['id'], $event['title'], $event['start_date'])) {
            throw new InvalidApiResponseException(
                sprintf('Event at index %d missing required fields', $index)
            );
        }
        
        // Datums-Validierung
        if (!strtotime($event['start_date'])) {
            throw new InvalidApiResponseException(
                sprintf('Invalid start_date in event %d', $event['id'])
            );
        }
    }
    
    // Pagination-Felder
    if (isset($data['last_event_date_id']) && !is_int($data['last_event_date_id'])) {
        throw new InvalidApiResponseException('Invalid last_event_date_id');
    }
}
```

### 3. Fallback-Strategien

#### Cache-Fallback
```php
public function getEventsWithFallback(FilterParameters $filter): EventResponse
{
    try {
        return $this->getEvents($filter);
    } catch (ApiException $e) {
        // Versuche, ältere gecachte Daten zu verwenden
        $staleCacheKey = $this->generateStaleCacheKey($filter);
        $staleData = $this->cache->get($staleCacheKey);
        
        if ($staleData !== false) {
            $this->logger->warning(
                'Using stale cache data due to API error',
                ['error' => $e->getMessage()]
            );
            return $staleData;
        }
        
        // Keine Fallback-Daten verfügbar
        throw $e;
    }
}
```

#### Leere Response als Fallback
```php
public function getEventsSafe(FilterParameters $filter): EventResponse
{
    try {
        return $this->getEvents($filter);
    } catch (\Exception $e) {
        $this->logger->error(
            'Failed to load events, returning empty response',
            ['error' => $e->getMessage(), 'filter' => $filter->toArray()]
        );
        
        // Leere Response zurückgeben
        return new EventResponse([], 0, $filter->getLimit(), $filter->getOffset());
    }
}
```

### 4. Logging-Strategie

#### Log-Level Definition
- **DEBUG**: Detaillierte API-Aufrufe, Cache-Hits/Misses
- **INFO**: Erfolgreiche API-Abfragen, Cache-Writes
- **WARNING**: API-Client-Fehler (4xx), veraltete Cache-Daten
- **ERROR**: API-Server-Fehler (5xx), Netzwerk-Fehler, Validierungsfehler
- **CRITICAL**: System-Fehler, die die Extension komplett lahmlegen

#### Logger-Konfiguration
```php
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Log\Logger;

class ApiClientService
{
    private Logger $logger;
    
    public function __construct()
    {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)
            ->getLogger(__CLASS__);
    }
    
    public function getEvents(array $queryParams): array
    {
        $this->logger->debug('Fetching events from API', ['params' => $queryParams]);
        
        try {
            // API-Aufruf
            $this->logger->info('API request successful');
            return $data;
        } catch (\Exception $e) {
            $this->logger->error('API request failed', [
                'error' => $e->getMessage(),
                'params' => $queryParams,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
```

### 5. Frontend-Fehlerbehandlung

#### Controller-Fehlerbehandlung
```php
public function listAction(): ResponseInterface
{
    try {
        $filter = $this->createFilterFromSettings();
        $eventResponse = $this->eventService->getEventsSafe($filter);
        
        $this->view->assignMultiple([
            'events' => $eventResponse->getEvents(),
            'pagination' => $this->createPaginationData($eventResponse),
            'hasError' => false,
        ]);
        
    } catch (\Exception $e) {
        $this->logger->error('EventController error', [
            'error' => $e->getMessage(),
            'settings' => $this->settings
        ]);
        
        $this->view->assignMultiple([
            'events' => [],
            'hasError' => true,
            'errorMessage' => 'Events konnten nicht geladen werden.',
            'errorDetails' => $this->shouldShowErrorDetails() ? $e->getMessage() : null,
        ]);
    }
    
    return $this->htmlResponse();
}
```

#### Template-Fehleranzeige
```html
<f:if condition="{hasError}">
    <div class="alert alert-error" role="alert">
        <h3>Fehler beim Laden der Events</h3>
        <p>{errorMessage}</p>
        
        <f:if condition="{errorDetails} && {showDebug}">
            <details>
                <summary>Technische Details</summary>
                <pre>{errorDetails}</pre>
            </details>
        </f:if>
        
        <p>
            <f:link.action action="list" class="btn btn-retry">
                Erneut versuchen
            </f:link.action>
        </p>
    </div>
</f:if>
```

### 6. Monitoring und Alerting

#### Health-Check Endpoint
```php
class HealthCheckController
{
    public function checkAction(): ResponseInterface
    {
        $status = [
            'api' => $this->checkApiConnectivity(),
            'cache' => $this->checkCacheStatus(),
            'database' => $this->checkDatabase(),
            'timestamp' => time(),
            'version' => '1.0.0',
        ];
        
        return $this->jsonResponse($status);
    }
    
    private function checkApiConnectivity(): array
    {
        try {
            $start = microtime(true);
            $this->apiClient->getEvents(['limit' => 1]);
            $duration = microtime(true) - $start;
            
            return [
                'status' => 'ok',
                'response_time' => round($duration * 1000, 2) . 'ms',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
```

#### Performance-Monitoring
```php
class PerformanceMonitor
{
    private array $metrics = [];
    
    public function trackApiCall(string $endpoint, float $duration, bool $success): void
    {
        $this->metrics[] = [
            'timestamp' => microtime(true),
            'endpoint' => $endpoint,
            'duration' => $duration,
            'success' => $success,
        ];
        
        // Log slow requests
        if ($duration > 2.0) { // > 2 seconds
            $this->logger->warning('Slow API request', [
                'endpoint' => $endpoint,
                'duration' => $duration,
            ]);
        }
    }
}
```

## Zusammenfassung der Strategie

### Caching
1. **Tag-basiertes Caching** für gezielte Invalidation
2. **Filter-spezifische Cache-Schlüssel** für maximale Wiederverwendung
3. **Konfigurierbare Lebensdauern** je nach Daten-Volatilität
4. **Cache-Fallback** bei API-Fehlern
5. **Cache-Warmup** für häufig genutzte Filter

### Fehlerbehandlung
1. **Mehrstufige Retry-Logik** mit exponentiellem Backoff
2. **Detailliertes Logging** mit kontextuellen Informationen
3. **Graceful Degradation** mit Fallback-Daten
4. **Benutzerfreundliche Fehlermeldungen** im Frontend
5. **Health-Checks** für Monitoring und Alerting

Diese Strategie gewährleistet eine robuste und performante Extension, die auch bei API-Problemen weiterhin funktioniert und sinnvolle Fehlermeldungen liefert.