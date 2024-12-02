<?php
use Helpers\StringHelper;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../ctrl/ctrl.php';
require_once __DIR__ . '/../dbClasses/AccesDonnees.php';
require_once __DIR__ . '/../RenseignementBack/Renseignement.class.php';
require_once __DIR__ . '/../GardeBack/gardeBack.class.php';

$Section = "Garde";
$PrevAgip = isset($PrevAgip) && $PrevAgip ? $PrevAgip : null;

//On récupère le fichier de commentaire et on force le téléchargement. Pour le dossier et la date en cours
if (isset($_GET["download"]) && $_GET["download"] == '1') {
    if ($_GET["DOS_NUM"]) {
        gardeBack::downLoadCom($_GET["DOS_NUM"], $_GET["moisVoulu"]);
    } else {
        gardeBack::downLoadCom($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"]);
    }
} elseif (isset($_GET["delFileCom"]) && $_GET["delFileCom"]) {
    //On supprimer le fichier de commentaire
    gardeBack::delFileCom($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"]);
}

if (isset($_POST["validCom"]) && $_POST["validCom"]) {
    $Enregistrement = dbAcces::setComImport($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"], $_POST["BALI_COM"]);
}

if (isset($_POST['fileComSubmit']) && $_POST['fileComSubmit']) {
    gardeBack::saveFileCom($_FILES["fileCom"], $_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"]);
}

//Calcul période N1
$NbMoisEcart = StringHelper::GetNbMoisEcart(date("Ym", strtotime($_SESSION["station_DOS_DEBEX"])), date("Ym", strtotime(str_replace('-00', '-01', $_SESSION["MoisHisto"]))));
$MoisFinN1 = $MoisDbtN1 = null;

if ($_SESSION["station_DOS_PREMDATECP"] > 0 && $_SESSION["station_DOS_PREMDATECP"] != "0000-00-00") {
    $MoisFinN1 = StringHelper::DatePlus(str_replace("-00", "-01", $_SESSION["station_DOS_PREMDATECP"]), array("moisplus" => $NbMoisEcart, "dateformat" => "Y-m-00"));
    $MoisDbtN1 = $_SESSION["station_DOS_PREMDATECP"];
} elseif ($_SESSION["agip_AG_NUM"]) {
    $MoisFinN1 = StringHelper::DatePlus($_SESSION["MoisHisto"], array("moisplus" => -12, "dateformat" => "Y-m-00"));
    $MoisDbtN1 = StringHelper::DatePlus($_SESSION["MoisHisto"], array("moisplus" => -$NbMoisEcart, "dateformat" => "Y-m-00"));
}

// -----------
//Total Charges
$TotalCharges["real"] = dbAcces::getTotalCharges($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"]);
if ($MoisDbtN1 && $MoisFinN1) {
    $TotalCharges["an1"] = dbAcces::getTotalCharges($_SESSION["station_DOS_NUMPREC"], $MoisDbtN1, true, $MoisFinN1);
}
$TotalCharges["prev"] = dbAcces::getPrev($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"], "Charges", true, null, null, null, null, false, true, null, null, true);

// -----------
//Total Produits
$TotalProd["real"] = dbAcces::getTotalProduits($_SESSION["station_DOS_NUM"], $_SESSION["station_STA_NUM"], $_SESSION["MoisHisto"]);
if ($MoisDbtN1 && $MoisFinN1) {
    $TotalProd["an1"] = dbAcces::getTotalProduits($_SESSION["station_DOS_NUMPREC"], $_SESSION["station_STA_NUM"], $MoisDbtN1, true, $MoisFinN1);
}
$TotalProd["prev"] = dbAcces::getPrev($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"], "Produits", true, null, null, null, null, true, false, null, null, true);

// -----------
//Masse salariale (poste synthese 13)
$MasseSal = dbAcces::getResultatsPoste_synthese($_SESSION["MoisHisto"], true, true, true, true, 13, null, null, null, null, null, $Contenu, false);

// -----------
//Grivèlerie
$Grivelerie = dbAcces::getResultatsPoste($_SESSION["MoisHisto"], true, true, true, true, 572, null, null, null, null, null, $Contenu, false);

// -----------
//Résultat
$Resultat = dbAcces::getResultatDossier_sauvegarde(array("BALI_MOIS" => $_SESSION["MoisHisto"], "cumul" => true));
$Resultat["BALI_RESN1"] = dbAcces::getResultatDossier($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"], true, true);

// -----------
//Agios
//(6611000 Intérêts débiteurs et agios => codeCompte 9769)
$Agios = dbAcces::getResultatsCompte($_SESSION["MoisHisto"], false, false, 9769);

// -----------
//Solde de caisse
//(5310000 Caisse => codeCompte 9315)
$SoldeCaisse = dbAcces::getResultatsCompte($_SESSION["MoisHisto"], false, false, 9315);

// -----------
//Commentaires
$BALI = dbAcces::getBalImportM1($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"]);

// -----------
//Date d'inventaire
$TabRens = dbAcces::getRgDivers($_SESSION["MoisHisto"], $_SESSION["station_DOS_NUM"], "dateinv");
$DateInv = $TabRens["dateinv"]["RGD_DATE"];


// -----------
//chemin vers le fichier de commentaire
$pathFileCom = null;
if (!empty($BALI[0]["BALI_FILECOM"])) {
    $pathFileCom = $BALI[0]["BALI_FILECOM"];
}

$cabNum = isset($_SESSION["logedVar"]["CAB_NUM"]) && $_SESSION["logedVar"]["CAB_NUM"] ? $_SESSION["logedVar"]["CAB_NUM"] : null;

$authorization = gardeBack::cabAuthToDepFileCom($cabNum);

include_once '../GardeBack/MGarde.php';
