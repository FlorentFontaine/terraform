<?php 
session_start();
include_once '../ctrl/ctrl.php';
require_once('../dbClasses/AccesDonnees.php');
require_once('../htmlClasses/table.php');

require_once('../synthese/synthese.class.php');


$MoisVoulu = $_SESSION["MoisHisto"];

if($mensuel)
    $opt['mensuel'] = 1;

if($cumul)
    $opt['cumul'] = 1;

if($cluster)
    $opt['cluster'] = 1;



$Type = 'Produits';
$Section = "compProd";
$LignesProduits = synthese::getTab($Type,$MoisVoulu,false,false,false,$opt);
$Type = 'Charges';
$Section = "compCharges";
$LignesCharges = synthese::getTab($Type,$MoisVoulu,false,false,false,$opt);



include '../synthese/MListe.php';

?>
