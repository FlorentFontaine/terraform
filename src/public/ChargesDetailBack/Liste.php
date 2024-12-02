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

if($Lie_numforce){
    $_SESSION["inLIE_NUM"] = false;
}

require_once('../dbClasses/AccesDonnees.php');
require_once('../htmlClasses/table.php');
require_once '../ChargesDetailBack/detailcharge.class.php';



if(!$codePoste)
{
    if($_GET["Produits"])
    {
	    $Type = 'Produits';
	    $Section = "compProd";
    }
    else
    {
	    $Type = 'Charges';
	    $Section = "compCharges";
    }
}

$MoisVoulu = $_SESSION["MoisHisto"];

$MesLignes = DetailChargeProd::getTab($Type,$MoisVoulu,false,false,$codePoste,$resume,$Periode);

if($resume)
{
     header('Content-type: text/html; charset=iso-8859-1');
    include '../ChargesDetailBack/resume.vue.php';
}
else
    include '../ChargesDetailBack/MListe.php';

?>
