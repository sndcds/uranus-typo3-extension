<?php

declare(strict_types=1);

namespace OklabFlensburg\UranusEvents\Tests\Unit\Domain\Model;

use OklabFlensburg\UranusEvents\Domain\Model\Event;
use OklabFlensburg\UranusEvents\Domain\Model\EventType;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case for Event model
 */
class EventTest extends UnitTestCase
{
    protected Event $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new Event();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->subject);
    }

    /**
     * @test
     */
    public function getTitleReturnsInitialValue(): void
    {
        self::assertSame('', $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function setTitleSetsTitle(): void
    {
        $title = 'Test Event';
        $this->subject->setTitle($title);
        self::assertSame($title, $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function getSubtitleReturnsInitialValue(): void
    {
        self::assertNull($this->subject->getSubtitle());
    }

    /**
     * @test
     */
    public function setSubtitleSetsSubtitle(): void
    {
        $subtitle = 'Test Subtitle';
        $this->subject->setSubtitle($subtitle);
        self::assertSame($subtitle, $this->subject->getSubtitle());
    }

    /**
     * @test
     */
    public function setSubtitleWithNullWorks(): void
    {
        $this->subject->setSubtitle(null);
        self::assertNull($this->subject->getSubtitle());
    }

    /**
     * @test
     */
    public function getStartDateReturnsInitialValue(): void
    {
        self::assertNull($this->subject->getStartDate());
    }

    /**
     * @test
     */
    public function setStartDateSetsStartDate(): void
    {
        $date = new \DateTime('2026-03-28');
        $this->subject->setStartDate($date);
        self::assertSame($date, $this->subject->getStartDate());
    }

    /**
     * @test
     */
    public function getStartTimeReturnsInitialValue(): void
    {
        self::assertNull($this->subject->getStartTime());
    }

    /**
     * @test
     */
    public function setStartTimeSetsStartTime(): void
    {
        $time = '21:00';
        $this->subject->setStartTime($time);
        self::assertSame($time, $this->subject->getStartTime());
    }

    /**
     * @test
     */
    public function getVenueNameReturnsInitialValue(): void
    {
        self::assertSame('', $this->subject->getVenueName());
    }

    /**
     * @test
     */
    public function setVenueNameSetsVenueName(): void
    {
        $venueName = 'Test Venue';
        $this->subject->setVenueName($venueName);
        self::assertSame($venueName, $this->subject->getVenueName());
    }

    /**
     * @test
     */
    public function getVenueCityReturnsInitialValue(): void
    {
        self::assertSame('', $this->subject->getVenueCity());
    }

    /**
     * @test
     */
    public function setVenueCitySetsVenueCity(): void
    {
        $city = 'Test City';
        $this->subject->setVenueCity($city);
        self::assertSame($city, $this->subject->getVenueCity());
    }

    /**
     * @test
     */
    public function getOrganizationNameReturnsInitialValue(): void
    {
        self::assertSame('', $this->subject->getOrganizationName());
    }

    /**
     * @test
     */
    public function setOrganizationNameSetsOrganizationName(): void
    {
        $orgName = 'Test Organization';
        $this->subject->setOrganizationName($orgName);
        self::assertSame($orgName, $this->subject->getOrganizationName());
    }

    /**
     * @test
     */
    public function getImagePathReturnsInitialValue(): void
    {
        self::assertNull($this->subject->getImagePath());
    }

    /**
     * @test
     */
    public function setImagePathSetsImagePath(): void
    {
        $imagePath = 'https://example.com/image.jpg';
        $this->subject->setImagePath($imagePath);
        self::assertSame($imagePath, $this->subject->getImagePath());
    }

    /**
     * @test
     */
    public function getEventTypesReturnsInitialValue(): void
    {
        self::assertEmpty($this->subject->getEventTypes());
    }

    /**
     * @test
     */
    public function setEventTypesSetsEventTypes(): void
    {
        $eventTypes = [
            new EventType(53, 0),
            new EventType(44, 0)
        ];
        $this->subject->setEventTypes($eventTypes);
        self::assertSame($eventTypes, $this->subject->getEventTypes());
    }

    /**
     * @test
     */
    public function getCategoriesReturnsInitialValue(): void
    {
        self::assertEmpty($this->subject->getCategories());
    }

    /**
     * @test
     */
    public function setCategoriesSetsCategories(): void
    {
        $categories = ['Music', 'Concert'];
        $this->subject->setCategories($categories);
        self::assertSame($categories, $this->subject->getCategories());
    }

    /**
     * @test
     */
    public function getLanguagesReturnsInitialValue(): void
    {
        self::assertEmpty($this->subject->getLanguages());
    }

    /**
     * @test
     */
    public function setLanguagesSetsLanguages(): void
    {
        $languages = ['de', 'en'];
        $this->subject->setLanguages($languages);
        self::assertSame($languages, $this->subject->getLanguages());
    }

    /**
     * @test
     */
    public function getTagsReturnsInitialValue(): void
    {
        self::assertEmpty($this->subject->getTags());
    }

    /**
     * @test
     */
    public function setTagsSetsTags(): void
    {
        $tags = ['rock', 'live'];
        $this->subject->setTags($tags);
        self::assertSame($tags, $this->subject->getTags());
    }

    /**
     * @test
     */
    public function getMinAgeReturnsInitialValue(): void
    {
        self::assertNull($this->subject->getMinAge());
    }

    /**
     * @test
     */
    public function setMinAgeSetsMinAge(): void
    {
        $minAge = 18;
        $this->subject->setMinAge($minAge);
        self::assertSame($minAge, $this->subject->getMinAge());
    }

    /**
     * @test
     */
    public function getMaxAgeReturnsInitialValue(): void
    {
        self::assertNull($this->subject->getMaxAge());
    }

    /**
     * @test
     */
    public function setMaxAgeSetsMaxAge(): void
    {
        $maxAge = 99;
        $this->subject->setMaxAge($maxAge);
        self::assertSame($maxAge, $this->subject->getMaxAge());
    }

    /**
     * @test
     */
    public function getReleaseStatusReturnsInitialValue(): void
    {
        self::assertSame('', $this->subject->getReleaseStatus());
    }

    /**
     * @test
     */
    public function setReleaseStatusSetsReleaseStatus(): void
    {
        $status = 'released';
        $this->subject->setReleaseStatus($status);
        self::assertSame($status, $this->subject->getReleaseStatus());
    }

    /**
     * @test
     */
    public function hasImageReturnsFalseWhenNoImage(): void
    {
        $this->subject->setImagePath(null);
        self::assertFalse($this->subject->hasImage());
    }

    /**
     * @test
     */
    public function hasImageReturnsTrueWhenImageExists(): void
    {
        $this->subject->setImagePath('https://example.com/image.jpg');
        self::assertTrue($this->subject->hasImage());
    }

    /**
     * @test
     */
    public function getFormattedDateTimeReturnsCorrectFormat(): void
    {
        $date = new \DateTime('2026-03-28');
        $this->subject->setStartDate($date);
        $this->subject->setStartTime('21:00');
        
        self::assertSame('28.03.2026 21:00', $this->subject->getFormattedDateTime());
    }

    /**
     * @test
     */
    public function getFormattedDateTimeWithoutTimeReturnsDateOnly(): void
    {
        $date = new \DateTime('2026-03-28');
        $this->subject->setStartDate($date);
        $this->subject->setStartTime(null);
        
        self::assertSame('28.03.2026', $this->subject->getFormattedDateTime());
    }

    /**
     * @test
     */
    public function getAgeRestrictionStringReturnsCorrectFormat(): void
    {
        $this->subject->setMinAge(18);
        $this->subject->setMaxAge(null);
        
        self::assertSame('Ab 18 Jahren', $this->subject->getAgeRestrictionString());
    }

    /**
     * @test
     */
    public function getAgeRestrictionStringWithMaxAgeReturnsRange(): void
    {
        $this->subject->setMinAge(16);
        $this->subject->setMaxAge(25);
        
        self::assertSame('16-25 Jahre', $this->subject->getAgeRestrictionString());
    }

    /**
     * @test
     */
    public function getAgeRestrictionStringWithoutAgeReturnsEmpty(): void
    {
        $this->subject->setMinAge(null);
        $this->subject->setMaxAge(null);
        
        self::assertSame('', $this->subject->getAgeRestrictionString());
    }

    /**
     * @test
     */
    public function fromApiDataCreatesEventCorrectly(): void
    {
        $apiData = [
            'event_date_id' => 550,
            'id' => 470,
            'title' => 'Test Event',
            'subtitle' => 'Test Subtitle',
            'start_date' => '2026-03-28',
            'start_time' => '21:00',
            'entry_time' => '20:00',
            'venue_id' => 52,
            'venue_name' => 'Test Venue',
            'venue_city' => 'Test City',
            'venue_street' => 'Test Street',
            'venue_house_number' => '30',
            'venue_postal_code' => '24937',
            'venue_country' => 'DEU',
            'venue_lat' => 54.805848,
            'venue_lon' => 9.453062,
            'image_id' => 386,
            'image_path' => 'https://example.com/image.jpg',
            'organization_id' => 9,
            'organization_name' => 'Test Organization',
            'event_types' => [
                ['type_id' => 53, 'genre_id' => 0],
                ['type_id' => 44, 'genre_id' => 0]
            ],
            'languages' => ['de', 'en'],
            'tags' => ['rock', 'live'],
            'min_age' => 18,
            'max_age' => null,
            'release_status' => 'released'
        ];

        $event = Event::fromApiData($apiData);
        
        self::assertSame(550, $event->getEventDateId());
        self::assertSame(470, $event->getId());
        self::assertSame('Test Event', $event->getTitle());
        self::assertSame('Test Subtitle', $event->getSubtitle());
        self::assertSame('2026-03-28', $event->getStartDate()->format('Y-m-d'));
        self::assertSame('21:00', $event->getStartTime());
        self::assertSame('20:00', $event->getEntryTime());
        self::assertSame('Test Venue', $event->getVenueName());
        self::assertSame('Test City', $event->getVenueCity());
        self::assertSame('https://example.com/image.jpg', $event->getImagePath());
        self::assertSame('Test Organization', $event->getOrganizationName());
        self::assertCount(2, $event->getEventTypes());
        self::assertSame(['de', 'en'], $event->getLanguages());
        self::assertSame(['rock', 'live'], $event->getTags());
        self::assertSame(18, $event->getMinAge());
        self::assertNull($event->getMaxAge());
        self::assertSame('released', $event->getReleaseStatus());
    }

    /**
     * @test
     */
    public function fromApiDataHandlesNullValues(): void
    {
        $apiData = [
            'event_date_id' => 550,
            'id' => 470,
            'title' => 'Test Event',
            'subtitle' => null,
            'start_date' => '2026-03-28',
            'start_time' => null,
            'entry_time' => null,
            'venue_name' => 'Test Venue',
            'venue_city' => 'Test City',
            'image_path' => null,
            'organization_name' => null,
            'event_types' => [],
            'languages' => null,
            'tags' => null,
            'min_age' => null,
            'max_age' => null,
            'release_status' => 'draft'
        ];

        $event = Event::fromApiData($apiData);
        
        self::assertNull($event->getSubtitle());
        self::assertNull($event->getStartTime());
        self::assertNull($event->getEntryTime());
        self::assertNull($event->getImagePath());
        self::assertSame('', $event->getOrganizationName());
        self::assertEmpty($event->getEventTypes());
        self::assertEmpty($event->getLanguages());
        self::assertEmpty($event->getTags());
        self::assertNull($event->getMinAge());
        self::assertNull($event->getMaxAge());
        self::assertSame('draft', $event->getReleaseStatus());
    }
}