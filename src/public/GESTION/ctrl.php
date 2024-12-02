<?php

use Classes\Debug\Debugbar\Debug;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../Init/bootstrap.php';

Debug::init();

require_once __DIR__ . "/../auth/classes/Auth.class.php";

if (
    !isset($_SESSION["LOCKERS_USER"])
    || (!isset($_SESSION["LOCKERS_USER"]["admin"]) || !$_SESSION["LOCKERS_USER"]["admin"])
) {
    header("Location: ../../StationBack/Liste.php");
    exit();
}

// On vérifie que le token est toujours valide
$Authentification = new Auth;
$Authentification->checkSession();
