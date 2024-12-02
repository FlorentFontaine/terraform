<?php

use Cicd\PrintServerClient\CurlClient;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

extract($_GET);
extract($_POST);

if ((isset($Imprimer) && $Imprimer) || (isset($validMAJBase) && $validMAJBase)) {
    $NoUpdateSget = true;
}

require_once '../ctrl/ctrl.php';
require_once '../dbClasses/station.php';
require_once '../dbClasses/AccesDonnees.php';
require_once '../htmlClasses/table.php';
require_once '../ImprimBack/imprim.class.php';
require_once '../HTML2PDF_CLIENT/classes/CurlClient.php';

// Garde en session le format d'impression voulu
$_SESSION['formatImpression'] = $_POST['formatImpression'] ?? 'PDF';

$MyImp = $CheckMyImp ?? [];

if ($MyImp) {
    ob_start();

    $Prem = true;

    foreach ($MyImp as $Cle => $val) {
        if (!$Prem) {
            Impression::Intermediaire();
        }
        $Prem = false;
        switch ($Cle) {

            case "balance":
                Impression::Balance();
                break;

            case "renseignement":
                Impression::Renseignement();
                break;

            case "mensuelp":
                Impression::Mensuel("produits");
                break;
            case "mensuelc":
                Impression::Mensuel("charges");
                break;

            case "cumulp":
                Impression::Cumul("produits");
                break;
            case "cumulc":
                Impression::Cumul("charges");
                break;

            case "detailproduit":
                Impression::DetailProduit();
                break;
            case "detailcharge":
                Impression::DetailCharge();
                break;

            case "compproduit":
                Impression::CompProduit();
                break;
            case "compcharge":
                Impression::CompCharge();
                break;

            case "synthese":
                Impression::synthese();
                break;

            case "marge":
                Impression::Marge();
                break;
            case "bilan":
                Impression::Bilan();
                break;

            case "prev":
                Impression::Prev();
                break;

            case "synthese":
                Impression::synthese(false);
                break;

            case "obj_CA":
                Impression::objectif("Produits", false);
                break;

            case "obj_carb":
                Impression::objectif("Carburants", false);
                break;

            case "obj_marge":
                Impression::objectif("Produits", true);
                break;

            case "obj_charge":
                Impression::objectif("Charges", false);
                break;

            case "anomalie":
                Impression::Anomalie();
                break;

            case "mensuelp_cluster":
                Impression::Mensuel("produits", true);
                break;

            case "mensuelc_cluster":
                Impression::Mensuel("charges", true);
                break;

            case "compproduit_cluster":
                Impression::CompProduit(true);
                break;

            case "compcharge_cluster":
                Impression::CompCharge(true);
                break;

            case "bilan_cluster":
                Impression::Bilan(true);
                break;

            case "synthese_cluster":
                Impression::synthese(true);
                break;

            case "garde":
            default:
                Impression::Garde();
        }
    }
    Impression::Pied();
}

//sécurisé ds le controleur ../ctrl/ctrl.php
if ($_SESSION["ioreport_MAJB"] && $validMAJBase) {
    include_once '../BenchMark/MAJBase.php';
}


if ($MyImp) {
    list($parm, $paramFile) = include '../HTML2PDF_CLIENT/config/config.php';

    $client = new CurlClient();
    $response = $client->schedulePrintTask($parm, $paramFile, array(
        'fluxHTML' => ob_get_clean(),
        'formatImpression' => $_SESSION['formatImpression'],
        'mail' => array(
            'emails' => isset($_POST['emails']) ? $_POST['emails'] : '',
            'sujet' => '',
            'corps' => ''
        ),
        'channelId' => $_POST['channelId'],
        'impressionId' => $_POST['impressionId']
    ), false);
}

include("../ImprimBack/MListe.php");
