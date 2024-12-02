<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../GestionCRP/CRP.class.php';
require_once __DIR__ . '/../GestionCRP/CRP_Detail.class.php';

header('Content-type: text/html; charset=iso-8859-1');

$AjaxReturn = [];

$_POST["action"] ??= '';
$Erreur = '';

switch ($_POST["action"]) {
    case "HTML_NewCRP":
        // Affichage du tableau de creation de nouveau CRP
        $AjaxReturn["html"] = CRP::HTML_FormCRP($_POST);

        break;

    case "HTML_FormDateCRP":
        // Affichage du tableau de modification des dates du CRP
        $AjaxReturn["html"] = CRP::HTML_FormCRP($_POST);

        break;

    case "ajout_crp":
        // Enregistrement du CRP
        $AjaxReturn["CRP_NUM"] = CRP::enregistrer_CRP($_POST, $Erreur);

        if ($_POST["modif_date"] && $AjaxReturn["CRP_NUM"]) {
            // Recalcule des montants mensuels
            CRP_Detail::recalcul_CRP_Detail(array("CRP_NUM" => $AjaxReturn["CRP_NUM"]), $Erreur);
        }

        if (!$Erreur) {
            $AjaxReturn["success"] = true;
        } else {
            $AjaxReturn["success"] = false;
            $AjaxReturn["ERROR"] = $Erreur;
        }

        break;

    case "HTML_FormCopie_CRP":
        // Affichage du formulaire de copie du CRP précédent
        $AjaxReturn["html"] = CRP::HTML_FormCopie_CRP($_POST, $Erreur);

        break;

    case "copie_crp":
        // Affichage du tableau de creation de nouveau CRP
        $AjaxReturn["CRP_NUM"] = CRP_Detail::copie_CRP_Detail($_POST, $Erreur);

        if (!$Erreur) {
            $AjaxReturn["success"] = true;
        } else {
            $AjaxReturn["success"] = false;
            $AjaxReturn["ERROR"] = $Erreur;
        }

        break;

    default:
        break;
}

echo json_encode($AjaxReturn);
