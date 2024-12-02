<?php 
session_start();

//pour laisser passer le détail des compte dans la consolidation
$Lie_numforce = false;
if(!$_SESSION["inLIE_NUM"])
{
    $_SESSION["inLIE_NUM"] = true;
    $Lie_numforce = true;

}

$Periode["BAL_MOIS_DEB"] = $_GET["BAL_MOIS_DEB"];
$Periode["BAL_MOIS_FIN"] = $_GET["BAL_MOIS_FIN"];

include_once '../ctrl/ctrl.php';

if($Lie_numforce) {
    $_SESSION["inLIE_NUM"] = false;
}

require_once('../dbClasses/AccesDonnees.php');
require_once('../htmlClasses/table.php');
require_once '../synthesedetail/synthesedetail.class.php';


$MoisVoulu = $_SESSION["MoisHisto"];

$MesLignes = SyntheseDetail::getTab($Type,$MoisVoulu,false,false,$codePoste_synthese);


header('Content-type: text/html; charset=iso-8859-1');
include '../synthesedetail/resume.vue.php';

