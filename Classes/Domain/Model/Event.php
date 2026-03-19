<?php
declare(strict_types=1);

namespace OklabFlensburg\UranusEvents\Domain\Model;

class Event
{
    private int $eventDateId;
    private int $id;
    private string $title;
    private ?string $subtitle;
    private ?string $description;
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
    private ?string $imageCreator;
    private ?string $imageCopyright;
    private ?string $imageLicense;
    private ?string $imageLicenseName;
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
    private ?string $priceType;
    /** @var array<array<string,mixed>> */
    private array $furtherDates = [];

    public static function fromApiArray(array $data): self
    {
        $event = new self();
        
        $event->id = (int)($data['id'] ?? 0);
        $event->title = (string)($data['title'] ?? '');
        $event->subtitle = isset($data['subtitle']) && $data['subtitle'] !== '' ? (string)$data['subtitle'] : null;
        $event->description = isset($data['description']) && $data['description'] !== '' ? (string)$data['description'] : null;
        $event->releaseStatus = (string)($data['release_status'] ?? 'released');

        // The detail API nests date/venue info under 'date'; the list API uses flat fields.
        $dateData = isset($data['date']) && is_array($data['date']) ? $data['date'] : $data;

        $event->eventDateId = (int)($dateData['id'] ?? $data['event_date_id'] ?? 0);

        // Date & time
        $startDateStr = $dateData['start_date'] ?? null;
        $event->startDate = $startDateStr ? new \DateTimeImmutable($startDateStr) : new \DateTimeImmutable();
        $event->startTime = isset($dateData['start_time']) && $dateData['start_time'] !== '' ? (string)$dateData['start_time'] : null;
        $event->entryTime = isset($dateData['entry_time']) && $dateData['entry_time'] !== '' ? (string)$dateData['entry_time'] : null;

        // Venue data
        $event->venueId = (int)($dateData['venue_id'] ?? 0);
        $event->venueName = (string)($dateData['venue_name'] ?? '');
        $event->venueCity = (string)($dateData['venue_city'] ?? '');
        $event->venueStreet = isset($dateData['venue_street']) && $dateData['venue_street'] !== '' ? (string)$dateData['venue_street'] : null;
        $event->venueHouseNumber = isset($dateData['venue_house_number']) && $dateData['venue_house_number'] !== '' ? (string)$dateData['venue_house_number'] : null;
        $event->venuePostalCode = isset($dateData['venue_postal_code']) && $dateData['venue_postal_code'] !== '' ? (string)$dateData['venue_postal_code'] : null;
        $event->venueState = isset($dateData['venue_state']) && $dateData['venue_state'] !== '' ? (string)$dateData['venue_state'] : null;
        $event->venueCountry = (string)($dateData['venue_country'] ?? '');
        $event->venueLat = isset($dateData['venue_lat']) ? (float)$dateData['venue_lat'] : null;
        $event->venueLon = isset($dateData['venue_lon']) ? (float)$dateData['venue_lon'] : null;

        // Image data — detail API uses nested 'image' object; list API uses flat fields
        if (isset($data['image']) && is_array($data['image'])) {
            $img = $data['image'];
            $event->imageId = isset($img['id']) ? (int)$img['id'] : null;
            $event->imagePath = isset($img['url']) && $img['url'] !== '' ? (string)$img['url'] : null;
            $event->imageCreator = isset($img['creator']) && $img['creator'] !== '' ? (string)$img['creator'] : null;
            $event->imageCopyright = isset($img['copyright']) && $img['copyright'] !== '' ? (string)$img['copyright'] : null;
            $event->imageLicense = isset($img['license']) && $img['license'] !== '' ? (string)$img['license'] : null;
            $event->imageLicenseName = isset($img['license_name']) && $img['license_name'] !== '' ? (string)$img['license_name'] : null;
        } else {
            $event->imageId = isset($data['image_id']) ? (int)$data['image_id'] : null;
            $event->imagePath = isset($data['image_path']) && $data['image_path'] !== '' ? (string)$data['image_path'] : null;
            $event->imageCreator = null;
            $event->imageCopyright = null;
            $event->imageLicense = null;
            $event->imageLicenseName = null;
        }

        // Organization
        $event->organizationId = (int)($data['organization_id'] ?? 0);
        $event->organizationName = (string)($data['organization_name'] ?? '');

        // Event types
        if (isset($data['event_types']) && is_array($data['event_types'])) {
            foreach ($data['event_types'] as $eventTypeData) {
                $event->eventTypes[] = EventType::fromApiArray($eventTypeData);
            }
        }

        // Languages and tags
        $event->languages = isset($data['languages']) && is_array($data['languages']) ? $data['languages'] : null;
        $event->tags = isset($data['tags']) && is_array($data['tags']) ? $data['tags'] : null;

        // Age restrictions
        $event->minAge = isset($data['min_age']) ? (int)$data['min_age'] : null;
        $event->maxAge = isset($data['max_age']) ? (int)$data['max_age'] : null;

        // Price type
        $event->priceType = isset($data['price_type']) && $data['price_type'] !== '' && $data['price_type'] !== 'not_specified' ? (string)$data['price_type'] : null;

        // Further dates (only present in detail response)
        $event->furtherDates = isset($data['further_dates']) && is_array($data['further_dates']) ? $data['further_dates'] : [];

        return $event;
    }

    // Getter methods
    public function getEventDateId(): int
    {
        return $this->eventDateId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function getStartTime(): ?string
    {
        return $this->startTime;
    }

    public function getEntryTime(): ?string
    {
        return $this->entryTime;
    }

    public function getVenueId(): int
    {
        return $this->venueId;
    }

    public function getVenueName(): string
    {
        return $this->venueName;
    }

    public function getVenueCity(): string
    {
        return $this->venueCity;
    }

    public function getVenueStreet(): ?string
    {
        return $this->venueStreet;
    }

    public function getVenueHouseNumber(): ?string
    {
        return $this->venueHouseNumber;
    }

    public function getVenuePostalCode(): ?string
    {
        return $this->venuePostalCode;
    }

    public function getVenueState(): ?string
    {
        return $this->venueState;
    }

    public function getVenueCountry(): string
    {
        return $this->venueCountry;
    }

    public function getVenueLat(): ?float
    {
        return $this->venueLat;
    }

    public function getVenueLon(): ?float
    {
        return $this->venueLon;
    }

    public function getImageId(): ?int
    {
        return $this->imageId;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function getImageCreator(): ?string
    {
        return $this->imageCreator;
    }

    public function getImageCopyright(): ?string
    {
        return $this->imageCopyright;
    }

    public function getImageLicense(): ?string
    {
        return $this->imageLicense;
    }

    public function getImageLicenseName(): ?string
    {
        return $this->imageLicenseName;
    }

    public function getOrganizationId(): int
    {
        return $this->organizationId;
    }

    public function getOrganizationName(): string
    {
        return $this->organizationName;
    }

    /** @return array<EventType> */
    public function getEventTypes(): array
    {
        return $this->eventTypes;
    }

    /** @return array<string>|null */
    public function getLanguages(): ?array
    {
        return $this->languages;
    }

    /** @return array<string>|null */
    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function getMinAge(): ?int
    {
        return $this->minAge;
    }

    public function getMaxAge(): ?int
    {
        return $this->maxAge;
    }

    public function getReleaseStatus(): string
    {
        return $this->releaseStatus;
    }

    public function getPriceType(): ?string
    {
        return $this->priceType;
    }

    /** @return array<array<string,mixed>> */
    public function getFurtherDates(): array
    {
        return $this->furtherDates;
    }

    public function hasImage(): bool
    {
        return $this->imagePath !== null && $this->imagePath !== '';
    }

    public function getFormattedAddress(): string
    {
        $parts = [];
        
        if ($this->venueStreet) {
            $parts[] = $this->venueStreet;
            if ($this->venueHouseNumber) {
                $parts[count($parts) - 1] .= ' ' . $this->venueHouseNumber;
            }
        }
        
        if ($this->venuePostalCode) {
            $parts[] = $this->venuePostalCode;
        }
        
        if ($this->venueCity) {
            $parts[] = $this->venueCity;
        }
        
        return implode(', ', $parts);
    }

    public function getCountryName(): string
    {
        $countryCodes = [
            'DEU' => 'Deutschland',
            'DNK' => 'Dänemark',
            'AUT' => 'Österreich',
            'CHE' => 'Schweiz',
            'NLD' => 'Niederlande',
            'BEL' => 'Belgien',
            'LUX' => 'Luxemburg',
            'FRA' => 'Frankreich',
            'POL' => 'Polen',
            'CZE' => 'Tschechien',
        ];
        
        return $countryCodes[$this->venueCountry] ?? $this->venueCountry;
    }
}