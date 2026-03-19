<?php
declare(strict_types=1);

namespace OklabFlensburg\UranusEvents\Domain\Dto;

class FilterParameters
{
    private ?\DateTimeInterface $start = null;
    private ?\DateTimeInterface $end = null;
    private ?string $search = null;
    /** @var array<int>|null */
    private ?array $categories = null;
    /** @var array<int>|null */
    private ?array $organizations = null;
    /** @var array<int>|null */
    private ?array $venues = null;
    private ?string $city = null;
    /** @var array<string>|null */
    private ?array $countries = null;
    private ?string $language = null;
    private int $limit = 20;
    private int $offset = 0;
    private ?int $lastEventDateId = null;
    private ?\DateTimeInterface $lastEventStartAt = null;
    private bool $past = false;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fromArray($data);
        }
    }

    public function fromArray(array $data): void
    {
        if (isset($data['start']) && $data['start']) {
            $this->start = new \DateTimeImmutable($data['start']);
        }
        
        if (isset($data['end']) && $data['end']) {
            $this->end = new \DateTimeImmutable($data['end']);
        }
        
        if (isset($data['search'])) {
            $this->search = (string)$data['search'];
        }
        
        if (isset($data['categories'])) {
            $this->categories = $this->parseIntArray($data['categories']);
        }
        
        if (isset($data['organizations'])) {
            $this->organizations = $this->parseIntArray($data['organizations']);
        }
        
        if (isset($data['venues'])) {
            $this->venues = $this->parseIntArray($data['venues']);
        }
        
        if (isset($data['city'])) {
            $this->city = (string)$data['city'];
        }
        
        if (isset($data['countries'])) {
            $this->countries = $this->parseStringArray($data['countries']);
        }
        
        if (isset($data['language'])) {
            $this->language = (string)$data['language'];
        }
        
        if (isset($data['limit'])) {
            $this->limit = (int)$data['limit'];
        }
        
        if (isset($data['offset'])) {
            $this->offset = (int)$data['offset'];
        }
        
        if (isset($data['last_event_date_id'])) {
            $this->lastEventDateId = (int)$data['last_event_date_id'];
        }
        
        if (isset($data['last_event_start_at']) && $data['last_event_start_at']) {
            $this->lastEventStartAt = new \DateTimeImmutable($data['last_event_start_at']);
        }
        
        if (isset($data['past'])) {
            $this->past = (bool)$data['past'];
        }
    }

    public function toQueryArray(): array
    {
        $query = [];
        
        if ($this->start !== null) {
            $query['start'] = $this->start->format('Y-m-d');
        }
        
        if ($this->end !== null) {
            $query['end'] = $this->end->format('Y-m-d');
        }
        
        if ($this->search !== null) {
            $query['search'] = $this->search;
        }
        
        if ($this->categories !== null) {
            $query['categories'] = implode(',', $this->categories);
        }
        
        if ($this->organizations !== null) {
            $query['organizations'] = implode(',', $this->organizations);
        }
        
        if ($this->venues !== null) {
            $query['venues'] = implode(',', $this->venues);
        }
        
        if ($this->city !== null) {
            $query['city'] = $this->city;
        }
        
        if ($this->countries !== null) {
            $query['countries'] = implode(',', $this->countries);
        }
        
        if ($this->language !== null) {
            $query['language'] = $this->language;
        }
        
        if ($this->limit !== 20) {
            $query['limit'] = $this->limit;
        }
        
        if ($this->offset !== 0) {
            $query['offset'] = $this->offset;
        }
        
        if ($this->lastEventDateId !== null) {
            $query['last_event_date_id'] = $this->lastEventDateId;
        }
        
        if ($this->lastEventStartAt !== null) {
            $query['last_event_start_at'] = $this->lastEventStartAt->format('Y-m-d\TH:i');
        }
        
        if ($this->past) {
            $query['past'] = 'true';
        }
        
        return $query;
    }

    /** @return array<int> */
    private function parseIntArray($value): array
    {
        if (is_array($value)) {
            return array_map('intval', $value);
        }
        
        if (is_string($value)) {
            return array_map('intval', explode(',', $value));
        }
        
        return [];
    }

    /** @return array<string> */
    private function parseStringArray($value): array
    {
        if (is_array($value)) {
            return array_map('strval', $value);
        }
        
        if (is_string($value)) {
            return array_map('trim', explode(',', $value));
        }
        
        return [];
    }

    // Getter methods
    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    /** @return array<int>|null */
    public function getCategories(): ?array
    {
        return $this->categories;
    }

    /** @return array<int>|null */
    public function getOrganizations(): ?array
    {
        return $this->organizations;
    }

    /** @return array<int>|null */
    public function getVenues(): ?array
    {
        return $this->venues;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    /** @return array<string>|null */
    public function getCountries(): ?array
    {
        return $this->countries;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLastEventDateId(): ?int
    {
        return $this->lastEventDateId;
    }

    public function getLastEventStartAt(): ?\DateTimeInterface
    {
        return $this->lastEventStartAt;
    }

    public function isPast(): bool
    {
        return $this->past;
    }

    // Setter methods
    public function setStart(?\DateTimeInterface $start): void
    {
        $this->start = $start;
    }

    public function setEnd(?\DateTimeInterface $end): void
    {
        $this->end = $end;
    }

    public function setSearch(?string $search): void
    {
        $this->search = $search;
    }

    /** @param array<int>|null $categories */
    public function setCategories(?array $categories): void
    {
        $this->categories = $categories;
    }

    /** @param array<int>|null $organizations */
    public function setOrganizations(?array $organizations): void
    {
        $this->organizations = $organizations;
    }

    /** @param array<int>|null $venues */
    public function setVenues(?array $venues): void
    {
        $this->venues = $venues;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    /** @param array<string>|null $countries */
    public function setCountries(?array $countries): void
    {
        $this->countries = $countries;
    }

    public function setLanguage(?string $language): void
    {
        $this->language = $language;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = max(1, $limit);
    }

    public function setOffset(int $offset): void
    {
        $this->offset = max(0, $offset);
    }

    public function setLastEventDateId(?int $lastEventDateId): void
    {
        $this->lastEventDateId = $lastEventDateId;
    }

    public function setLastEventStartAt(?\DateTimeInterface $lastEventStartAt): void
    {
        $this->lastEventStartAt = $lastEventStartAt;
    }

    public function setPast(bool $past): void
    {
        $this->past = $past;
    }
}