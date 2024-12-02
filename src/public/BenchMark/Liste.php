<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../ctrl/ctrl.php';
require_once __DIR__ . '/../BenchMark/benchmark.class.php';
require_once __DIR__ . '/../compChargesBack/compCharges.class.php';
require_once __DIR__ . '/../dbClasses/station.php';
require_once __DIR__ . '/../dbClasses/AccesDonnees.php';
require_once __DIR__ . '/../htmlClasses/table.php';

function TypoStr($Typo)
{
    if ($Typo == "oui") {
        return "&radic;";
    } elseif ($Typo == "non") {
        return "x";
    }

    return "";
}

if (isset($_GET["LIE_NUM"]) && $_GET["LIE_NUM"]) {
    $_POST["LIE_NUM"] = $_GET["LIE_NUM"];
}

if (!isset($_POST['LIE_NUM'])) {
    $_POST['LIE_NUM'] = '';
}

if (!isset($_SESSION['BenchUpdated']) || !$_SESSION['BenchUpdated']) {
    $_POST["rechargerBench"] = 1;
}

Benchmark::getMaxMinDate($Max, $Min, $_POST['LIE_NUM']);

$MoisActuel = date("Y-m-00");

if ((!isset($MoisDeb) || !$MoisDeb) || (!isset($MoisFin) || !$MoisFin)) {
    if (($MoisActuel <= $Max || !$Max) && ($MoisActuel >= $Min || !$Min)) {
        $MoisDeb = $MoisActuel;
        $MoisFin = $MoisActuel;
        $_POST["MoisDeb"] = $MoisActuel;
        $_POST["MoisFin"] = $MoisActuel;

    } else {
        $MoisDeb = $Max;
        $MoisFin = $Max;

        $_POST["MoisDeb"] = $Max;
        $_POST["MoisFin"] = $Max;
    }
}

if ($MoisFin < $MoisDeb) {
    $MoisFin = $MoisDeb;
    $_POST["MoisFin"] = $MoisDeb;
}

if ($_SESSION["User"]->Type == "Secteur") {
    $_POST["codeChefSecteur"] = $_SESSION["User"]->NumTableIdUser;
} elseif ($_SESSION["User"]->Type == "Region") {
    $_POST["codeChefRegion"] = $_SESSION["User"]->NumTableIdUser;
}

$NbDossier = count(Benchmark::getStationInclude($MoisDeb, $MoisFin, true, $_POST));

$_POST["TypePrev"] = "AGIP";
$_POST['color'] = true;

extract($_POST);

$MesLignesProduits = compChargesProd::getTabBench("Produits", $MoisDeb, $MoisFin, $_POST, $etude);
$MesLignesCharges = compChargesProd::getTabBench("Charges", $MoisDeb, $MoisFin, $_POST, $etude);

$_SESSION["ioreport_Bench_POST"] = $_POST;

include_once __DIR__ . '/../BenchMark/MListe.php';

//Benchmark::reset_tmp_BenchLieuIn();
