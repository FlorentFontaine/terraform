<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ .  '/../ctrl/ctrl.php';
require_once __DIR__ . '/../dbClasses/AccesDonnees.php';
require_once __DIR__ . '/../htmlClasses/table.php';
require_once __DIR__ . '/../Bilan/Bilan.class.php';

if (isset($_SESSION['MoisHisto']) && $_SESSION['MoisHisto']) {
    $MoisVoulu = $_SESSION["MoisHisto"];
} else {
    $MoisVoulu = $_SESSION["station_STA_DERNBAL"];
}

$MesLignesActif = Bilan::getTab($_SESSION["station_DOS_NUM"], $MoisVoulu, "actif");
$MesLignesPassif = Bilan::getTab($_SESSION["station_DOS_NUM"], $MoisVoulu, "passif");

include_once __DIR__ . '/../Bilan/MListe.php';
