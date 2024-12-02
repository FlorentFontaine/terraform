<?php
session_start();

require_once __DIR__ . '/../ctrl/ctrl.php';
require_once __DIR__ . '/../dbClasses/station.php';
require_once __DIR__ . '/../dbClasses/AccesDonnees.php';
require_once __DIR__ . '/../htmlClasses/table.php';

if (!isset($Imprimer) || !$Imprimer) {
    include_once __DIR__ . '/../Anomalie/Anomalie.class.php';
}

global $Section;

$Section = "Rg";

require_once __DIR__ . '/../RenseignementBack/Renseignement.class.php';
require_once __DIR__ . '/../MargeBack/marge.class.php';


if (isset($_POST["valid"]) && $_POST["valid"]) {
    ListeRenseignement::setTaux($_POST, $_SESSION["MoisHisto"], true);
    ListeRenseignement::setSaison($_POST);

    if (!$_SESSION["User"]->getAut($Section, "prevprod")) {
        if ($_SESSION["station_STA_DERNBAL"] <= date("Y-m-00", strtotime($_SESSION["station_DOS_FINEX"]))) {
            $DateFin = date("Y-m-t", strtotime(str_replace("-00", "-01", $_SESSION["station_STA_DERNBAL"])));
        } else {
            $DateFin = $_SESSION["station_DOS_FINEX"];
        }

        //Mise à jour des prev avec les nouvelles saisonnalités
        $param = array(
            "tabCriteres" => array(
                "STA_NUM" => $_SESSION["station_STA_NUM"],
                "CRP_DBT" => $_SESSION["station_DOS_DEBEX"]
            ),
            "tabOP" => array(
                "CRP_DBT" => ">="
            ),
        );
        $CRP_dbt = db_CRP::select_CRP($param);

        $param = array(
            "tabCriteres" => array(
                "STA_NUM" => $_SESSION["station_STA_NUM"],
                "CRP_FIN" => $_SESSION["station_DOS_FINEX"]
            ),
            "tabOP" => array(
                "CRP_FIN" => "<="
            ),
        );
        $CRP_fin = db_CRP::select_CRP($param);

        $CRP_temp = array_merge($CRP_dbt, $CRP_fin);

        $CRP = [];
        foreach ($CRP_temp as $value) {
            $CRP[$value["CRP_NUM"]] = $value;
        }

        foreach ($CRP as $CRP_NUM => $val) {
            $param = array(
                "CRP_NUM" => $CRP_NUM,
                "STA_NUM" => $_SESSION["station_STA_NUM"],
            );

            Previsionnel::RefactAllPrev($param);
        }
    }

    ListeRenseignement::setCarb($_POST);
    ListeRenseignement::setTabDivers($_POST);

    // Mise à jour écart marge + écart marge précédent
    Marge::getTab($_SESSION["MoisHisto"], array("updEcartMarge" => true, "updStockTheo" => true));

    // Mise à zero de la date maj base pour anomalie
    dbAcces::setMAJBaseMois($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"], 0);
    Anomalie::CompterAnomalies();
}

$MoisVoulu = $_SESSION["MoisHisto"];

$opt = [];

if (isset($OubliMarge) && $OubliMarge) {
    $opt["oublimarge"] = true;
}

if (isset($AnoTauxSup100) && $AnoTauxSup100) {
    $opt["AnoTauxSup100"] = true;
}

if (isset($AnoStockInit) && $AnoStockInit) {
    $opt["AnoStockInit"] = true;
}

if (isset($AnoStockFinal) && $AnoStockFinal) {
    $opt["AnoStockFinal"] = true;
}

if (isset($AnoVariationStock) && $AnoVariationStock) {
    $opt["AnoVariationStock"] = true;
}


if (isset($AnoStockFinalZero) && $AnoStockFinalZero) {
    $opt["AnoStockFinalZero"] = true;
}


if (isset($PrevTxModifie) && $PrevTxModifie) {
    $opt["PrevTxModifie"] = true;
}

$MesLignesProd = ListeRenseignement::getTab($MoisVoulu, $opt, $Retour);
$MesLigneCarb = ListeRenseignement::getTabCarburant($MoisVoulu, $TotalCarb);
$MesLigneSaison = ListeRenseignement::getTabSaison($MoisVoulu, $MesSum);
$MesLigneDivers = ListeRenseignement::getTabDivers($MoisVoulu);

if (isset($OubliMarge) && $OubliMarge) {
    if ($Retour["oublimarge"]) {
        foreach ($Retour["oublimarge"] as $cle => $valeur) {
            break;
        }

        $UnMessage["titre"] = "Oubli % de marge";
        $UnMessage["message"] = "Il manque des poucentages de marge.";
        $UnMessage["icon"] = "Ext.MessageBox.ERROR";
        $UnMessage["fn"] = "function(){
            var Taux = document.getElementsByName('Taux[" . $cle . "]');
            Taux[0].focus();Taux[0].select();
        }";

        $MessageBox[] = $UnMessage;
    } else {
        foreach ($Retour["oublimarge"] as $cle => $valeur) {
            break;
        }

        $UnMessage["titre"] = "Oubli % de marge";

        if (!$CorrectOubliMarge) {
            $UnMessage["message"] = "Il ne manque aucun pourcentage.";
        } else {
            $UnMessage["message"] = "Anomalie sur les poucentages de marge corrig&eacute;e.";
        }

        $MessageBox[] = $UnMessage;
    }
}

if (isset($AnoTauxSup100) && $AnoTauxSup100) {
    if ($Retour["AnoTauxSup100"]) {

        foreach ($Retour["AnoTauxSup100"] as $cle => $valeur) {
            break;
        }

        $UnMessage["titre"] = "Taux de marge";
        $UnMessage["message"] = "Taux de marge supŽrieur &agrave; 100%.";
        $UnMessage["icon"] = "Ext.MessageBox.ERROR";
        $UnMessage["fn"] = "function(){
            var Taux = document.getElementsByName('Taux[" . $cle . "]');
            Taux[0].focus();Taux[0].select();
        }";
        $MessageBox[] = $UnMessage;
    } else {
        foreach ($Retour["AnoTauxSup100"] as $cle => $valeur) {
            break;
        }

        $UnMessage["titre"] = "Stocks finaux";

        if (!$CorrectAnoTauxSup100) {
            $UnMessage["message"] = "R.A.S Taux de marge supŽrieur &agrave; 100%.";
        } else {
            $UnMessage["message"] = "Anomalie sur les taux de marge supŽrieur &agrave; 100% corrig&eacute;e.";
        }

        $MessageBox[] = $UnMessage;
    }
}

if (isset($AnoStockInit) && $AnoStockInit) {
    if ($Retour["AnoStockInit"]) {
        $ClePrem = 0;
        foreach ($Retour["AnoStockInit"] as $cle => $valeur) {
            if (!$ClePrem)
                $ClePrem = $cle;
            break;
        }

        $UnMessage["titre"] = "Stocks initiaux";
        $UnMessage["message"] = "Stocks initiaux des renseignements ne sont pas &eacute;gaux ˆ ceux de la balance.";
        $UnMessage["icon"] = "Ext.MessageBox.ERROR";
        $UnMessage["fn"] = "function(){
            var StockInit = document.getElementsByName('StockInit[" . $ClePrem . "]');
            StockInit[0].focus();StockInit[0].select();
        }";
        $MessageBox[] = $UnMessage;
    } else {
        foreach ($Retour["AnoStockInit"] as $cle => $valeur)
            break;

        $UnMessage["titre"] = "Stocks initiaux";

        if (!$CorrectAnoStockInit)
            $UnMessage["message"] = "R.A.S sur les stocks initiaux.";
        else
            $UnMessage["message"] = "Anomalie sur les stocks initiaux corrig&eacute;e.";

        $MessageBox[] = $UnMessage;
    }
}

if (isset($AnoStockFinal) && $AnoStockFinal) {
    if ($Retour["AnoStockFinal"]) {

        foreach ($Retour["AnoStockFinal"] as $cle => $valeur)
            break;

        $UnMessage["titre"] = "Stocks finaux";
        $UnMessage["message"] = "Stocks finaux ne sont pas &eacute;gaux ˆ ceux de la balance.";
        $UnMessage["icon"] = "Ext.MessageBox.ERROR";
        $UnMessage["fn"] = "function(){
            var StockFinal = document.getElementsByName('StockFinal[" . $cle . "]');
            StockFinal[0].focus();StockFinal[0].select();
        }";
        $MessageBox[] = $UnMessage;
    } else {
        foreach ($Retour["AnoStockFinal"] as $cle => $valeur)
            break;

        $UnMessage["titre"] = "Stocks finaux";

        if (!$CorrectAnoStockInit)
            $UnMessage["message"] = "R.A.S sur les stocks finaux.";
        else
            $UnMessage["message"] = "Anomalie sur les stocks finaux corrig&eacute;e.";

        $MessageBox[] = $UnMessage;
    }
}

if (isset($AnoVariationStock) && $AnoVariationStock) {
    if ($Retour["AnoVariationStock"]) {

        foreach ($Retour["AnoVariationStock"] as $cle => $valeur)
            break;

        $UnMessage["titre"] = "Variation de stock";
        $UnMessage["message"] = "Stock initial - Stock final - Variation stock different de 0.";
        $UnMessage["icon"] = "Ext.MessageBox.ERROR";
        $UnMessage["fn"] = "function(){
            var StockFinal = document.getElementsByName('StockFinal[" . $cle . "]');
            StockFinal[0].focus();StockFinal[0].select();
        }";
        $MessageBox[] = $UnMessage;
    } else {
        foreach ($Retour["AnoStockFinal"] as $cle => $valeur)
            break;

        $UnMessage["titre"] = "Variation de stock";

        if (!$CorrectAnoStockInit)
            $UnMessage["message"] = "R.A.S sur la Variation de stock.";
        else
            $UnMessage["message"] = "Anomalie sur la Variation de stock corrig&eacute;e.";

        $MessageBox[] = $UnMessage;
    }
}


if (isset($AnoStockFinalZero) && $AnoStockFinalZero) {
    if ($Retour["AnoStockFinalZero"]) {

        foreach ($Retour["AnoStockFinalZero"] as $cle => $valeur)
            break;

        $UnMessage["titre"] = "Saisie des stocks finaux";
        $UnMessage["message"] = "Stocks finaux manquants.";
        $UnMessage["icon"] = "Ext.MessageBox.ERROR";
        $UnMessage["fn"] = "function(){
            var StockFinal = document.getElementsByName('StockFinal[" . $cle . "]');
            StockFinal[0].focus();StockFinal[0].select();
        }";
        $MessageBox[] = $UnMessage;
    } else {
        foreach ($Retour["AnoStockFinalZero"] as $cle => $valeur)
            break;

        $UnMessage["titre"] = "Stocks finaux";

        if (!$CorrectAnoStockInit)
            $UnMessage["message"] = "R.A.S sur la saisie des stocks finaux.";
        else
            $UnMessage["message"] = "Anomalie sur la saisie des stocks finaux corrig&eacute;e.";

        $MessageBox[] = $UnMessage;
    }
}


include '../RenseignementBack/MListe.php';
