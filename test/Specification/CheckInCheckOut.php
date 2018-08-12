<?php

declare(strict_types=1);

namespace Specification;

use Behat\Behat\Context\Context;
use Building\Domain\Aggregate\Building;
use Building\Domain\DomainEvent\NewBuildingWasRegistered;
use Building\Domain\DomainEvent\UserCheckedIn;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\Aggregate\AggregateType;
use Rhumsaa\Uuid\Uuid;

final class CheckInCheckOut implements Context
{
    /** @var array<string, array<int, AggregateChanged>> */
    private $pastEvents = [];

    /** @var array<string, Building> */
    private $buildings = [];

    /** @var array<string, array<int, AggregateChanged>> */
    private $recorded    = [];

    /**
     * @Given the building ":buildingName" was registered
     */
    public function the_building_was_registered(string $buildingName) : void
    {
        $this->pastEvents[$buildingName][] = NewBuildingWasRegistered::occur(
            Uuid::uuid4()->toString(),
            ['name' => $buildingName]
        );
    }

    /**
     * @When ":person" checks into ":buildingName"
     */
    public function person_checks_into(string $person, string $buildingName) : void
    {
        $this
            ->building($buildingName)
            ->checkInUser($person);
    }

    /**
     * @Then ":person" should have been checked into ":buildingName"
     */
    public function person_should_have_been_checked_into_building(string $person, string $buildingName) : void
    {
        $lastEvent = $this->popRecordedEvent($buildingName);

        if (! $lastEvent instanceof UserCheckedIn) {
            throw new \Exception('Incorrect event type');
        }

        if ($lastEvent->username() !== $person) {
            throw new \Exception('Incorrect person name');
        }
    }

    private function building(string $name) : Building
    {
        return $this->buildings[$name]
            ?? $this->buildings[$name] = (new AggregateTranslator())
                ->reconstituteAggregateFromHistory(
                    AggregateType::fromAggregateRootClass(Building::class),
                    new \ArrayIterator($this->pastEvents[$name])
                );
    }

    private function popRecordedEvent(string $name) : AggregateChanged
    {
        if (! isset($this->recorded[$name])) {
            $this->recorded[$name] = (new AggregateTranslator())
                ->extractPendingStreamEvents($this->building($name));
        }

        return array_shift($this->recorded[$name]);
    }
}
