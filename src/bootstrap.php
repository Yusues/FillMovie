<?php
declare(strict_types=1);

// Single include that wires up the whole back end. Every page in public/ starts
// with: require __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/omdb.php';
