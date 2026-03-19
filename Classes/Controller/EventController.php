<?php
declare(strict_types=1);

namespace OklabFlensburg\UranusEvents\Controller;

use OklabFlensburg\UranusEvents\Domain\Dto\FilterParameters;
use OklabFlensburg\UranusEvents\Service\EventService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
        $queryParams = $this->request->getQueryParams();
        $globalGet = GeneralUtility::_GET();

        $eventId = (int)($queryParams['uranus-event-id']
            ?? $queryParams['uranus_event_id']
            ?? $globalGet['uranus-event-id']
            ?? $globalGet['uranus_event_id']
            ?? 0);

        $dateId = (int)($queryParams['uranus-event-date-id']
            ?? $queryParams['uranus_event_date_id']
            ?? $globalGet['uranus-event-date-id']
            ?? $globalGet['uranus_event_date_id']
            ?? 0);

        // Allow direct detail URLs like ?uranus-event-id=12&uranus-event-date-id=213
        if ($eventId > 0 && $dateId > 0) {
            return $this->detailAction($eventId, $dateId);
        }

        try {
            $filter = $this->createFilterFromSettings();
            $eventResponse = $this->eventService->getEventsWithFallback($filter);

            foreach ($eventResponse->getEvents() as $event) {
                $this->eventService->enrichEventWithLookup($event);
            }

            $this->view->assignMultiple([
                'events' => $eventResponse->getEvents(),
                'pagination' => [
                    'total' => $eventResponse->getTotalCount(),
                    'limit' => $eventResponse->getLimit(),
                    'offset' => $eventResponse->getOffset(),
                    'lastEventDateId' => $eventResponse->getLastEventDateId(),
                    'lastEventStartAt' => $eventResponse->getLastEventStartAt(),
                    'currentPage' => $eventResponse->getCurrentPage(),
                    'totalPages' => $eventResponse->getTotalPages(),
                    'hasMore' => $eventResponse->hasMore(),
                ],
                'filter' => $filter,
                'hasError' => false,
                'errorMessage' => null,
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to load events in EventController', [
                'error' => $e->getMessage(),
                'settings' => $this->settings,
                'trace' => $e->getTraceAsString(),
            ]);
            
            $this->view->assignMultiple([
                'events' => [],
                'pagination' => [
                    'total' => 0,
                    'limit' => 20,
                    'offset' => 0,
                    'currentPage' => 1,
                    'totalPages' => 1,
                    'hasMore' => false,
                ],
                'filter' => null,
                'hasError' => true,
                'errorMessage' => 'Events konnten nicht geladen werden. Bitte versuchen Sie es später erneut.',
                'errorDetails' => $this->shouldShowErrorDetails() ? $e->getMessage() : null,
            ]);
        }
        
        return $this->htmlResponse();
    }
    
    public function loadMoreAction(int $offset = 0, ?int $lastEventDateId = null, ?string $lastEventStartAt = null): ResponseInterface
    {
        try {
            $filter = $this->createFilterFromSettings();
            $filter->setOffset($offset);
            
            if ($lastEventDateId) {
                $filter->setLastEventDateId($lastEventDateId);
            }
            
            if ($lastEventStartAt) {
                $filter->setLastEventStartAt(new \DateTimeImmutable($lastEventStartAt));
            }
            
            $eventResponse = $this->eventService->getEventsWithFallback($filter);

            foreach ($eventResponse->getEvents() as $event) {
                $this->eventService->enrichEventWithLookup($event);
            }

            $this->view->assignMultiple([
                'events' => $eventResponse->getEvents(),
                'pagination' => [
                    'total' => $eventResponse->getTotalCount(),
                    'limit' => $eventResponse->getLimit(),
                    'offset' => $eventResponse->getOffset(),
                    'lastEventDateId' => $eventResponse->getLastEventDateId(),
                    'lastEventStartAt' => $eventResponse->getLastEventStartAt(),
                    'hasMore' => $eventResponse->hasMore(),
                ],
                'filter' => $filter,
                'hasError' => false,
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to load more events', [
                'error' => $e->getMessage(),
                'offset' => $offset,
                'lastEventDateId' => $lastEventDateId,
                'lastEventStartAt' => $lastEventStartAt,
            ]);
            
            $this->view->assignMultiple([
                'events' => [],
                'hasError' => true,
                'errorMessage' => 'Weitere Events konnten nicht geladen werden.',
            ]);
        }
        
        // Return partial for AJAX requests
        if ($this->request->getQueryParams()['ajax'] ?? false) {
            $this->view->setTemplate('ListPartial');
        }
        
        return $this->htmlResponse();
    }
    
    public function detailAction(int $eventId, int $dateId): ResponseInterface
    {
        try {
            // Fetch event detail from API using the specific event and date ID
            $eventData = $this->eventService->getEventDetail($eventId, $dateId);
            
            if ($eventData === null) {
                throw new \RuntimeException('Event not found');
            }
            
            // Create Event model from API data
            $event = \OklabFlensburg\UranusEvents\Domain\Model\Event::fromApiArray($eventData);
            $this->eventService->enrichEventWithLookup($event);

            $this->view->assignMultiple([
                'event' => $event,
                'hasError' => false,
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to load event details', [
                'eventId' => $eventId,
                'error' => $e->getMessage(),
            ]);
            
            $this->view->assignMultiple([
                'event' => null,
                'hasError' => true,
                'errorMessage' => 'Event-Details konnten nicht geladen werden.',
            ]);
        }
        
        return $this->htmlResponse();
    }
    
    private function createFilterFromSettings(): FilterParameters
    {
        $filterData = [];
        
        // Map plugin settings to filter parameters
        if (!empty($this->settings['start'])) {
            $filterData['start'] = $this->settings['start'];
        }
        
        if (!empty($this->settings['end'])) {
            $filterData['end'] = $this->settings['end'];
        }
        
        if (!empty($this->settings['search'])) {
            $filterData['search'] = $this->settings['search'];
        }
        
        // Handle array parameters
        if (!empty($this->settings['categories'])) {
            $filterData['categories'] = $this->settings['categories'];
        }
        
        if (!empty($this->settings['organizations'])) {
            $filterData['organizations'] = $this->settings['organizations'];
        }
        
        if (!empty($this->settings['venues'])) {
            $filterData['venues'] = $this->settings['venues'];
        }
        
        if (!empty($this->settings['city'])) {
            $filterData['city'] = $this->settings['city'];
        }
        
        if (!empty($this->settings['countries'])) {
            $filterData['countries'] = $this->settings['countries'];
        }
        
        if (!empty($this->settings['language'])) {
            $filterData['language'] = $this->settings['language'];
        }
        
        // Set limit from settings or default
        $filterData['limit'] = (int)($this->settings['limit'] ?? 20);
        
        // Handle pagination from request
        $queryParams = $this->request->getQueryParams();
        $filterData['offset'] = (int)($queryParams['offset'] ?? 0);
        
        // Handle Uranus-specific pagination
        if (!empty($queryParams['last_event_date_id'])) {
            $filterData['last_event_date_id'] = (int)$queryParams['last_event_date_id'];
        }
        
        if (!empty($queryParams['last_event_start_at'])) {
            $filterData['last_event_start_at'] = $queryParams['last_event_start_at'];
        }
        
        // Past events
        if (isset($this->settings['past']) && $this->settings['past']) {
            $filterData['past'] = true;
        }
        
        return new FilterParameters($filterData);
    }
    
    private function shouldShowErrorDetails(): bool
    {
        // Show error details only in development context
        return ($GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'] ?? '') === '*'
            || ($this->settings['debug'] ?? false)
            || ($this->request->getQueryParams()['debug'] ?? false);
    }
    
}