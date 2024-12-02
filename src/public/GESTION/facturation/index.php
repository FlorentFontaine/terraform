<?php
use Helpers\StringHelper;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../ctrl.php";
require_once __DIR__ . '/../facturation/facturation.class.php';

$DateDebut = (isset($_POST["DateDebut"]) && $_POST["DateDebut"]) ? $_POST["DateDebut"] : '';
$DateFin = (isset($_POST["DateFin"]) && $_POST["DateFin"]) ? $_POST["DateFin"] : '';

$Imprimer = (isset($_POST['Imprimer']) && $_POST['Imprimer']) ? $_POST['Imprimer'] : '';

if (!$DateDebut) {
    $DateDebut = StringHelper::DatePlus(date("Y-m-d"), array("moisplus" => -1, "joursplus" => -1));
} else {
    $DateDebut = StringHelper::DateFr2MySql($DateDebut);
}

$DateFin = StringHelper::DateFr2MySql($DateFin);

if ($DateFin == 0 && $DateDebut) {
    $DateFin = StringHelper::DatePlus($DateDebut, array("moisplus" => 1));
}

if ($DateFin && $DateDebut == 0) {
    $DateDebut = StringHelper::DatePlus($DateFin, array("moisplus" => -1));
}

include_once __DIR__ . "/facturation.vue.php";
