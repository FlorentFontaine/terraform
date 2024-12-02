<?php

use Classes\Alert;
use Helpers\StringHelper;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../ctrl/ctrl.php';
require_once __DIR__ . '/../dbClasses/station.php';

if (!isset($STA_NUM) || !$STA_NUM) {
    if (!isset($_SESSION["station_STA_NUM"]) || !$_SESSION["station_STA_NUM"]) {
        header("Location: ../StationBack/Liste.php");
        exit();
    }

    $STA_NUM = $_SESSION["station_STA_NUM"];
}

if (isset($DOS_NUM) && $DOS_NUM && !station::GetExercice($STA_NUM, $DOS_NUM)) {
    //sécurité, il faut que le dossier demandé existe, car sinon il va demander la création d'un nouvel exo
    //lors de $LnDernEx = station::GetExercice($STA_NUM,$DOS_NUM)
    $DOS_NUM = false;
}

if ($MaStation = station::GetStation($STA_NUM)) {
    $_SESSION["station_STA_NUM"] = $STA_NUM ?? false;
    $_SESSION["station_DOS_NUM"] = $DOS_NUM ?? false;
    $_SESSION["station_STA_DERNDOS"] = false;
    $_SESSION["station_LIE_NUM"] = false;
    $_SESSION["inLIE_NUM"] = false;
    $_SESSION["MoisHisto"] = false;
    $_SESSION["station_STA_MAINTENANCE"] = false;

    if ($LnDernEx = station::GetExercice($STA_NUM, $DOS_NUM)) {
        foreach ($MaStation as $cle => $valeur) {
            $_SESSION["station_$cle"] = $valeur;
        }

        foreach ($LnDernEx as $cle => $valeur) {
            $_SESSION["station_$cle"] = $valeur;
        }

        $_SESSION["station_CAB_NOM"] = station::getCabinet($STA_NUM);

        $MonDosPrec = station::GetExercice($STA_NUM, $_SESSION["station_DOS_NUMPREC"]);

        foreach ($MonDosPrec as $clePrec => $valeurPrec) {
            $_SESSION["station_" . $clePrec . "_PREC"] = $valeurPrec;
        }

        if ($_SESSION["station_STA_DERNBAL"] == "0000-00-00") {
            $_SESSION["station_STA_DERNBAL"] = date("Y-m-00", strtotime($_SESSION["station_DOS_DEBEX"]));
        }

        $MesDateImport = dbAcces::getBalImportM1($_SESSION["station_DOS_NUM"]);

        if (empty($MesDateImport)) {
            $MesDateImport[] = array("BALI_MOIS" => date("Y-m-00", strtotime($_SESSION["station_DOS_DEBEX"])));

            $_SESSION["station_PREM_BAL"] = true;
            $_SESSION["station_BALI_NBJOUR"] = (int)date("t", strtotime($_SESSION["station_DOS_DEBEX"])) - (int)date("d", strtotime($_SESSION["station_DOS_DEBEX"])) + 1;
        } else {
            $_SESSION["station_PREM_BAL"] = false;
            $_SESSION["station_BALI_NBJOUR"] = $MesDateImport[0]["BALI_NBJOUR"];
        }

        $_SESSION["MoisHisto"] = $_SESSION["MoisHisto"] ?: $MesDateImport[0]["BALI_MOIS"];

        // On détermine la date de la dernière balance validée par MAJ Base
        $MesDateImport = array_reverse($MesDateImport);

        foreach ($MesDateImport as $UneDate) {
            if ($UneDate["BALI_DATE_MAJBASE"] == 0 && ($_SESSION["loged"] == "station" || $_SESSION["loged"] == "agip")) {
                break;
            }

            $_SESSION["MoisHisto"] = $UneDate["BALI_MOIS"];

            if ($UneDate["BALI_DATE_MAJBASE"] == 0) {
                break;
            }
        }

        $MesDateImport = array_reverse($MesDateImport);

        //comptage des anomalies
        include_once __DIR__ . '/../Anomalie/Anomalie.class.php';
        $_SESSION["NbAno"] = Anomalie::CompterAnomalies();


        //Comptage du nombre de mois d'exploitation
        $NbMois = dbAcces::getDateMAJBase($_SESSION["station_DOS_NUM"]);
        $_SESSION['NbMois'] = count($NbMois);

        //verif si tableau clef créé
        include_once __DIR__ . '/../RenseignementBack/Renseignement.class.php';

        //Récupération du numéro d'exercice du dernier exercice en cours
        $LnDernDos = station::GetExercice($STA_NUM);
        $_SESSION["station_STA_DERNDOS"] = $LnDernDos["DOS_NUM"];

        if ($_SESSION["station_DOS_NUMPREC"] > 0 && !dbAcces::getSaison($_SESSION["MoisHisto"]))
        {

            //si on a un dossier precedent on recopie le prev et les clefs du dossier precedent

            $MonDossierPrec = dbAcces::getDossier(NULL, $_SESSION["station_DOS_NUMPREC"]);
            $MonDossierPrec = $MonDossierPrec[0];
            $DosPrecFinEx = date("Y-m-00", strtotime(str_replace("-00", "-01", $MonDossierPrec["DOS_FINEX"])));

            $PlageBase = NULL;
            $MesSaison = dbAcces::getSaison(NULL, NULL, $MesSum, $MesSaison["SAI_NUMSAISON"], $PlageBase, $_SESSION["station_STA_NUM"]);


            $SaisonDebut = $_SESSION["station_DOS_DEBEX"];
            $SaisonFin = StringHelper::DatePlus($SaisonDebut, array("moisplus" => 12));//$_SESSION["station_DOS_NBMOIS"]
            $TabSaison = array();

            while (strtotime($SaisonDebut) < strtotime($SaisonFin)) {
                $SaisonDebutF = date("Y-m-00", strtotime($SaisonDebut));
                $SaisonMois = StringHelper::DatePlus($SaisonDebut, array("moisplus" => -12, "dateformat" => "Y-m-00"));

                $SaisonDebutFN1 = StringHelper::DatePlus($SaisonDebut, array("moisplus" => -1, "dateformat" => "Y-m-00"));

                $TabSaison[$SaisonDebutF]["SAI_DATE"] = $SaisonDebutF;

                $TabSaison[$SaisonDebutF]["SAI_CLE1"] = $MesSaison[$SaisonMois]["SAI_CLE1"];
                $TabSaison[$SaisonDebutF]["SAI_CLE2"] = $MesSaison[$SaisonMois]["SAI_CLE2"];
                $TabSaison[$SaisonDebutF]["SAI_CLE3"] = $MesSaison[$SaisonMois]["SAI_CLE3"];

                $SaisonDebut = StringHelper::DatePlus($SaisonDebut, array("moisplus" => 1));
            }

            if ($_SESSION["station_DOS_NBMOIS"] > 12) {
                while (strtotime($SaisonDebut) < strtotime($_SESSION["station_DOS_FINEX"])) {
                    $SaisonDebutF = date("Y-m-00", strtotime($SaisonDebut));
                    $SaisonDebutFN1 = StringHelper::DatePlus($SaisonDebutF, array("anneesplus" => -1, "dateformat" => "Y-m-00"));

                    $TabSaison[$SaisonDebutF]["SAI_DATE"] = $SaisonDebutF;
                    $TabSaison[$SaisonDebutF]["SAI_CLE1"] = $TabSaison[$SaisonDebutFN1]["SAI_CLE1"];
                    $TabSaison[$SaisonDebutF]["SAI_CLE2"] = $TabSaison[$SaisonDebutFN1]["SAI_CLE2"];
                    $TabSaison[$SaisonDebutF]["SAI_CLE3"] = $TabSaison[$SaisonDebutFN1]["SAI_CLE3"];

                    $SaisonDebut = StringHelper::DatePlus($SaisonDebut, array("moisplus" => 1));
                }
            }

            if ($TabSaison) {
                dbAcces::setSaison($TabSaison, $_SESSION["station_DOS_NUM"], 1);
            }


            $DosNouvDebEx = date("Y-m-00", strtotime(str_replace("-00", "-01", $_SESSION["station_DOS_DEBEX"])));

            include_once('../PrevBack/prev.class.php');

            //CRP à cheval au dbt de l'exercice
            $param = array(
                "tabCriteres" => array(
                    "STA_NUM" => $_SESSION["station_STA_NUM"],
                    "CRP_DBT" => $_SESSION["station_DOS_DEBEX"],
                    "CRP_FIN" => $_SESSION["station_DOS_DEBEX"]
                ),
                "tabOP" => array(
                    "CRP_DBT" => "<=",
                    "CRP_FIN" => ">="
                ),
            );
            $CRP_temp[] = db_CRP::select_CRP($param);

            //CRP à cheval à la fin de l'exercice
            $param = array(
                "tabCriteres" => array(
                    "STA_NUM" => $_SESSION["station_STA_NUM"],
                    "CRP_DBT" => $_SESSION["station_DOS_FINEX"],
                    "CRP_FIN" => $_SESSION["station_DOS_FINEX"]
                ),
                "tabOP" => array(
                    "CRP_DBT" => "<=",
                    "CRP_FIN" => ">="
                ),
            );
            $CRP_temp[] = db_CRP::select_CRP($param);

            //CRP inclus dans l'exercice
            $param = array(
                "tabCriteres" => array(
                    "STA_NUM" => $_SESSION["station_STA_NUM"],
                    "CRP_DBT" => $_SESSION["station_DOS_DEBEX"],
                    "CRP_FIN" => $_SESSION["station_DOS_FINEX"]
                ),
                "tabOP" => array(
                    "CRP_DBT" => ">=",
                    "CRP_FIN" => "<="
                ),
            );
            $CRP_temp[] = db_CRP::select_CRP($param);

            //CRP englobant l'exercice
            $param = array(
                "tabCriteres" => array(
                    "STA_NUM" => $_SESSION["station_STA_NUM"],
                    "CRP_DBT" => $_SESSION["station_DOS_DEBEX"],
                    "CRP_FIN" => $_SESSION["station_DOS_FINEX"]
                ),
                "tabOP" => array(
                    "CRP_DBT" => "<=",
                    "CRP_FIN" => ">="
                ),
            );
            $CRP_temp[] = db_CRP::select_CRP($param);

            foreach ($CRP_temp as $array_crp) {
                if ($array_crp) {
                    foreach ($array_crp as $value) {
                        $CRP[$value["CRP_NUM"]] = $value;
                    }
                }
            }

            foreach ($CRP as $CRP_NUM => $val) {
                $param = array(
                    "CRP_NUM" => $CRP_NUM,
                    "STA_NUM" => $_SESSION["station_STA_NUM"],
                    "DateDbt" => date('Y-m-00', strtotime($_SESSION["station_DOS_DEBEX"])),
                    "DateFin" => date('Y-m-00', strtotime($_SESSION["station_DOS_FINEX"]))
                );
                Previsionnel::RefactAllPrev($param);
            }
        }

        if (!dbAcces::getSaison()) {
            $SaisonDebut = $_SESSION["station_DOS_DEBEX"];
            $SaisonFin = StringHelper::DatePlus($SaisonDebut, array("moisplus" => 12));//

            while (strtotime($SaisonDebut) < strtotime($SaisonFin)) {
                $SaisonDebutF = date("Y-m-00", strtotime($SaisonDebut));
                $SaisonDebutFN1 = StringHelper::DatePlus($SaisonDebut, array("moisplus" => -1, "dateformat" => "Y-m-00"));

                $TabSaison[$SaisonDebutF]["SAI_DATE"] = $SaisonDebutF;
                $TabSaison[$SaisonDebutF]["SAI_CLE1"] = 1;
                $TabSaison[$SaisonDebutF]["SAI_CLE2"] = 1;
                $TabSaison[$SaisonDebutF]["SAI_CLE3"] = 1;
                $TabSaison[$SaisonDebutF]["SAI_NUMSAISON"] = 1;
                $SaisonDebut = StringHelper::DatePlus($SaisonDebut, array("moisplus" => 1));
            }

            if ($_SESSION["station_DOS_NBMOIS"] > 12) {
                while (strtotime($SaisonDebut) < strtotime($_SESSION["station_DOS_FINEX"])) {
                    $SaisonDebutF = date("Y-m-00", strtotime($SaisonDebut));
                    $SaisonDebutFN1 = StringHelper::DatePlus($SaisonDebut, array("moisplus" => -1, "dateformat" => "Y-m-00"));

                    $TabSaison[$SaisonDebutF]["SAI_DATE"] = $SaisonDebutF;
                    $TabSaison[$SaisonDebutF]["SAI_CLE1"] = 1;
                    $TabSaison[$SaisonDebutF]["SAI_CLE2"] = 1;
                    $TabSaison[$SaisonDebutF]["SAI_CLE3"] = 1;
                    $TabSaison[$SaisonDebutF]["SAI_NUMSAISON"] = 1;
                    $SaisonDebut = StringHelper::DatePlus($SaisonDebut, array("moisplus" => 1));
                }
            }

            dbAcces::setSaison($TabSaison, $_SESSION["station_DOS_NUM"], 1);
        }

        //maj des derniere connection pour CDS ou station
        if ($_SESSION["User"]->Type == "Secteur") {
            station::Update("STA_DERNCONNECTION_CDS", date("Y-m-d H:i:00"), $STA_NUM);
        }
        elseif ($_SESSION["User"]->Type == "station") {
            station::Update("STA_DERNCONNECTION", date("Y-m-d H:i:00"), $STA_NUM);
        }

        $BalanceImp = dbAcces::getBalImportM1($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"]);
        $BALI_TYPE = $BalanceImp[0]["BALI_TYPE"];
        $_SESSION["station_BALI_TYPE"] = $BALI_TYPE;

        if ($BALI_TYPE) {
            switch (trim($BALI_TYPE)) {
                case "BS":
                    $_SESSION["station_BALI_TYPE_exp"] = "Balance Standard";
                    break;
                case "SI":
                    $_SESSION["station_BALI_TYPE_exp"] = "Situation Interm&eacute;diaire";
                    break;
                case "PB":
                    $_SESSION["station_BALI_TYPE_exp"] = "Pr&eacute;-Bilan";
                    break;
                case "BI":
                    $_SESSION["station_BALI_TYPE_exp"] = "Bilan";
                    break;
                default:
                    $_SESSION["station_BALI_TYPE_exp"] = "";
                    break;
            }
        }

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

        if (!$redirect) {
            header("Location: ../GardeBack/Garde.php");
        } else {
            header("Location: $redirect");
        }
    } else {
        if (!$_SESSION["agip_AG_NUM"]) {
            header("Location: ../StationBack/formulaire2.php?STA_NUM=$STA_NUM");
        } else {
            header("Location: ../StationBack/Liste.php");
        }
    }

    exit();
}

header("Location: ../StationBack/Liste.php");
exit();
