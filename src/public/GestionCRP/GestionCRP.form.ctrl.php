<?php

use Helpers\StringHelper;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once '../ctrl/ctrl.php';
include_once '../dbClasses/station.php';
include_once '../dbClasses/comptable.php';
require_once '../htmlClasses/table.php';

$param = array("Intitule" => "CRP", "colspanCenter" => 1, "colspanRight" => 4);
$EnteteTab = EnteteTab::HTML_EnteteTab($param);


if (isset($_POST["enregistrer"]) && $_POST["enregistrer"]) {
    $CRP_NUM = CRP_Detail::enregister_CRP_Detail($_POST);

    if ($CRP_NUM) {
        $param = [
            "tabCriteres" => [
                "CRP_NUM" => $CRP_NUM
            ]
        ];
        $CRP = db_CRP::select_CRP($param);
        $CRP = $CRP[$CRP_NUM];

        $param = [
            "CRP_NUM" => $CRP_NUM,
            "STA_NUM" => $_SESSION["station_STA_NUM"]
        ];

        Previsionnel::RefactAllPrev($param, $e);
    }
}

if (!isset($CRP_NUM) || !$CRP_NUM) {
    $CRP_NUM = $_GET["CRP_NUM"];
}

if ($CRP_NUM) {
    $param = [
        "tabCriteres" => [
            "CRP_NUM" => $_GET["CRP_NUM"]
        ]
    ];
    $CRP = db_CRP::select_CRP($param);
    $CRP = $CRP[$_GET["CRP_NUM"]];

    // Tableau Produits
    $param['Type'] = 'Produits';
    $MesLignesProduits = CRP_Detail::get_TabCRPDetail($param);

    // Tableau Charges
    $param['Type'] = 'Charges';
    $MesLignesCharges = CRP_Detail::get_TabCRPDetail($param);

    // Récupération du CRP_NUM précédent (vérification si la copie du précédent est possible)
    $param = [
        "tabCriteres" => [
            "STA_NUM" => $_SESSION["station_STA_NUM"],
            "CRP_NUM" => $CRP_NUM,
            "CRP_FIN" => $CRP["CRP_DBT"]
        ],
        "tabOP" => [
            "CRP_NUM" => "!=",
            "CRP_FIN" => "<"
        ],
        "triRequete" => " order by CRP_FIN DESC LIMIT 0,1 "
    ];
    $LastCRP = db_CRP::select_CRP($param);
    $array_keys = array_keys($LastCRP);
    $CRP_NUM_PREC = $array_keys[0] ?? null;

    //Récupération du dernier CRP pour cette SARL
    $param = array(
        "tabCriteres" => array("STA_NUM" => $_SESSION["station_STA_NUM"]),
        "triRequete" => " order by CRP_FIN DESC LIMIT 0,1 "
    );
    $LastCRP = db_CRP::select_CRP($param);
    $array_keys = array_keys($LastCRP);
    $LAST_CRP_NUM = $array_keys[0];

    $TitleTable = "CRP du " . StringHelper::MySql2DateFr($CRP["CRP_DBT"]) . " au " . StringHelper::MySql2DateFr($CRP["CRP_FIN"]);
    include __DIR__ . "/GestionCRP.form.vue.php";
    die();
}

header("Location:index.php");
die();

