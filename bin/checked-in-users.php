#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace Building\Projector;

(function () {
    (require __DIR__ . '/../container.php')
        ->get('project-checked-in-users')();
})();
