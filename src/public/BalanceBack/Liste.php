<?php

use Helpers\StringHelper;
use Classes\DB\Database;

session_start();

include_once '../ctrl/ctrl.php';
require_once('../RenseignementBack/Renseignement.class.php');
require_once('../MargeBack/marge.class.php');
require_once('../dbClasses/AccesDonnees.php');
require_once('../htmlClasses/table.php');


$Section = "Balance";


if ($initSessionBal) {
    unset($_SESSION["Session_correctBal"]);

    //$TabBal = dbAcces::getResultatsCompte(station::GetLastBal($_SESSION["station_DOS_NUM"]));
    $TabBal = dbAcces::getResultatsCompte($_SESSION["MoisHisto"]);
    $MesComptes = dbAcces::getComptes($Where);

    foreach ($TabBal as $Tab) {
        $_SESSION["Session_correctBal"][$MesComptes[$Tab["codeCompte"]]["numero"]] = $Tab["BAL_CUMUL"];
    }

}

class ListeBalance
{


    static function echo_Select_TypeBalance($selected)
    {
        $MesType = array("BS" => "Standard", "BP" => "Pré-Bilan", "BD" => "Bilan");
        $MesTypeNiveau = array("BS" => 1, "BP" => 2, "BD" => 3);

        $disable1 = "";
        $disable2 = "";


        switch ($selected) {
            case "BD":
                $disable2 = " disabled='disabled' ";

            case "BP":
                $disable1 = " disabled='disabled' ";


        }

        echo "<select name='BALI_TYPE' style='width: 100px'> ";

        if ($selected)
            echo "<option value='$selected'>" . $MesType[$selected] . "</option>";

        echo "<option value='BS' $disable1>" . $MesType["BS"] . "</option>";

        echo "<option value='BP' $disable2>" . $MesType["BP"] . "</option>";

        echo "<option value='BD'>" . $MesType["BD"] . "</option>";

        echo "</select>";

    }


    static function getTabBalance($MoisActuel, $opt = false, &$return = false)
    {
        if ($opt["classe"]) {
            $MesClasses = $opt["classe"];

            if (!is_array($MesClasses)) {
                $MesClassesT = NULL;
                $MesClassesT[] = $MesClasses;
                $MesClasses = $MesClassesT;
            }

            if ($MesClasses[1]) {

                $MaclasseDbt = $MesClasses[0];
                $MaclasseFin = $MesClasses[1];
                $MesClasses = NULL;
                while ($MaclasseDbt <= $MaclasseFin) {

                    $MesClasses[] = $MaclasseDbt;
                    $MaclasseDbt++;
                }
            }

            foreach ($MesClasses as $UneClasse) {
                $MonTab[] = " LIKE '$UneClasse%' ";
            }

            $Where = array(
                "or" =>
                    array(
                        "numero" => $MonTab
                    )
            );
        }

        if ($opt["AnoSensErrone"]) {
            $MesNumErrone = explode("||#||", $_SESSION["Ano"]["AnoSens"]);
            $Where["and"] = array("code_compte" => " in ('" . implode("','", $MesNumErrone) . "')");
        }

        if ($opt["imp"]) {
            $Where["and"] = array("code_compte" => " in (select distinct codeCompte from balance where DOS_NUM = '" . $_SESSION["station_DOS_NUM"] . "' and BAL_MOIS = '$MoisActuel')");
        }


        $Tri = array(
            "numero" => "ASC"

        );
        $MesComptes = dbAcces::getComptes($Where, $Tri);
        $MesLignesTableau = NULL;

        foreach ($MesComptes as $codeCompte => $UneLigneDb) {
            $UneLigneTableau = NULL;

            $UneLigneTableau[] = array($UneLigneDb["numero"] => array("class" => "balcpt", "excelformat" => "text", "style" => "text-align: center;"));


            if (isset($opt["histo"]) && $opt["histo"])
                $UneLigneTableau[] = array(substr($UneLigneDb["libelle"], 0, 20) => "");
            else
                $UneLigneTableau[] = array($UneLigneDb["libelle"] => "");

            $MesLignesTableau[$codeCompte] = $UneLigneTableau;
        }

        if (!$MesLignesTableau && $opt["AnoSensErrone"]) {
            $return["PasAnoSens"] = true;
            return;
        }

        if ($opt["histo"]) {
            $return["MaLigneEntete"][] = array("Numéro" => array("width" => "100px", "class" => 'tdfixe'));
            $return["MaLigneEntete"][] = array("Libellé" => array("width" => "220px", "class" => 'tdfixe'));

            if ($opt["histo"] != "N1") {
                $DateDeb = date("Y-m-01", strtotime($_SESSION["station_DOS_DEBEX"]));
                $Datecourante = str_replace("-00", "-01", station::GetLastBal($_SESSION["station_DOS_NUM"]));
            } else {

                $MonDossierN1 = station::GetAllExercice($_SESSION["station_STA_NUM"], $_SESSION["station_DOS_NUMPREC"]);
                $DateDeb = date("Y-m-01", strtotime($MonDossierN1[0]["DOS_DEBEX"]));

                if (!$MonDossierN1[0]["DOS_FINEX"]) {
                    $MonDossierN1[0]["DOS_FINEX"] = StringHelper::DatePlus($DateDeb, array("moisplus" => -1));//1 mois inferieur pour ne pas passer ds la boucle
                }

                $Datecourante = date("Y-m-01", strtotime($MonDossierN1[0]["DOS_FINEX"]));
            }

            while (strtotime($Datecourante) >= strtotime($DateDeb)) {
                $UnMois = date("Y-m-00", strtotime($Datecourante));
                $return["MaLigneEntete"][] = array(StringHelper::MySql2DateFr($UnMois) => array("width" => "70px", "class" => 'tdfixe'));
                ListeBalance::get_ColResultatMois($UnMois, $MesLignesTableau, $MesComptes, $opt);
                $Datecourante = StringHelper::DatePlus($Datecourante, array("moisplus" => -1));
            }
        } else {

            ListeBalance::get_ColResultatMois($MoisActuel, $MesLignesTableau, $MesComptes, $opt);
            $MesResultats = dbAcces::getResultatsCompte($MoisActuel, false, true, NULL, NULL, NULL, NULL, NULL, NULL, NULL, true, false);

            foreach ($MesLignesTableau as $codeCompte => &$UneLigneDb) {
                $UneLigneDb[] = array("" => array("class" => "col_sep"));//colone vide
                $UneLigneDb[] = array(StringHelper::NombreFr($MesResultats[$codeCompte . "UMois"]["BAL_CUMUL"], 2, false, true) => array("align" => "right"));
                $UneLigneDb[] = array(StringHelper::NombreFr($MesResultats[$codeCompte . "UMois"]["BAL_BALANCE"], 2, false, true) => array("align" => "right"));
            }
        }

        return $MesLignesTableau;
    }

    static function get_ColResultatMois($MoisActuel, &$MesLignesTableau, $MesComptes, $opt)
    {
        if (!$opt["histo"]) $M1 = true;
        else                $M1 = false;

        $MesResultats = dbAcces::getResultatsCompte($MoisActuel, $M1);//,true

        foreach ($MesLignesTableau as $codeCompte => &$UneLigneDb) {
            if (!isset($opt["histo"]) || !$opt["histo"]) {

                $UneLigneDb[] = array(StringHelper::NombreFr($MesResultats[$codeCompte . "UMoisMoins1"]["BAL_CUMUL"], 2, false, true) => array("align" => "right"));
            }

            if (isset($opt["correction"]) && $opt["correction"]) {

                if (array_key_exists($MesComptes[$codeCompte]["numero"], $opt["correctBal"]))
                    $Montant = StringHelper::Texte2Nombre($opt["correctBal"][$MesComptes[$codeCompte]["numero"]]);
                else
                    $Montant = $MesResultats[$codeCompte . "UMois"]["BAL_CUMUL"];

                $disabled = $_SESSION['ModifOK'] ? "" : " disabled='disabled' ";
                $MonTd = "<input type='text' $disabled class='gapiareamontant' name='correctBal[" . $MesComptes[$codeCompte]["numero"] . "]' value='" . StringHelper::NombreFr($Montant, 2, false, true) . "' />";

            } else {
                $MonTd = StringHelper::NombreFr($MesResultats[$codeCompte . "UMois"]["BAL_CUMUL"], 2, false, true);
            }

            if (!isset($opt["histo"]) || !$opt["histo"]) {
                $UneLigneDb[] = array($MonTd => array("align" => "right"));
            }

            $UneLigneDb[] = array(StringHelper::NombreFr($MesResultats[$codeCompte . "UMois"]["BAL_BALANCE"], 2, false, true) => array("align" => "right"));

        }
    }


    static function Import($dir, $Mois, $Format = NULL, &$Lines, &$Diff, &$Erreur, $Correction = false, $CumulWithPre = false, $TypeAviaXel = false)
    {
        if (file_exists($dir) || $Lines) {

            if (!$Lines) {
                include_once (!$Format) ? "../BalanceFormats/defaut.php" : "../BalanceFormats/$Format";
            }

            unlink($dir);

            if (!$Lines) {
                return false;
            }

            $MesComptes = dbAcces::getComptes();

            if (!$Correction) {
                $MesComptesEquivalence = dbAcces::getEquivalenceComptes($_SESSION["station_STA_NUM"]);
                $DeplaceUneFois = $_SESSION["ioreport_CompteDeplaceEquivalence"];

                foreach ($Lines as $Numero => $UneLigne) {

                    if ($MesComptesEquivalence[$Numero . "#||#" . $_SESSION["station_STA_NUM"]] && !in_array($Numero, $DeplaceUneFois) && !in_array($Numero, $AffectImp)) {
                        $MonNouveauNum = $MesComptesEquivalence[$Numero . "#||#" . $_SESSION["station_STA_NUM"]]["numero"];

                        if ($MonNouveauNum != $Numero) {
                            $Montant = $Lines[$MonNouveauNum]["cumulbal"];
                            $Montant += $Lines[$Numero]["cumulbal"];
                            $MaNouvLigne = NULL;
                            $MaNouvLigne["cumulbal"] = $Montant;
                            $DeplaceUneFois[] = $MonNouveauNum;
                            $Lines[$MonNouveauNum] = $MaNouvLigne;

                            unset($Lines[$Numero]);
                        }
                    } elseif ($MesComptesEquivalence[$Numero . "#||#0"] && !in_array($Numero, $DeplaceUneFois) && !in_array($Numero, $AffectImp)) {
                        $MonNouveauNum = $MesComptesEquivalence[$Numero . "#||#0"]["numero"];

                        if ($MonNouveauNum != $Numero) {
                            $Montant = $Lines[$MonNouveauNum]["cumulbal"];
                            $Montant += $UneLigne["cumulbal"];
                            $MaNouvLigne = NULL;
                            $MaNouvLigne["cumulbal"] = $Montant;
                            $DeplaceUneFois[] = $MonNouveauNum;
                            $Lines[$MonNouveauNum] = $MaNouvLigne;

                            unset($Lines[$Numero]);
                        }
                    }
                }

                $_SESSION["ioreport_CompteDeplaceEquivalence"] = $DeplaceUneFois;
            }

            $MesNumCpt = $MesCumuls = [];
            $Total = 0;

            // ----------------------------------------
            // Initialisation des valeurs à zéro
            // ----------------------------------------
            foreach ($MesComptes as $codeCompteBd => $Uncompte) {
                $MonNumeroCPT = $codeCompteBd;

                $MesNumCpt[$Uncompte["numero"]] = $MonNumeroCPT;
                $MesCumuls[$codeCompteBd] = 0;
            }


            // ----------------------------------------
            // Remplissage
            // ----------------------------------------
            foreach ($Lines as $Numero => $UneLigne) {

                $MonCodeCompteDb = $MesNumCpt[$Numero];

                $MesCumuls[$MonCodeCompteDb] += round(StringHelper::Texte2Nombre($UneLigne["cumulbal"]), 2);
                $MesCumuls[$MonCodeCompteDb] = round($MesCumuls[$MonCodeCompteDb], 2);

                if (!round($MesCumuls[$MonCodeCompteDb], 2)) {
                    unset($Lines[$Numero]);
                }
            }

            $Total = round(array_sum($MesCumuls), 2);
            $Total = round($Total, 2);


            // ----------------------------------------
            // Vérification de l'équilibre
            // ----------------------------------------
            if ($Total > 0 || $Total < 0) {
                $Erreur = "##EQUILIBRE##||$Total";
                return false;
            }


            // ----------------------------------------
            // Vérification si des comptes sont inconnus
            // ----------------------------------------
            if (!$Diff) {
                $Diff = array_diff_key($Lines, $MesNumCpt);

                if (count($Diff) > 0 && $Diff) {
                    $Erreur = "##DIFF##";
                    return false;
                }
            }


            if ($Mois > ($MaDernBal = station::GetLastBal($_SESSION["station_DOS_NUM"])) && !$_SESSION["station_PREM_BAL"]) {
                //duplique Marge + Prev
                $MoisPrec = StringHelper::DatePlus($Mois, array("moisplus" => -1, "dateformat" => "Y-m-00"));
                $ResultPrec = dbAcces::getTauxMarge($_SESSION["station_DOS_NUM"], $MoisPrec);


                foreach ($ResultPrec as $codeCpt => &$Ln) {

                    foreach ($Ln as $cle => &$Montant) {
                        if ($cle == "EcartMarge" && ($Montant || $Ln["StockFinalZero"] == 1 || $Ln["StockFinal"] > 0)) {
                            $Ln["EcartMargePrec"] = $Montant;
                        }

                        if (!$Ln["Taux"])
                            $Ln["Taux"] = "";
                    }

                    foreach ($Ln as $cle => &$Montant) {//on enleve le stock final
                        if ($cle == "StockFinal") {
                            $Montant = "";
                        }
                    }

                }


                ListeRenseignement::setTaux($ResultPrec, $Mois);


                //Mise é jour du prévisionnel
                $param = NULL;
                $param = array(
                    "tabCriteres" => array(
                        "STA_NUM" => $_SESSION["station_STA_NUM"],
                        "CRP_DBT" => str_replace("-00", "-01", $Mois),
                        "CRP_FIN" => date("Y-m-t", strtotime(str_replace("-00", "-01", $Mois)))
                    ),
                    "tabOP" => array(
                        "CRP_DBT" => "<=",
                        "CRP_FIN" => ">="
                    ),
                );
                $CRP = db_CRP::select_CRP($param);


                foreach ($CRP as $CRP_NUM => $val) {
                    $param = array(
                        "CRP_NUM" => $CRP_NUM,
                        "STA_NUM" => $_SESSION["station_STA_NUM"],
                        "DateDbt" => $Mois,
                        "DateFin" => $Mois
                    );
                    Previsionnel::RefactAllPrev($param);
                }


                //mise é jour ecart marge + ecart marge prec sur les ligne que l'on vient de dupliquer (Ln des renseignements)
                Marge::getTab($MoisPrec, array("updEcartMarge" => true, "updStockTheo" => true));


            } else {
                if (!$Correction) //si c'est un réimport de balance
                {
                    //duplique Marge + Prev
                    $MoisPrec = StringHelper::DatePlus($Mois, array("moisplus" => -1, "dateformat" => "Y-m-00"));
                    $ResultPrec = dbAcces::getTauxMarge($_SESSION["station_DOS_NUM"], $MoisPrec);

                    foreach ($ResultPrec as $codeCpt => &$Ln) {

                        foreach ($Ln as $cle => &$Montant) {
                            if ($cle == "EcartMarge" && ($Montant || $Ln["StockFinalZero"] == 1 || $Ln["StockFinal"] > 0)) {
                                $Ln["EcartMargePrec"] = $Montant;
                            }

                            if (!$Ln["Taux"])
                                $Ln["Taux"] = "";
                        }

                        foreach ($Ln as $cle => &$Montant) {//on enleve le stock final
                            if ($cle == "StockFinal") {
                                $Montant = "";
                            }
                        }

                    }

                    //mise é jour ecart marge + ecart marge prec sur les ligne que l'on vient de dupliquer (Ln des renseignements)
                    Marge::getTab($MoisPrec, array("updEcartMarge" => true, "updStockTheo" => true));

                }

                $DateCourante = $Mois;

                while ($DateCourante <= $MaDernBal) {
                    dbAcces::MAJBalanceMontantMois($_SESSION["station_DOS_NUM"], $DateCourante);
                    $DateCourante = StringHelper::DatePlus($DateCourante, array("moisplus" => 1));
                }
            }


            if ($CumulWithPre) {//si cumul avec balance précédente

                $MesResultatBalance = dbAcces::getResultatsCompte($Mois);

                foreach ($MesResultatBalance as $codeLn => $Resultat) {
                    $MesCumuls[str_replace("UMois", "", $codeLn)] += $Resultat["BAL_CUMUL"];
                }

            }


            dbAcces::InsertBalance($MesCumuls, $Mois, $Correction);
            Marge::getTab($Mois, array("updEcartMarge" => true, "updStockTheo" => true));

            if ($Correction) {
                include_once('../Anomalie/Anomalie.class.php');
                $_SESSION["NbAno"] = Anomalie::CompterAnomalies();
            }

            dbAcces::setMAJBaseMois($_SESSION["station_DOS_NUM"], $Mois, 0);//mise é zero de la date maj base pour anomalie


        }
    }


    static function SaveEquivalance($CptFichier, $CptAffect)
    {

        $sqlcpt = "select * from comptes where numero = '$CptAffect' ";
        $res = Database::query($sqlcpt);
        $MyCpt = Database::fetchArray($res);

        $NouvCpt_codeCompte = $MyCpt["code_compte"];

        $reqdel = "delete from liaisoncompte where CAB_NUM = '" . $_SESSION["User"]->Var["CAB_NUM"] . "' and STA_NUM = 0 and LICO_nouvcompte = '$CptFichier'";
        Database::query($reqdel);

        $reqinsert = 'INSERT INTO liaisoncompte (CAB_NUM,LICO_code_compte,LICO_nouvcompte) VALUE ("' . $_SESSION["User"]->Var["CAB_NUM"] . '","' . $NouvCpt_codeCompte . '","' . $CptFichier . '");';
        Database::query($reqinsert);

    }

}

$ErreurImport = false;


if ($_POST["validf"] && !$_SESSION["User"]->getAut($Section))//import de balance
{

    $BAL_MOISNouv1 = str_replace("-00", "-01", StringHelper::DateFr2MySql($BAL_MOISNouv));
    $BAL_MOISActuel = str_replace("-00", "-01", $_SESSION["MoisHisto"]);

    $MessErr = false;

    if (!StringHelper::isDateValide($BAL_MOISNouv, true)) {
        $MessErr = "Période non valide";
    } elseif (strtotime($BAL_MOISNouv1) < strtotime($BAL_MOISActuel)) {
        $MessErr = "La période ne peut pas être inférieur à cette date : '" . StringHelper::MySql2DateFr($BAL_MOISActuel) . "'";
    } elseif (strtotime($BAL_MOISNouv1) > strtotime($_SESSION["station_DOS_FINEX"])) {
        $MessErr = "La période ne peut pas étre supérieur à la date de fin d'exercice,\\nvous devez creer un nouvel exercice pour importer sur cette période";
    } elseif (strtotime($BAL_MOISNouv1) < strtotime(date("Y-m-00", strtotime($_SESSION["station_DOS_DEBEX"])))) {
        $MessErr = "La période ne peut pas étre inférieur à la date de début d'exercice";
    } elseif (strtotime($BAL_MOISNouv1) > strtotime($BAL_MOISActuel) && $_SESSION["NbAno"]) {
        $MessErr = "Import de la balance pour la période " . date("m/Y", strtotime($BAL_MOISNouv1)) . " impossible, il reste " . $_SESSION["NbAno"] . " anomalie(s) sur le mois.";
    }

    if (!empty($_FILES["fimport"]) && !$MessErr) {
        $BAL_MOISNouv1 = date("Y-m-00", strtotime($BAL_MOISNouv1));
        $BAL_MOISActuel = date("Y-m-00", strtotime($BAL_MOISActuel));
        if (move_uploaded_file($_FILES["fimport"]["tmp_name"], "/tmp/" . $BAL_MOISNouv1 . $_FILES["fimport"]["name"])) {
            $_SESSION["ioreport_CompteDeplaceEquivalence"] = NULL;

            $tabBalanceFormat = dbAcces::get_BalanceFormat();

            if ($_SESSION["station_STA_BAF_NUM"])
                $BAF_NOM = $tabBalanceFormat[$_SESSION["station_STA_BAF_NUM"]]["BAF_NOM"];
            else
                $BAF_NOM = $tabBalanceFormat[$_SESSION["station_BAF_NUM"]]["BAF_NOM"];

            ListeBalance::Import("/tmp/" . $BAL_MOISNouv1 . $_FILES["fimport"]["name"], $BAL_MOISNouv1, $BAF_NOM, $Lines, $Diff, $ErreurImport, false, $_POST["CumulWithPre"], $_POST["ExportAviaXel"]);


            if (!$ErreurImport) {
                $_GET["import"] = false;
                $_SESSION["LinesImport"] = false;
                $_SESSION["LinesDiff"] = false;
                $redirect = "../BalanceBack/Liste.php?initSessionBal=1";
                $DOS_NUM = $_SESSION["station_DOS_NUM"];
                include("../StationBack/open.php");
            } else {
                $_SESSION["LinesImport"] = $Lines;
                $_SESSION["LinesDiff"] = $Diff;

                if (stristr($ErreurImport, "##EQUILIBRE##||")) {
                    $Equilibrage = str_replace("##EQUILIBRE##||", "", $ErreurImport);
                }
            }

        } else {
            $MessErr = "Erreur lors de l'import du fichier";
        }
    }

}


if ($_POST["ValidCorrectImp"] && !$_SESSION["User"]->getAut($Section))//correction balance

{
    $MesLinesImport = $_SESSION["LinesImport"];
    $MesCptDiff = $_SESSION["LinesDiff"];
    $MaNouvelleListe = NULL;


    foreach ($MesLinesImport as &$UneLigneImport) {

        $NumAffect = $AffectImp[$UneLigneImport[0]];

        if ($NumAffect) {
            $Montant = 0;
            $Montant = round(StringHelper::Texte2Nombre($MesLinesImport[$NumAffect]["cumulbal"]), 2);

            $Montant += round(StringHelper::Texte2Nombre($MesCptDiff[$UneLigneImport[0]]["cumulbal"]), 2);
            $MesLinesImport[$NumAffect][2] = $Montant;
            $MesLinesImport[$NumAffect]["cumulbal"] = $Montant;

            $UneLigneImport[2] = 0;
            $UneLigneImport["cumulbal"] = 0;

            if ($equivalence[$UneLigneImport[0]]) {//si on demande de retenir l'affectation

                ListeBalance::SaveEquivalance($UneLigneImport[0], $NumAffect);
            }
        }
    }


    foreach ($MesCptDiff as $codeCpt => $ln) {
        unset($MesLinesImport[$codeCpt]);
    }


    $MoisActuel = $_SESSION["MoisHisto"];
    $Diff = false;

    $tabBalanceFormat = dbAcces::get_BalanceFormat();

    if ($_SESSION["station_STA_BAF_NUM"])
        $BAF_NOM = $tabBalanceFormat[$_SESSION["station_STA_BAF_NUM"]]["BAF_NOM"];
    else
        $BAF_NOM = $tabBalanceFormat[$_SESSION["station_BAF_NUM"]]["BAF_NOM"];

    ListeBalance::Import(false, $BAL_MOISNouv1, $BAF_NOM, $MesLinesImport, $Diff, $ErreurImport, FALSE, $_POST["CumulWithPre"], $_POST["ExportAviaXel"]);
    $_SESSION["ioreport_CompteDeplaceEquivalence"] = NULL;

    $_SESSION["LinesImport"] = false;
    $_SESSION["LinesDiff"] = false;
    if (!$ErreurImport) {
        $redirect = "../BalanceBack/Liste.php?initSessionBal=1";
        $DOS_NUM = $_SESSION["station_DOS_NUM"];


        include("../StationBack/open.php");
        exit();
    } else {
        if (stristr($ErreurImport, "##EQUILIBRE##||")) {
            $Equilibrage = str_replace("##EQUILIBRE##||", "", $ErreurImport);
        }
    }

}

if ($_POST["AnnulCorrectBal"] && !$_SESSION["User"]->getAut($Section)) {
    header("Location: ../BalanceBack/Liste.php");
}


$opt = false;
$Enregistrement = false;
if ($classe)// choit d'une classe é afficher dans le menu balance
{

    if ($correctionBal) //mise en place des valeur saisie en session pour garder les valeur lors de changement de classe si en modif
    {
        $opt["correctBal"] = $_SESSION["Session_correctBal"];

        foreach ($_POST["correctBal"] as $cle => $valeur) {
            $opt["correctBal"][$cle] = $valeur;
        }

        $Equilibrage = 0;

        foreach ($opt["correctBal"] as $valeurS) {
            $Equilibrage += StringHelper::Texte2Nombre($valeurS);
        }

        $_SESSION["Session_correctBal"] = $opt["correctBal"];

        $Equilibrage = $Equilibrage;

    }

    if ($classe != "all") {
        $opt["classe"] = explode("-", $classe);
    }
}

if ($AnoSensErrone) {
    $opt["AnoSensErrone"] = true;
}


if ($correctionBal && !$_SESSION["User"]->getAut($Section)) {

    //$_SESSION["MoisHisto"] = station::GetLastBal($_SESSION["station_DOS_NUM"]);

    $opt["correction"] = true;
}


if ($_POST["validCorrectBal"] && !$_SESSION["User"]->getAut($Section)) {
    $Lines = NULL;

    if ($_SESSION["Session_correctBal"])//si $_SESSION["correctBal"] c'est que l'ont est passé par le menu des classes

    {
        foreach ($_POST["correctBal"] as $cle => $valeur) {
            $_SESSION["Session_correctBal"][$cle] = $valeur;
        }

        $_POST["correctBal"] = $_SESSION["Session_correctBal"];
    }

    foreach ($_POST["correctBal"] as $UnNum => $UnMontant) {
        $UneLigne = NULL;
        $UneLigne["cumulbal"] = StringHelper::Texte2Nombre($UnMontant);
        $Lines[$UnNum] = $UneLigne;
    }

    $Diff = false;
    ListeBalance::Import(NULL, $_SESSION["MoisHisto"], NULL, $Lines, $Diff, $ErreurImport, true);

    $opt["correctBal"] = $_POST["correctBal"];

    if (!$ErreurImport) {
        $correctionBal = false;
        $QUERY_STRING = str_replace('&correctionBal=1', '', $QUERY_STRING);

        $redirect = "../BalanceBack/Liste.php?initSessionBal=1";
        $DOS_NUM = $_SESSION["station_DOS_NUM"];


        include("../StationBack/open.php");
        exit();
        //header("Location: ../BalanceBack/Liste.php?initSessionBal=1");
    } else {
        if (strpos('##EQUILIBRE##||', $ErreurImport) !== false) {
            $Equilibrage = str_replace("##EQUILIBRE##||", "", $ErreurImport);

        }
    }
}

if ($histo) {
    $opt["correctBal"] = false;
    $opt["histo"] = $histo;
}

if ($Imp) {
    $opt["imp"] = true;
}


if ($_SESSION["MoisHisto"]) $MoisVoulu = $_SESSION["MoisHisto"];
else                                $MoisVoulu = station::GetLastBal($_SESSION["station_DOS_NUM"]);


$MesLignes = ListeBalance::getTabBalance($MoisVoulu, $opt, $return);

if ($histo) {
    $MaLigneEntete = $return["MaLigneEntete"];
    include 'MHisto.php';
} elseif ($ErreurImport != "##DIFF##") {
    if ($return["PasAnoSens"]) {
        $UnMessage["titre"] = "Compte avec sens erroné";
        $UnMessage["message"] = "R.A.S. sur le sens (Débit/Crédit) des comptes.";
        $UnMessage["fn"] = "function(){
			document.location.href = '" . $_SERVER["HTTP_REFERER"] . "';
		}";
        $MessageBox[] = $UnMessage;

        $impression = false;
    }

    include '../BalanceBack/MListe.php';
} else {
    $Diff = $_SESSION["LinesDiff"];
    include '../BalanceBack/CptInconnu.php';//doit recevoir &$Diff
} ?>