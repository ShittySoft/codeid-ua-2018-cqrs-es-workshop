#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace Building\Projector;

use Building\Domain\Aggregate\Building;
use Building\Domain\DomainEvent\UserCheckedIn;
use Building\Domain\DomainEvent\UserCheckedOut;
use Interop\Container\ContainerInterface;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream\StreamName;

(function () {
    /** @var ContainerInterface $container */
    $container = require __DIR__ . '/../container.php';

    $eventStore = $container->get(EventStore::class);

    /** @var AggregateChanged[] $allEvents */
    $allEvents = $eventStore->loadEventsByMetadataFrom(
        new StreamName('event_stream'),
        ['aggregate_type' => Building::class]
    );

    $usersPerBuilding = [];

    foreach ($allEvents as $event) {
        $buildingId = $event->aggregateId();
        $usersPerBuilding[$buildingId] = $usersPerBuilding[$buildingId] ?? [];

        if ($event instanceof UserCheckedIn) {
            $usersPerBuilding[$buildingId][$event->username()] = true;
        }

        if ($event instanceof UserCheckedOut) {
            unset($usersPerBuilding[$buildingId][$event->username()]);
        }
    }

    array_walk($usersPerBuilding, function (array $users, string $buildingId) : void {
        file_put_contents(
            __DIR__ . '/../public/' . $buildingId . '.json',
            json_encode(array_keys($users))
        );
    });
})();
