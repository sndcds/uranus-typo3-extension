<?php
declare(strict_types=1);

namespace OklabFlensburg\UranusEvents\Domain\Model;

use OklabFlensburg\UranusEvents\Domain\Dto\FilterParameters;

class EventResponse
{
    /** @var array<Event> */
    private array $events = [];
    private ?int $lastEventDateId;
    private ?\DateTimeInterface $lastEventStartAt;
    private int $totalCount;
    private int $limit;
    private int $offset;

    public static function fromApiResponse(array $apiData, FilterParameters $filter): self
    {
        $response = new self();
        
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
        $response->totalCount = count($response->events);
        $response->limit = $filter->getLimit();
        $response->offset = $filter->getOffset();
        
        return $response;
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
}