<?php 
session_start();
include_once '../ctrl/ctrl.php';
require_once('../dbClasses/AccesDonnees.php');
require_once('../htmlClasses/table.php');

require_once('../compChargesBack/compCharges.class.php');


$MoisVoulu = $_SESSION["MoisHisto"];


$opt["projection"] = true;

$Type = 'Produits';
$Section = "compProd";
$LignesProduits = compChargesProd::getTab($Type,$MoisVoulu,false,false,false,$opt);
$Type = 'Charges';
$Section = "compCharges";
$LignesCharges = compChargesProd::getTab($Type,$MoisVoulu,false,false,false,$opt);

include '../compChargesBack/MListeProjection.php';

?>
