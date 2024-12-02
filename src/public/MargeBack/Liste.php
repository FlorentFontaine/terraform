<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../ctrl/ctrl.php';
require_once __DIR__ . '/../dbClasses/AccesDonnees.php';
require_once __DIR__ . '/../MargeBack/marge.class.php';
require_once __DIR__ . '/../htmlClasses/table.php';

global $Arrondir;

$Arrondir = true;
$Section = "Marge";
$MoisVoulu = $_SESSION["MoisHisto"];

if (isset($_POST["moishisto"]) && $_POST["moishisto"]) {
    $MoisVoulu = $_POST["moishisto"];
}

$MesLignesMarge = Marge::getTab($MoisVoulu);

include_once __DIR__ . '/../MargeBack/MListe.php';
