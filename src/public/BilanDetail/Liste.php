<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

//pour laisser passer le détail des comptes dans la consolidation
$Lie_numforce = false;

if (!isset($_SESSION["inLIE_NUM"]) || !$_SESSION["inLIE_NUM"]) {
    $_SESSION["inLIE_NUM"] = true;
    $Lie_numforce = true;
}

include_once __DIR__ . '/../ctrl/ctrl.php';

if ($Lie_numforce) {
    $_SESSION["inLIE_NUM"] = false;
}

require_once __DIR__ . '/../dbClasses/AccesDonnees.php';
require_once __DIR__ . '/../htmlClasses/table.php';
require_once __DIR__ . '/../BilanDetail/bilandetail.class.php';

$MesLignes = BilanDetail::getTab($_SESSION["MoisHisto"], $_POST['CPTB_NUM']);

header('Content-type: text/html; charset=iso-8859-1');

include_once __DIR__ . '/../BilanDetail/resume.vue.php';
