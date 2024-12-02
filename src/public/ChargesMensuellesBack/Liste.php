<?php

use Services\Debug\DebugService;

session_start();
include_once '../ctrl/ctrl.php';
require_once('../dbClasses/AccesDonnees.php');
require_once('../htmlClasses/table.php');
require_once '../compChargesBack/compCharges.class.php';
require_once('../ChargesMensuellesBack/ChargesMensuelles.class.php');


if ($_GET["Produits"]) {
    $Type = 'Produits';
    $Section = "compProd";
} else {
    $Type = 'Charges';
    $Section = "compCharges";
}

if ($_SESSION['MoisHisto']) {
    $MoisVoulu = $_SESSION['MoisHisto'];
} else {
    $MoisVoulu = $_SESSION["station_STA_DERNBAL"];
}

$MesLignes = compChargesProdMensuel::getTab($Type,$MoisVoulu,false,false,false,NULL,$print,$cluster);

$opt['cumul'] = 1;

if ($cluster) {
    $opt['cluster'] = 1;
}

if ($Imprimer) {
    $opt['allCompte'] = true;
}

if ($Type == "Produits") {
    $MesLignesTabCharges = compChargesProd::getTab("Charges", $MoisVoulu, false, false, false, $opt);
}

$MesLignesTab2 = compChargesProd::getTab($Type, $MoisVoulu, false, false, false, $opt);


foreach ($MesLignes as $key => &$value) {
    $j++;
    $i = 0;

    if (!$MesLignesTab2[$key]) {
        $value = NULL;
    } else {
        foreach ($MesLignesTab2[$key] as $key2 => $value2) {
            $i++;

            if ($i > 1) {
                $value[] = $value2;
            } else {
                $value[] = "";
            }
        }
    }
}


include '../ChargesMensuellesBack/MListe.php';
