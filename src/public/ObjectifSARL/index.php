<?php

session_start();

include_once __DIR__ . '/../ctrl/ctrl.php';
include_once __DIR__ . '/../dbClasses/station.php';
include_once __DIR__ . '/../ObjectifSARL/objectifsarl.class.php';

global $Arrondir;

$Arrondir = true;

$param1 = $_GET['param1'] ?? 'Produits';
$param2 = $_GET['param2'] ?? 0;

if ($param1 == "Produits" && $param2) {
    $Title = "Objectif Marges";
} elseif ($param1 == "Produits" && !$param2) {
    $Title = "Objectif CA";
} elseif ($param1 == "Charges") {
    $Title = "Objectif Charges";
}

include "objectifsarl.vue.php";
