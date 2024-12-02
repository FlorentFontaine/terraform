<?php

 use Classes\Debug\Debugbar\Debug;
use Facades\Modules\Module;

require_once __DIR__ . '/../../Init/bootstrap.php';
Debug::init();
Module::loadModules();

require_once __DIR__ . "/../dbClasses/AccesDonnees.php";
require_once __DIR__ . "/../auth/classes/Auth.class.php";
require_once __DIR__ . "/../Anomalie/Anomalie.class.php";
require_once __DIR__ . "/../PrevBack/prev.class.php";
require_once __DIR__ . "/../RenseignementBack/Renseignement.class.php";
require_once __DIR__ . "/../MargeBack/marge.class.php";
require_once __DIR__ . "/../LieuBack/lieu.class.php";
require_once __DIR__ . "/../dbClasses/User.php";
require_once __DIR__ . "/../dbClasses/station.php";

// Includes
include_once "../include/EnteteTab.class.php";

// Instance de dbClasses/User
global $User;
global $Arrondir;
global $colorSituation;
global $Imprimer;

if (!isset($_GET["reloadpage"]) || !$_GET["reloadpage"]) {
    $_SESSION["ioreport_DernPost"] = $_POST;
} else {
    $_POST = $_SESSION["ioreport_DernPost"];
}

extract($_POST);
extract($_GET);

// Vérification de la validité du token
$Auth = new Auth;
$Auth->checkSession();

if ($_SESSION["loged"] && $_SESSION["loged"] != "") {
    include_once __DIR__ . '/../dbClasses/init_user.php';
}

// Actions diverses
$colorSituation = array(
    "BS" => array("color" => "green", "desc" => "Balance standard", "num" => 1),
    "BI" => array("color" => "blue", "desc" => "Situation interm&eacute;diaire", "num" => 2),
    "BP" => array("color" => "yellow", "desc" => "Pr&eacute;-Bilan", "num" => 3),
    "BD" => array("color" => "red", "desc" => "Bilan", "num" => 4),
    "" => array("color" => "", "desc" => "", "num" => 5)
);

if (isset($pdfMe) && $pdfMe) {
    ob_start();
    $EntetePiedFalse = true;
    require_once __DIR__ . '/../ImprimBack/imprim.class.php';
}

if (isset($_GET["xls"]) && $_GET["xls"]) {
    header("Content-type: application/msexcel");
    header("Content-disposition: attachment; filename=myreport_export.xls");
    if (isset($_SESSION["station_STA_NUM"]) && $_SESSION["station_STA_NUM"]) {
        require_once '../ImprimBack/imprim.class.php';
        Impression::TeteStation(false, $FlagCRRXls);
    }
    ?>
    <link rel="stylesheet" type="text/css" href="../style.css" media="screen">
    <?php
}

if (
    (
        (isset($_GET["LIEU_NUM"]) && $_GET["LIEU_NUM"] != $_SESSION["station_LIE_NUM"])
        || (isset($_POST["LIEU_NUM"]) && $_POST["LIEU_NUM"] != $_SESSION["station_LIE_NUM"])
    ) && $_SESSION["station_STA_NUM"] && $notselect && ($_POST["LIEU_NUM"] || $_GET["LIEU_NUM"])
) {
    $_SESSION["station_STA_NUM"] = false;
    $_SESSION["station_DOS_NUM"] = false;
    $_SESSION["inLIE_NUM"] = false;
    $_SESSION["MoisHisto"] = false;
    header("Location:../StationBack/Liste.php?notselect=1");
    exit();
}

if (
    ((!isset($_SESSION["station_DOS_NUM"]) || !$_SESSION["station_DOS_NUM"])
        || !$_SESSION["station_STA_NUM"])
    && (!isset($notselect) || !$notselect)
    && !$_SESSION["inLIE_NUM"]
) {
    header("Location:../StationBack/Liste.php?notselect=1");
    exit();
}

// Reset des anomalies, le recalcule a lieu à la fin du fichier si la MAJ Base n'est pas faite
$_SESSION["Ano"] = [];
$_SESSION["NbAno"] = 0;

if (isset($notselect) && $notselect) {
    $_SESSION["station_STA_NUM"] = false;
    $_SESSION["station_DOS_NUM"] = false;
    $_SESSION["inLIE_NUM"] = false;
    $_SESSION["MoisHisto"] = false;
}

// Gestion des actions en optall
$_SESSION["ioreport_MAJB"] = false;
if (isset($optall) && $optall) {
    switch ($optall) {
        case "majb":
            $_SESSION["ioreport_MAJB"] = true;
            break;

        case "refactprev":
            if ($_SESSION["User"]->getAut("option", "effaceprev")) {
                break;
            }

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

            $Enregistrement = true;

            Anomalie::CompterAnomalies();
            break;

        case "copieTM":
            //copie des tx de marge du prev dans les RG
            if ($_SESSION["User"]->getAut("option", "copiebal")) {
                break;
            }

            ListeRenseignement::CopieTxPrev($_SESSION["MoisHisto"]);
            //mise à jour ecart marge + ecart marge prec
            Marge::getTab($_SESSION["MoisHisto"], array("updEcartMarge" => true));
            Anomalie::CompterAnomalies();

            header("Location: ../RenseignementBack/Liste.php");
            exit();

        case "copieSTI":
            if ($_SESSION["User"]->getAut("option", "copiebal")) {
                break;
            }

            ListeRenseignement::CopieBal($_SESSION["MoisHisto"]);
            //mise &eacute; jour ecart marge + ecart marge prec
            Marge::getTab($_SESSION["MoisHisto"], array("updEcartMarge" => true));
            //mise à zero de la date maj base pour anomalie
            dbAcces::setMAJBaseMois($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"], 0);

            header("Location: ../RenseignementBack/Liste.php");
            exit();

        case "copieSTF":
            if ($_SESSION["User"]->getAut("option", "copiebal")) {
                break;
            }

            ListeRenseignement::CopieBal(station::GetLastBal($_SESSION["station_DOS_NUM"]), "StockFinal");
            //mise à jour ecart marge + ecart marge prec
            Marge::getTab($_SESSION["MoisHisto"], array("updEcartMarge" => true));
            //mise à zero de la date maj base pour anomalie
            dbAcces::setMAJBaseMois($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"], 0);

            header("Location: ../RenseignementBack/Liste.php");
            exit();

        case "delprevprod":
            if ($_SESSION["User"]->getAut("option", "effaceprev")) {
                break;
            }

            $MaPeriodeDel = $_SESSION["MoisHisto"];
            Previsionnel::EffacePrev($MaPeriodeDel, "Produits");
            Previsionnel::EffacePrev($MaPeriodeDel, "Charges");
            Anomalie::CompterAnomalies();

            header("Location: ../PrevBack/Liste.php?Produits=1");
            exit();

        case "suppbal":
            if ($_SESSION["User"]->getAut("option", "suppbal")) {
                break;
            }

            dbAcces::SuppBalance($_SESSION["station_STA_NUM"], $_SESSION["station_DOS_NUM"], $_SESSION['MoisVoulu']);

            header(sprintf("Location: ../StationBack/open.php?STA_NUM=%s", $_SESSION["station_STA_NUM"]));
            exit();
    }
}


if (!$_SESSION["MoisHisto"] && $_SESSION["station_DOS_NUM"]) {
    if ($_SESSION["agip_AG_NUM"]) {
        if (!$DernDateMAJ = dbAcces::getDateMAJBase($_SESSION["station_DOS_NUM"], null, 1, true)) {
            die("dossier non commenc&eacute; !!!");
        } else {
            foreach ($DernDateMAJ as $MoisDernMiseAJour => $LnDernEx) {
                break;
            }

            $_SESSION["MoisHisto"] = $MoisDernMiseAJour;
        }
    } else {
        if (!$_SESSION["MoisHisto"] = station::GetLastBal($_SESSION["station_DOS_NUM"])) {
            $_SESSION["MoisHisto"] = date("Y-m-00", strtotime($_SESSION["station_DOS_DEBEX"]));
        }
    }
}

if (isset($_SESSION["agip_AG_NUM"]) && $_SESSION["agip_AG_NUM"]) {
    $Arrondir = true;
}

$MesDateImport = array();
if (isset($_SESSION["station_DOS_NUM"]) && $_SESSION["station_DOS_NUM"]) {
    $MesDateImport = dbAcces::getBalImportM1($_SESSION["station_DOS_NUM"]);
}

foreach ($MesDateImport as $MoisDernMiseAJour => $UneDate) {
    $_SESSION["ioreport_maxMoisHisto"] = $MoisDernMiseAJour;
    break;
}

// Mise à jour de la session au changement de période
if (isset($_POST["moishisto"]) && $_POST["moishisto"]) {
    $_SESSION["MoisHisto"] = $_POST["moishisto"];

    $balanceImportData = array_filter($MesDateImport, function($subArray) {
        return isset($subArray['BALI_MOIS']) && $subArray['BALI_MOIS'] === $_SESSION["MoisHisto"];
    });
    $balanceImportData = reset($balanceImportData);

    $_SESSION["station_BALI_TYPE"] = $balanceImportData["BALI_TYPE"];

    //Vérification si les modifications sont autorisées
    $_SESSION["ModifOK"] = false;
    if ($_SESSION["station_STA_DERNDOS"] == $_SESSION["station_DOS_NUM"]) {
        if ($_SESSION["MoisHisto"] == $_SESSION["station_STA_DERNBAL"]) {
            $_SESSION["ModifOK"] = true;
        }
    } else {
        if ($_SESSION["MoisHisto"] == date("Y-m-00", strtotime($_SESSION["station_DOS_FINEX"]))) {
            $_SESSION["ModifOK"] = true;
        }
    }
}


$balanceImportData = array_filter($MesDateImport, function($subArray) {
    return isset($subArray['BALI_MOIS']) && $subArray['BALI_MOIS'] <= $_SESSION["MoisHisto"];
});
$_SESSION['NbMois'] = count($balanceImportData);

// Recalcule des anomalies si on est dans un dossier sans la MajBase (requis en cas de changement de période)
if (isset($_SESSION["station_DOS_NUM"]) && $_SESSION["station_DOS_NUM"] && Anomalie::Ano_MAJBase()) {
    Anomalie::CompterAnomalies();
}
