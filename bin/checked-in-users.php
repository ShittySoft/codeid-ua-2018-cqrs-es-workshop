#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace Building\Projector;

use Prooph\EventStore\EventStore;

(function () {
    $container = require __DIR__ . '/../container.php';

    $eventStore = $container->get(EventStore::class);

    // 1. fetch relevant stuff from the event store
    // 2. make a list of checked in users (fold / accumulator)
    // 3. save that list in public/{buildingId}.json
})();
