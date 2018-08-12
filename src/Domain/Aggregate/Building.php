<?php

declare(strict_types=1);

namespace Building\Domain\Aggregate;

use Building\Domain\DomainEvent;
use Building\Domain\DomainEvent\NewBuildingWasRegistered;
use Prooph\EventSourcing\AggregateRoot;
use Rhumsaa\Uuid\Uuid;

final class Building extends AggregateRoot
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var string
     */
    private $name;

    /** array<string, null> */
    private $checkedInUsers = [];

    public static function new(string $name) : self
    {
        $self = new self();

        $self->recordThat(NewBuildingWasRegistered::occur(
            (string) Uuid::uuid4(),
            [
                'name' => $name
            ]
        ));

        return $self;
    }

    public function checkInUser(string $username)
    {
        if (array_key_exists($username, $this->checkedInUsers)) {
            throw new \RuntimeException(sprintf(
                'User "%s" is already checked into "%s" (%s)',
                $username,
                $this->name,
                $this->uuid->toString()
            ));
        }

        $this->recordThat(DomainEvent\UserCheckedIn::toBuilding(
            $this->uuid,
            $username
        ));
    }

    public function checkOutUser(string $username)
    {
        if (! array_key_exists($username, $this->checkedInUsers)) {
            throw new \RuntimeException(sprintf(
                'User "%s" is not checked into "%s" (%s)',
                $username,
                $this->name,
                $this->uuid->toString()
            ));
        }

        $this->recordThat(DomainEvent\UserCheckedOut::ofBuilding(
            $this->uuid,
            $username
        ));
    }

    public function whenNewBuildingWasRegistered(NewBuildingWasRegistered $event)
    {
        $this->uuid = Uuid::fromString($event->aggregateId());
        $this->name = $event->name();
    }

    protected function whenUserCheckedIn(DomainEvent\UserCheckedIn $checkedIn) : void
    {
        $this->checkedInUsers[$checkedIn->username()] = null;
    }

    protected function whenUserCheckedOut(DomainEvent\UserCheckedOut $checkedIn) : void
    {
        unset($this->checkedInUsers[$checkedIn->username()]);
    }

    /**
     * {@inheritDoc}
     */
    protected function aggregateId() : string
    {
        return (string) $this->uuid;
    }

    /**
     * {@inheritDoc}
     */
    public function id() : string
    {
        return $this->aggregateId();
    }
}
