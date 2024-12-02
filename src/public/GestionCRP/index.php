<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../ctrl/ctrl.php';
include_once __DIR__ . '/../dbClasses/station.php';
include_once __DIR__ . '/../GestionCRP/CRP.class.php';
include_once __DIR__ . '/../GestionCRP/CRP_Detail.class.php';

global $Arrondir;

$Arrondir = true;
$new_crp_click = 0;

$_GET["page"] ??= '';

switch ($_GET["page"]) {
    case "crp_en_cours":
        //Récupération du dernier CRP pour cette SARL
        $param = array(
            "tabCriteres" => array("STA_NUM" => $_SESSION["station_STA_NUM"]),
            "triRequete" => " order by CRP_FIN DESC LIMIT 0,1 "
        );
        $LastCRP = db_CRP::select_CRP($param);
        $array_keys = array_keys($LastCRP);
        $key = $array_keys[0];

        header("Location:index.php?page=form&CRP_NUM=" . $key);
        break;

    case "form":
        include __DIR__ . "/GestionCRP.form.ctrl.php";
        break;

    case "nouveau_crp":
        $new_crp_click = 1;
        include __DIR__ . "/GestionCRP.liste.ctrl.php";
        break;

    case "liste":
    default:
        include __DIR__ . "/GestionCRP.liste.ctrl.php";
        break;
}
