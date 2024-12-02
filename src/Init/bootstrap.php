<?php

namespace Init;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Chargement de l'"autoload" composer
 */
require_once __DIR__ . '/../vendor/autoload.php';
