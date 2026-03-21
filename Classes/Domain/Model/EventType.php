<?php
declare(strict_types=1);

namespace OklabFlensburg\UranusEvents\Domain\Model;

class EventType
{
    private int $typeId;
    private ?string $typeName;
    private ?int $genreId;
    private ?string $genreName = null;

    public static function fromApiArray(array $data): self
    {
        $eventType = new self();
        $eventType->typeId = (int)($data['type_id'] ?? 0);
        $eventType->typeName = isset($data['type_name']) && $data['type_name'] !== '' ? (string)$data['type_name'] : null;
        $eventType->genreId = isset($data['genre_id']) ? (int)$data['genre_id'] : null;

        return $eventType;
    }

    public function getTypeId(): int
    {
        return $this->typeId;
    }

    public function getTypeName(): ?string
    {
        return $this->typeName;
    }

    public function setTypeName(?string $name): void
    {
        $this->typeName = $name;
    }

    public function getGenreId(): ?int
    {
        return $this->genreId;
    }

    public function setGenreName(?string $name): void
    {
        $this->genreName = $name;
    }

    public function getGenreName(): ?string
    {
        return $this->genreName;
    }
}
