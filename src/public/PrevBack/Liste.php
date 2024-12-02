<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../ctrl/ctrl.php';
require_once __DIR__ . '/../dbClasses/AccesDonnees.php';
require_once __DIR__ . '/../htmlClasses/table.php';

global $Section;

include_once __DIR__ . '/../PrevBack/prev.class.php';

if (isset($_SESSION["inLIE_NUM"]) && $_SESSION["inLIE_NUM"]) {
    $_SESSION["station_DOS_NUM"] = $_SESSION["inLIE_NUM_station_DOS_NUM"];
}

$Section = "prev";

$MesLignesProduits = Previsionnel::getTab('Produits', $_SESSION["MoisHisto"]);
$MesLignesCharges = Previsionnel::getTab('Charges', $_SESSION["MoisHisto"]);

if (isset($_SESSION["inLIE_NUM"]) && $_SESSION["inLIE_NUM"]) {
    $_SESSION["station_DOS_NUM"] = false;
}

include __DIR__ . '/../PrevBack/MListe.php';
