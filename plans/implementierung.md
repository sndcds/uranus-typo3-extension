# Detaillierte Implementierungsplanung

## 1. Domain Models / DTOs

### Event.php
```php
<?php
declare(strict_types=1);

namespace OklabFlensburg\UranusEvents\Domain\Model;

class Event
{
    private int $eventDateId;
    private int $id;
    private string $title;
    private ?string $subtitle;
    private \DateTimeInterface $startDate;
    private ?string $startTime;
    private ?string $entryTime;
    private int $venueId;
    private string $venueName;
    private string $venueCity;
    private ?string $venueStreet;
    private ?string $venueHouseNumber;
    private ?string $venuePostalCode;
    private ?string $venueState;
    private string $venueCountry;
    private ?float $venueLat;
    private ?float $venueLon;
    private ?int $imageId;
    private ?string $imagePath;
    private int $organizationId;
    private string $organizationName;
    /** @var array<EventType> */
    private array $eventTypes = [];
    /** @var array<string>|null */
    private ?array $languages;
    /** @var array<string>|null */
    private ?array $tags;
    private ?int $minAge;
    private ?int $maxAge;
    private string $releaseStatus;
    
    // Getter/Setter für alle Properties
    // Constructor mit Array-Input für API-Daten
    // fromApiArray() static method
}
```

### EventResponse.php
```php
<?php
declare(strict_types=1);

namespace OklabFlensburg\UranusEvents\Domain\Model;

class EventResponse
{
    /** @var array<Event> */
    private array $events = [];
    private ?int $lastEventDateId;
    private ?\DateTimeInterface $lastEventStartAt;
    private int $totalCount;
    private int $limit;
    private int $offset;
    
    // Getter/Setter
    // fromApiResponse() static method
}
```

### FilterParameters.php
```php
<?php
declare(strict_types=1);

namespace OklabFlensburg\UranusEvents\Domain\Dto;

class FilterParameters
{
    private ?\DateTimeInterface $start;
    private ?\DateTimeInterface $end;
    private ?string $search;
    /** @var array<int>|null */
    private ?array $categories;
    /** @var array<int>|null */
    private ?array $organizations;
    /** @var array<int>|null */
    private ?array $venues;
    private ?string $city;
    /** @var array<string>|null */
    private ?array $countries;
    private ?string $language;
    private int $limit = 20;
    private int $offset = 0;
    private ?int $lastEventDateId;
    private ?\DateTimeInterface $lastEventStartAt;
    private bool $past = false;
    
    // Getter/Setter mit Validierung
    // toQueryArray() für API-Parameter
    // fromPluginSettings() aus TYPO3-Plugin
}
```

## 2. ApiClientService

### Verantwortlichkeiten:
- HTTP-Kommunikation mit Uranus-API
- Query-Parameter-Bildung
- Error Handling und Retry-Logik
- JSON-Parsing und Validierung

### Implementierungsdetails:
```php
<?php
declare(strict_types=1);

namespace OklabFlensburg\UranusEvents\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ApiClientService
{
    private Client $client;
    private string $baseUrl;
    private string $endpoint;
    private int $timeout;
    private int $maxRetries;
    private Logger $logger;
    
    public function __construct(
        ExtensionConfiguration $extensionConfiguration,
        Logger $logger
    ) {
        $config = $extensionConfiguration->get('uranus_events');
        $this->baseUrl = rtrim($config['apiBaseUrl'] ?? 'https://api.example.com', '/');
        $this->endpoint = $config['apiEndpoint'] ?? '/api/events';
        $this->timeout = (int)($config['httpTimeout'] ?? 30);
        $this->maxRetries = (int)($config['maxRetries'] ?? 3);
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'TYPO3-Uranus-Events/1.0',
            ],
        ]);
        
        $this->logger = $logger;
    }
    
    public function getEvents(array $queryParams = []): array
    {
        $retryCount = 0;
        
        while ($retryCount <= $this->maxRetries) {
            try {
                $response = $this->client->get($this->endpoint, [
                    'query' => $queryParams,
                ]);
                
                return $this->parseResponse($response);
                
            } catch (RequestException $e) {
                $retryCount++;
                $this->logger->error(
                    sprintf('API request failed (attempt %d/%d): %s', 
                        $retryCount, $this->maxRetries, $e->getMessage())
                );
                
                if ($retryCount > $this->maxRetries) {
                    throw new \RuntimeException(
                        'Failed to fetch events from Uranus API after ' . $this->maxRetries . ' attempts',
                        0,
                        $e
                    );
                }
                
                // Exponential backoff
                usleep(100000 * (2 ** $retryCount));
            }
        }
        
        return [];
    }
    
    private function parseResponse(ResponseInterface $response): array
    {
        $body = (string)$response->getBody();
        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        
        if (!is_array($data) || !isset($data['events'])) {
            throw new \RuntimeException('Invalid API response format');
        }
        
        return $data;
    }
}
```

## 3. EventService

### Verantwortlichkeiten:
- Orchestrierung von API-Aufrufen und Caching
- Daten-Mapping zu Domain-Modellen
- Pagination-Logik
- Filter-Validierung

### Implementierungsdetails:
```php
<?php
declare(strict_types=1);

namespace OklabFlensburg\UranusEvents\Service;

use OklabFlensburg\UranusEvents\Domain\Dto\FilterParameters;
use OklabFlensburg\UranusEvents\Domain\Model\Event;
use OklabFlensburg\UranusEvents\Domain\Model\EventResponse;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

class EventService
{
    private ApiClientService $apiClient;
    private FrontendInterface $cache;
    private int $cacheLifetime;
    
    public function __construct(
        ApiClientService $apiClient,
        CacheManager $cacheManager,
        ExtensionConfiguration $extensionConfiguration
    ) {
        $this->apiClient = $apiClient;
        $this->cache = $cacheManager->getCache('uranus_events');
        
        $config = $extensionConfiguration->get('uranus_events');
        $this->cacheLifetime = (int)($config['cacheLifetime'] ?? 3600);
    }
    
    public function getEvents(FilterParameters $filter): EventResponse
    {
        $cacheKey = $this->generateCacheKey($filter);
        
        // Cache lookup
        $cachedData = $this->cache->get($cacheKey);
        if ($cachedData !== false) {
            return $cachedData;
        }
        
        // API call
        $queryParams = $filter->toQueryArray();
        $apiData = $this->apiClient->getEvents($queryParams);
        
        // Map to domain models
        $eventResponse = EventResponse::fromApiResponse($apiData, $filter);
        
        // Store in cache
        $this->cache->set(
            $cacheKey,
            $eventResponse,
            ['uranus_events'],
            $this->cacheLifetime
        );
        
        return $eventResponse;
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
            'past' => $filter->isPast(),
        ];
        
        return 'events_' . md5(serialize($keyData));
    }
}
```

## 4. EventController

### Verantwortlichkeiten:
- Verarbeitung von Frontend-Requests
- Plugin-Einstellungen auslesen
- FilterParameters aus Plugin-Daten erstellen
- Daten an View übergeben

### Implementierungsdetails:
```php
<?php
declare(strict_types=1);

namespace OklabFlensburg\UranusEvents\Controller;

use OklabFlensburg\UranusEvents\Domain\Dto\FilterParameters;
use OklabFlensburg\UranusEvents\Service\EventService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class EventController extends ActionController
{
    private EventService $eventService;
    
    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }
    
    public function listAction(): ResponseInterface
    {
        try {
            $filter = $this->createFilterFromSettings();
            $eventResponse = $this->eventService->getEvents($filter);
            
            $this->view->assignMultiple([
                'events' => $eventResponse->getEvents(),
                'pagination' => [
                    'total' => $eventResponse->getTotalCount(),
                    'limit' => $eventResponse->getLimit(),
                    'offset' => $eventResponse->getOffset(),
                    'lastEventDateId' => $eventResponse->getLastEventDateId(),
                    'lastEventStartAt' => $eventResponse->getLastEventStartAt(),
                ],
                'filter' => $filter,
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to load events: ' . $e->getMessage());
            $this->view->assign('error', true);
            $this->view->assign('errorMessage', 'Events konnten nicht geladen werden.');
        }
        
        return $this->htmlResponse();
    }
    
    private function createFilterFromSettings(): FilterParameters
    {
        $filter = new FilterParameters();
        
        // Map plugin settings to filter parameters
        if (!empty($this->settings['start'])) {
            $filter->setStart(new \DateTime($this->settings['start']));
        }
        
        if (!empty($this->settings['end'])) {
            $filter->setEnd(new \DateTime($this->settings['end']));
        }
        
        if (!empty($this->settings['search'])) {
            $filter->setSearch($this->settings['search']);
        }
        
        // Handle array parameters (categories, organizations, etc.)
        if (!empty($this->settings['categories'])) {
            $filter->setCategories(
                GeneralUtility::intExplode(',', $this->settings['categories'], true)
            );
        }
        
        // Set limit from settings or default
        $filter->setLimit((int)($this->settings['limit'] ?? 20));
        
        // Handle pagination from request
        $offset = (int)($this->request->getQueryParams()['offset'] ?? 0);
        $filter->setOffset($offset);
        
        return $filter;
    }
}
```

## 5. TCA Configuration für Plugin

### `Configuration/TCA/Overrides/tt_content.php`:
```php
<?php
defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'UranusEvents',
    'Events',
    'LLL:EXT:uranus_events/Resources/Private/Language/locallang_db.xlf:plugin.events.title',
    'EXT:uranus_events/Resources/Public/Icons/Extension.svg'
);

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

// Add FlexForm configuration
$GLOBALS['TCA']['tt_content']['types']['uranusevents_events']['columnsOverrides']['pi_flexform'] = [
    'config' => [
        'ds' => [
            '*,uranusevents_events' => 'FILE:EXT:uranus_events/Configuration/FlexForms/Events.xml',
        ],
    ],
];
```

## 6. FlexForm für Plugin-Einstellungen

### `Configuration/FlexForms/Events.xml`:
```xml
<T3DataStructure>
    <meta>
        <langDisable>1</langDisable>
    </meta>
    <ROOT>
        <type>array</type>
        <el>
            <settings.filter>
                <TCEforms>
                    <label>Filter-Einstellungen</label>
                    <config>
                        <type>array</type>
                        <section>1</section>
                        <el>
                            <start>
                                <TCEforms>
                                    <label>Startdatum (YYYY-MM-DD)</label>
                                    <config>
                                        <type>input</type>
                                        <size>20</size>
                                        <eval>date</eval>
                                    </config>
                                </TCEforms>
                            </start>
                            <end>
                                <TCEforms>
                                    <label>Enddatum (YYYY-MM-DD)</label>
                                    <config>
                                        <type>input</type>
                                        <size>20</size>
                                        <eval>date</eval>
                                    </config>
                                </TCEforms>
                            </end>
                            <search>
                                <TCEforms>
                                    <label>Suchbegriff</label>
                                    <config>
                                        <type>input</type>
                                        <size>50</size>
                                    </config>
                                </TCEforms>
                            </search>
                            <categories>
                                <TCEforms>
                                    <label>Kategorien (kommaseparierte IDs)</label>
                                    <config>
                                        <type>input</type>
                                        <size>50</size>
                                    </config>
                                </TCEforms>
                            </categories>
                            <limit>
                                <TCEforms>
                                    <label>Anzahl Events pro Seite</label>
                                    <config>
                                        <type>input</type>
                                        <size>5</size>
                                        <eval>int</eval>
                                        <default>20</default>
                                    </config>
                                </TCEforms>
                            </limit>
                        </el>
                    </config>
                </TCEforms>
            </settings.filter>
        </el>
    </ROOT>
</T3DataStructure>
```

## 7. TypoScript Configuration

### `Configuration/TypoScript/setup.typoscript`:
```typoscript
plugin.tx_uranusevents {
    view {
        templateRootPaths {
            0 = EXT:uranus_events/Resources/Private/Templates/
            1 = {$plugin.tx_uranusevents.view.templateRootPath}
        }
        partialRootPaths {
            0 = EXT:uranus_events/Resources/Private/Templates/Partial/
            1 = {$plugin.tx_uranusevents.view.partialRootPath}
        }
        layoutRootPaths {
            0 = EXT:uranus_events/Resources/Private/Templates/Layouts/
            1 = {$plugin.tx_uranusevents.view.layoutRootPath}
        }
    }
    
    persistence {
        storagePid = {$plugin.tx_uranusevents.persistence.storagePid}
    }
    
    features {
        # skipDefaultArguments = 1
        # if set to