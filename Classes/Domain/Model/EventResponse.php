<?php
declare(strict_types=1);

namespace OklabFlensburg\UranusEvents\Domain\Model;

use OklabFlensburg\UranusEvents\Domain\Dto\FilterParameters;

class EventResponse
{
    /** @var array<Event> */
    private array $events = [];
    private ?int $lastEventDateId = null;
    private ?\DateTimeInterface $lastEventStartAt = null;
    private int $totalCount = 0;
    private int $limit = 0;
    private int $offset = 0;
    private bool $hasKnownTotal = false;

    /**
     * @param array<Event> $events
     */
    public function __construct(array $events = [], int $totalCount = 0, int $limit = 0, int $offset = 0, bool $hasKnownTotal = false)
    {
        $this->events = $events;
        $this->totalCount = $totalCount;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->hasKnownTotal = $hasKnownTotal;
    }

    public static function fromApiResponse(array $apiData, FilterParameters $filter): self
    {
        $response = new self(limit: $filter->getLimit(), offset: $filter->getOffset());
        
        // Map events
        if (isset($apiData['events']) && is_array($apiData['events'])) {
            foreach ($apiData['events'] as $eventData) {
                $response->events[] = Event::fromApiArray($eventData);
            }
        }
        
        // Pagination data
        $response->lastEventDateId = isset($apiData['last_event_date_id']) ? (int)$apiData['last_event_date_id'] : null;
        
        if (isset($apiData['last_event_start_at']) && $apiData['last_event_start_at']) {
            $response->lastEventStartAt = new \DateTimeImmutable($apiData['last_event_start_at']);
        } else {
            $response->lastEventStartAt = null;
        }
        
        // Counts
        $response->totalCount = self::extractTotalCount($apiData) ?? count($response->events);
        $response->hasKnownTotal = self::extractTotalCount($apiData) !== null;
        
        return $response;
    }

    public function __unserialize(array $data): void
    {
        $this->events = is_array($data['events'] ?? null) ? $data['events'] : [];
        $this->lastEventDateId = isset($data['lastEventDateId']) ? (int)$data['lastEventDateId'] : null;
        $this->lastEventStartAt = ($data['lastEventStartAt'] ?? null) instanceof \DateTimeInterface ? $data['lastEventStartAt'] : null;
        $this->totalCount = isset($data['totalCount']) ? (int)$data['totalCount'] : count($this->events);
        $this->limit = isset($data['limit']) ? (int)$data['limit'] : 0;
        $this->offset = isset($data['offset']) ? (int)$data['offset'] : 0;
        $this->hasKnownTotal = isset($data['hasKnownTotal']) ? (bool)$data['hasKnownTotal'] : isset($data['totalCount']);
    }

    /** @return array<Event> */
    public function getEvents(): array
    {
        return $this->events;
    }

    public function getLastEventDateId(): ?int
    {
        return $this->lastEventDateId;
    }

    public function getLastEventStartAt(): ?\DateTimeInterface
    {
        return $this->lastEventStartAt;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function hasKnownTotal(): bool
    {
        return $this->hasKnownTotal;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function hasMore(): bool
    {
        return $this->lastEventDateId !== null || $this->lastEventStartAt !== null;
    }

    public function getCurrentPage(): int
    {
        if ($this->limit === 0) {
            return 1;
        }
        
        return (int)floor($this->offset / $this->limit) + 1;
    }

    public function getTotalPages(): int
    {
        if ($this->limit === 0) {
            return 1;
        }
        
        return (int)ceil($this->totalCount / $this->limit);
    }

    private static function extractTotalCount(array $apiData): ?int
    {
        $candidates = [
            $apiData['total_count'] ?? null,
            $apiData['totalCount'] ?? null,
            $apiData['total'] ?? null,
            $apiData['count'] ?? null,
            $apiData['meta']['total'] ?? null,
            $apiData['pagination']['total'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_numeric($candidate)) {
                return (int)$candidate;
            }
        }

        return null;
    }
}
