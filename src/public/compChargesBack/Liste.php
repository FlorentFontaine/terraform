<?php 
session_start();
include_once '../ctrl/ctrl.php';
require_once('../dbClasses/AccesDonnees.php');
require_once('../htmlClasses/table.php');

require_once('../compChargesBack/compCharges.class.php');



$MoisVoulu = $_SESSION["MoisHisto"];

if($mensuel)
    $opt['mensuel'] = true;

if($cumul)
    $opt['cumul'] = true;

if($cluster)
    $opt['cluster'] = true;


if($Imprimer)
{
    $opt["allCompte"] = true;
}

$opt["MAJBASE"] = true;

$Type = 'Charges';
$Section = "compCharges";
$LignesCharges = compChargesProd::getTab($Type,$MoisVoulu,false,false,false,$opt);


$Type = 'Produits';
$Section = "compProd";
$LignesProduits = compChargesProd::getTab($Type,$MoisVoulu,false,false,false,$opt);


include '../compChargesBack/MListeCharges.php';

?>
