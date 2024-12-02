<?php

use Classes\Alert;
use Helpers\StringHelper;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../ctrl/ctrl.php';
require_once __DIR__ . '/../dbClasses/station.php';
require_once __DIR__ . '/../dbClasses/AccesDonnees.php';
require_once __DIR__ . '/../htmlClasses/table.php';
require_once __DIR__ . '/../ImprimBack/imprim.class.php';
require_once __DIR__ . '/../Anomalie/Anomalie.class.php';
require_once __DIR__ . '/../BenchMark/MAJBase.class.php';

$MAJBase = true;
$MesDateNonMAJ = dbAcces::getDateMAJBase($_SESSION["station_DOS_NUM"], null, 2);
$DateMAJ = false;

foreach ($MesDateNonMAJ as $UneDate => $val) {
    $DateMAJ = $UneDate;
    break;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
    <link rel="stylesheet" href="../style.css" type="text/css" media="screen"/>
    <link rel="stylesheet" href="../print.css" type="text/css" media="print"/>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>MAJ Base | MyReport</title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
    <style type="text/css">
        #tetepage {
            display: none;
        }

        .submit {
            visibility: hidden;
        }

    </style>
</head>
<body style="background-image: none">
<div style="width: 850px;height:0;border:0"></div>
<div id="corp" style="display: none;overflow: hidden;">

<?php
$Wait = true;

if ($DateMAJ >= $_SESSION["MoisHisto"] || !$DateMAJ) {
    $loadhref = "../GardeBack/Garde.php";

    if (isset($_SESSION["Myloadhref"]) && $_SESSION["Myloadhref"]) {
        $loadhref = $_SESSION["Myloadhref"];
    }
}


include __DIR__ . "/../include/entete.inc.php";

$text = '';

if ($DateMAJ) {
    $return = MAJBase::DoMAJBase($DateMAJ, $_SESSION["station_DOS_NUM"], $TabCharge, $TabProduit, $_SESSION["station_STA_NUM"]);

    if ($return === true) {
        $text .= '<p style="color:green">
                    Validation de la p&eacute;riode ' . StringHelper::MySql2DateFr($DateMAJ) . '
                </p>';

        // ------------------------------------------------------------
        // Définition et envoi du mail d'information de MAJ Base
        $to = dbAcces::getMail($_SESSION["station_LIE_NUM"], $_SESSION["station_DOS_NUM"], true);
        $subject = "My Report - Mise a disposition du mois " . StringHelper::MySql2DateFr($DateMAJ) . " pour " . $_SESSION["station_STA_SARL"];

        $message = "Bonjour,
        <br /><br />Les &eacute;tats de gestion de la soci&eacute;t&eacute; " . $_SESSION["station_STA_SARL"] . " (" . $_SESSION["station_LIE_NOM"] . ") sont disponibles pour la p&eacute;riode " . StringHelper::MySql2DateFr($DateMAJ) . "
        <br /><br /><br />Consultez ce dossier &agrave; cette adresse : <a href='https://myreport.cicd.biz'>myreport.cicd.biz</a>
        <br /><br /><br /><br /><br />---------------------------------------------
        <br />Ceci est un mail automatique, merci de ne pas r&eacute;pondre";

        // Envoi du mail
        Alert::mail($subject, $message, $to);
    } elseif ($return == "0") {
        $text .= '<p style="color:red">
                    Il reste ' . ($_SESSION['NbAno'] - 1) .' anomalie(s) sur la p&eacute;riode ' . StringHelper::MySql2DateFr($DateMAJ) .'<br />
                    Op&eacute;ration annul&eacute;e.
                </p>';
    } else {
        $text .= '<p style="color:red">
                    Une erreur est survenue lors de la mise &agrave; de la base.
                </p>';
    }
} else {
    $text .= '<p style="color:blue">
                Mise &agrave; jour de la base termin&eacute;e
            </p>';
}

Anomalie::CompterAnomalies();
?>

    <div style="margin-top: 30px; padding: 10px; background-color: #bedbf8; text-align: center">
        <img src="../images/logo_myreport.png" alt="logo My Report" style="height: 100px; margin-top: 30px"/>
        <img src="../images/app_logo.png" alt="My Report" style="width: 250px"/>
        <br/>
        <h2>Mise &agrave; jour de la base de Benchmarking</h2>
        <br/>
    </div>
    <div style="text-align: center; font-size: 1.2em; font-weight: bold">
        <h3><?= $_SESSION["station_STA_SARL"] ?></h3>
        <?= $text ?>
        <svg class="loader" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 340 340">
            <circle cx="170" cy="170" r="160" stroke="#2799ff"/>
            <circle cx="170" cy="170" r="135" stroke="#0F67BF"/>
            <circle cx="170" cy="170" r="110" stroke="#2799ff"/>
            <circle cx="170" cy="170" r="85" stroke="#0F67BF"/>
        </svg>
    </div>
</div>

