<?php

use Helpers\StringHelper;
use Classes\DB\Database;

require_once __DIR__ . '/../dbClasses/AccesDonnees.php';
require_once __DIR__ . '/../htmlClasses/table.php';
require_once __DIR__ . '/../GestionCRP/CRP.class.php';
require_once __DIR__ . '/../GestionCRP/CRP_Detail.class.php';

global $Section;


class Previsionnel
{

    static function getTab($Type, $MoisActuel, $FamilleSelect = false, $SsFamilleSelect = false, $codePosteSelect = false)
    {
        $Where = array(
            "and" =>
                array(
                    "comptePoste.type" => "='$Type' "
                ),
            array(
                "comptePoste.Famille" => "!='Carburants' "
            )
        );

        if ($codePosteSelect) {
            $plus = &$Where["and"];
            $plus["comptePoste.codePoste"] = "='$codePosteSelect'";
        }

        if ($SsFamilleSelect) {
            $plus = &$Where["and"];
            $plus["comptePoste.SsFamille"] = "=\"$SsFamilleSelect\"";
        }

        if ($FamilleSelect) {
            $plus = &$Where["and"];
            $plus["comptePoste.Famille"] = "=\"$FamilleSelect\"";
        }

        $tri = array(
            "ordre" => "ASC"
        );

        $MesPostes = dbAcces::getPosteVisible($Where, $tri);

        $MaCleTab = NULL;
        $TotauxF = NULL;
        $TotauxSF = NULL;
        $PremF = true;
        $PremSF = true;
        $FaireSToataux = false;
        $FamilleDef = $SsFamilleDef = '';

        $TotalCDC = $_SESSION["ioreport_PREV_CDC"];

        //initialisation du tableau avec ligne total + stotal de chaque postes
        foreach ($MesPostes as $codePoste => $UneLignePoste) {
            $UneLigneTableau = NULL;

            if ($UneLignePoste["SsFamille"] != $SsFamilleDef && $UneLignePoste["Famille"] == $FamilleDef)//pour savoir combien ya de sous familles
            {
                $FaireSToataux = true;
            }

            if ($UneLignePoste["SsFamille"] != $SsFamilleDef)//changement de Sousfamille
            {
                if (!$PremSF && $SsFamilleDef != $FamilleDef && $FaireSToataux) {
                    //LnsTotal

                    $Nom = explode("||#||", $SsFamilleDef);

                    if (count($Nom) > 1)
                        $Nom = "Sous total :";
                    else
                        $Nom = "Total " . $SsFamilleDef . " :";

                    $UneLigneTableau[] = array($Nom => array("libelle" => 1, 'align' => 'right', 'style' => 'font-weight: bolder'));
                    $MesLignesTableau["STOTAL" . $SsFamilleDef] = $UneLigneTableau;
                    $UneLigneTableau = NULL;

                }

                $SsFamilleDef = $UneLignePoste["SsFamille"];
            }


            if ($UneLignePoste["Famille"] != $FamilleDef)//changement de famille
            {
                if (!$PremF) {
                    $FamilleDefStr = str_replace("PERSONNEL ET DE GERANCE", "PERS. ET DE GER.", $FamilleDef);
                    $FamilleDefStr = str_replace("VENTES MARCHANDISES", "BOUTIQUE ET AUTRES PRESTATIONS DE SERVICE", $FamilleDef);
                    //LnTotal
                    $UneLigneTableau[] = array("TOTAL " . $FamilleDefStr . " :" => array("libelle" => 1, 'style' => 'font-weight: bolder'));
                    $MesLignesTableau["TOTAL" . $FamilleDef] = $UneLigneTableau;
                    $UneLigneTableau = NULL;

                    //Ln vide
                    $UneLigneTableau[] = array("&nbsp;" => array('colspan' => "6", "style" => "border-left:1px solid grey"));
                    $MesLignesTableau["VIDE" . $FamilleDef] = $UneLigneTableau;
                    $UneLigneTableau = NULL;

                    if ($FamilleDef == "Chiffres d'affaires")
                        $MesLignesTableau = compChargesProd::setTabMarges($MesLignesTableau);

                }

                $FamilleDefStr = str_replace("VENTES MARCHANDISES", "BOUTIQUE ET AUTRES PRESTATIONS DE SERVICE", $UneLignePoste["Famille"]);
                //ln nom famille
                if ($Type == "Produits")
                    $UneLigneTableau[] = array($FamilleDefStr => array("libelle" => 1, "style" => "font-weight: bolder;text-align:left", 'class' => "tdflotte", 'colspan' => "6"));
                else
                    $UneLigneTableau[] = array($FamilleDefStr => array("libelle" => 1, "style" => "font-weight: bolder;text-align:left", 'class' => "tdflotte", 'colspan' => "6"));
                /*$UneLigneTableau[4]=array(""=>array("class"=>"colvide"));
                $UneLigneTableau[8]=array(""=>array("class"=>"colvide"));*/
                $MesLignesTableau["TITRE" . $UneLignePoste["Famille"]] = $UneLigneTableau;
                $UneLigneTableau = NULL;


                $FamilleDef = $UneLignePoste["Famille"];
                $PremSF = true;
                $FaireSToataux = false;
            }


            $UneLigneTableau[] = array($UneLignePoste["Libelle"] => array("libelle" => 1, "align" => "left"));

            $MesLignesTableau[$codePoste] = $UneLigneTableau;

            if ($PremSF)
                $PremSF = false;
            if ($PremF)
                $PremF = false;
        }

        //LnsTotal
        if ($FaireSToataux) {
            $Nom = explode("||#||", $SsFamilleDef);

            if (count($Nom) > 1)
                $Nom = "Sous total :";
            else
                $Nom = "Total " . $SsFamilleDef . " :";


            $UneLigneTableau = NULL;
            $UneLigneTableau[] = array($Nom => array("libelle" => 1, 'align' => 'right', 'style' => 'font-weight: bolder'));

            $MesLignesTableau["STOTAL" . $SsFamilleDef] = $UneLigneTableau;

        }

        $UneLigneTableau = NULL;
        //LnTotal
        $UneLigneTableau[] = array("TOTAL " . $FamilleDef . " :" => array("libelle" => 1, 'style' => 'font-weight: bolder'));
        $MesLignesTableau["TOTAL" . $FamilleDef] = $UneLigneTableau;

        $MomPrev = dbAcces::getPrev($_SESSION["station_DOS_NUM"], $MoisActuel, $Type);

        $UnTotalAnnuel = 0;
        $UnTotalCor = 0;
        $UnTotalTaux = 0;
        $UnTotalMontant = 0;
        $UnTotalMargeMois = 0;
        $UnSTotalAnnuel = 0;
        $UnSTotalCor = 0;
        $UnSTotalTaux = 0;
        $UnSTotalMontant = 0;
        $UnSTotalMargeMois = 0;

        $BigTotal = [
            "annuel" => 0,
            "taux" => 0,
            "MargeMois" => 0,
            "montant" => 0,
            "cor" => 0,
        ];

        $PremTotal = true;

        foreach ($MesLignesTableau as $codeCompte => &$UneLigneDb) {

            if (is_numeric($codeCompte)) {


                $cle = $MomPrev[$codeCompte]["SAI_CLE"] ?? '';
                $annuel = $MomPrev[$codeCompte]["Annuel"] ?? 0;
                $cor = $MomPrev[$codeCompte]["Correction"] ?? 0;
                $montant = $MomPrev[$codeCompte]["Montant"] ?? 0;
                $Taux = $MomPrev[$codeCompte]["PrevTaux"] ?? 0;

                //c'est une ligne d'un poste
                if (isset($_GET["xls"]) && $_GET["xls"]) {
                    $UneLigneDb[] = str_replace("&nbsp;", "", StringHelper::NombreFr($cle));
                    $UneLigneDb[] = str_replace("&nbsp;", "", StringHelper::NombreFr($annuel, 0));

                    $UneLigneDb[] = str_replace("&nbsp;", "", StringHelper::NombreFr($montant, 0));
                    if ($Type == "Produits")
                        $UneLigneDb[] = str_replace("&nbsp;", "", StringHelper::NombreFr($Taux));

                    $MargeMois = 0;
                    $MargeMois = ($montant * $Taux) / 100;
                    $UneLigneDb[] = str_replace("&nbsp;", "", StringHelper::NombreFr($MargeMois));
                } else {


                    $UneLigneDb[] = array($cle => array("align" => "center"));

                    $UneLigneDb[] = array(StringHelper::NombreFr($annuel, 0) => array("align" => "right"));

                    $UneLigneDb[] = array(StringHelper::NombreFr($montant, 0) => array("align" => "right"));


                    $MargeMois = 0;
                    if ($Type == "Produits") {

                        if ($PremTotal)
                            $UneLigneDb[] = array(StringHelper::NombreFr($Taux, 0) => array("align" => "right"));
                        else
                            $UneLigneDb[] = array("" => array("align" => "right"));

                        $MargeMois = ($montant * $Taux) / 100;

                        $UneLigneDb[] = array(StringHelper::NombreFr($MargeMois, 0) => array("align" => "right"));
                    } else {
                        $UneLigneDb[] = array("" => array("align" => "right"));
                        $UneLigneDb[] = array("" => array("align" => "right"));
                    }

                    if ($cor == "0")
                        $Zero = true;
                    else
                        $Zero = false;


                }

                $UnTotalAnnuel += $annuel;

                $UnTotalTaux += $annuel * ($Taux / 100);

                $UnTotalMontant += $montant;
                $UnTotalCor += $cor;
                $UnTotalMargeMois += $MargeMois;

                $UnSTotalAnnuel += $annuel;

                $UnSTotalTaux += $annuel * ($Taux / 100);

                $UnSTotalMontant += $montant;
                $UnSTotalCor += $cor;
                $UnSTotalMargeMois += $MargeMois;


                if (isset($MesPostes[$codeCompte]["CDC"]) && $MesPostes[$codeCompte]["CDC"]) {
                    if ($Type == "Charges") {
                        $montant = -$montant;
                        $annuel = -$annuel;
                    }

                    $TotalCDC["mensuel"] += $montant;
                    $TotalCDC["annuel"] += $annuel;

                }

            } else {

                if (stristr($codeCompte, "STOTAL")) {

                    $UneLigneDb[] = array("" => array("align" => "right"));
                    $UneLigneDb[] = StringHelper::NombreFr($UnSTotalAnnuel, 0);

                    $UneLigneDb[] = StringHelper::NombreFr($UnSTotalMontant, 0);


                    if ($Type == "Produits") {
                        $UneLigneDb[] = array(StringHelper::NombreFr($UnSTotalTaux, 0) => array("align" => "right"));
                        $UneLigneDb[] = array(StringHelper::NombreFr($UnSTotalMargeMois, 0) => array("align" => "right"));
                    } else {
                        $UneLigneDb[] = array("" => array("align" => "right"));
                        $UneLigneDb[] = array("" => array("align" => "right"));
                    }

                    //$UneLigneDb[] =  StringHelper::NombreFr($UnSTotalCor);
                    $UnSTotalAnnuel = 0;
                    $UnSTotalCor = 0;
                    $UnSTotalTaux = 0;
                    $UnSTotalMontant = 0;
                    $UnSTotalMargeMois = 0;
                } elseif (stristr($codeCompte, "TOTAL")) {

                    $UneLigneDb[] = array("" => array("align" => "right"));

                    $UneLigneDb[] = StringHelper::NombreFr($UnTotalAnnuel, 0);


                    $UneLigneDb[] = StringHelper::NombreFr($UnTotalMontant, 0);

                    if ($Type == "Produits") {
                        $UneLigneDb[] = array(StringHelper::NombreFr($UnTotalTaux, 0) => array("align" => "right"));
                        $UneLigneDb[] = array(StringHelper::NombreFr($UnTotalMargeMois, 0) => array("align" => "right"));
                    } else {
                        $UneLigneDb[] = array("" => array("align" => "right"));
                        $UneLigneDb[] = array("" => array("align" => "right"));
                    }


                    if ($PremTotal && $Type == "Produits") {
                        $UnTotalAnnuel = 0;
                        $UnTotalMontant = 0;
                    }
                    $BigTotal["annuel"] += $UnTotalAnnuel ?? 0;
                    $BigTotal["taux"] += $UnTotalTaux ?? 0;
                    $BigTotal["MargeMois"] += $UnTotalMargeMois ?? 0;
                    $BigTotal["montant"] += $UnTotalMontant ?? 0;
                    $BigTotal["cor"] += $UnTotalCor ?? 0;


                    $UnTotalAnnuel = 0;
                    $UnTotalCor = 0;
                    $UnTotalTaux = 0;
                    $UnTotalMontant = 0;
                    $UnTotalMargeMois = 0;
                    $UnSTotalAnnuel = 0;
                    $UnSTotalCor = 0;
                    $UnSTotalTaux = 0;
                    $UnSTotalMontant = 0;
                    $UnSTotalMargeMois = 0;
                    $PremTotal = false;


                }

            }


        }

        if ($Type == "Produits" || $Type == "Charges") {
            $MesLignesTableau[] = array(
                0 => array("&nbsp;" => array("align" => "right", "style" => "border-left:1px solid grey")),
                1 => array("&nbsp;" => array("align" => "right")),
                2 => array("&nbsp;" => array("align" => "right")),
                3 => array("&nbsp;" => array("align" => "right")),
                4 => array("&nbsp;" => array("align" => "right")),
                5 => array("&nbsp;" => array("align" => "right"))
            );
            $MesLignesTableau["BIGTOTAL"] = array(0 => array("Total pr&eacute;visionnel $Type" => array("align" => "right", "style" => "font-weight: bolder;border-left:1px solid grey")));
            $MesLignesTableau["BIGTOTAL"][] = array("" => array("align" => "right"));
            $MesLignesTableau["BIGTOTAL"][] = StringHelper::NombreFr($BigTotal["annuel"] + $BigTotal["taux"], 0);
            $MesLignesTableau["BIGTOTAL"][] = StringHelper::NombreFr($BigTotal["montant"] + $BigTotal["MargeMois"], 0);
            $MesLignesTableau["BIGTOTAL"][] = array("" => array("align" => "right"));
            $MesLignesTableau["BIGTOTAL"][] = array("" => array("align" => "right"));
        }


        //$MesLignesTableau["BIGTOTAL"][] = StringHelper::NombreFr($BigTotal["cor"]);

        if ($Type == "Produits") {
            $_SESSION["BIGTOTAL_PREVPRODUITS"] = $MesLignesTableau["BIGTOTAL"];
            $_SESSION["ioreport_PREV_CDC"] = $TotalCDC;
            //$_SESSION["TOTALMARGECRP"] = $BigTotal["taux"];
        } elseif ($Type == "Charges") {
            $MesLignesTableau[] = array(0 => array("&nbsp;" => array("align" => "right", "style" => "font-weight: bolder;border-left:1px solid grey")));
            $MesLignesTableau["RESULTATTOTAL"] = array(0 => array("R&eacute;sultat" => array("align" => "right", "style" => "font-weight: bolder;border-left:1px solid grey")));
            $MesLignesTableau["RESULTATTOTAL"][] = array("" => array("align" => "right"));
            $MesLignesTableau["RESULTATTOTAL"][] = StringHelper::NombreFr(StringHelper::Texte2Nombre($_SESSION["BIGTOTAL_PREVPRODUITS"][2]) - $BigTotal["annuel"], 0);
            $MesLignesTableau["RESULTATTOTAL"][] = StringHelper::NombreFr(StringHelper::Texte2Nombre($_SESSION["BIGTOTAL_PREVPRODUITS"][3]) - $BigTotal["montant"], 0);
            $MesLignesTableau["RESULTATTOTAL"][] = array("" => array("align" => "right"));
            $MesLignesTableau["RESULTATTOTAL"][] = array("" => array("align" => "right"));
            /*$MesLignesTableau["CDCTOTAL"] = array(0=>array("CDC"=>array("align"=>"right","style"=>"font-weight: bolder")));
            $MesLignesTableau["CDCTOTAL"][] = "";
            $MesLignesTableau["CDCTOTAL"][] = StringHelper::NombreFr($TotalCDC["annuel"]);
            $MesLignesTableau["CDCTOTAL"][] = StringHelper::NombreFr($TotalCDC["mensuel"]);*/

            $_SESSION["ioreport_PREV_CDC"] = NULL;

        }

        return $MesLignesTableau;

    }

    static function setTabMarges($MesLignesTableau)
    {
        $Retour = NULL;
        $CodeDef = NULL;
        $i = 0;

        foreach ($MesLignesTableau as $codeCompte => $MesTds) {
            if (stristr($codeCompte, "TITRE"))
                $Retour["$codeCompte MARGE" . $i] = array(0 => array("Marges" => ""));
            elseif (stristr($codeCompte, "STOTAL"))
                $Retour["$codeCompte MARGE" . $i] = array(0 => array("sous total :" => ""));
            elseif (stristr($codeCompte, "TOTAL"))
                $Retour["$codeCompte MARGE" . $i] = array(0 => array("Total marges :" => ""));
            else
                $Retour["$codeCompte MARGE" . $i] = $MesTds;
        }

        foreach ($Retour as $code => $LnMarge) {
            $MesLignesTableau[$code] = $LnMarge;
        }

        return $MesLignesTableau;
    }

    static function Update($TabPost, $Periode = false)
    {
        if (!$Periode) {
            $PeriodeRechCle = $_SESSION["MoisHisto"];
        } else {
            $PeriodeRechCle = $Periode;
        }

        $MonTab = $PlageBase = [];

        $arrDebEx = array("DOS_DEBEX" => date("Y-m-t", strtotime(str_replace("-00", "-01", $PeriodeRechCle))), "test" => "<=");
        $arrFinEx = array("DOS_FINEX" => date("Y-m-01", strtotime(str_replace("-00", "-01", $PeriodeRechCle))), "test" => ">=");

        $TabDossier = dbAcces::getDossier($_SESSION["station_STA_NUM"], null, null, $arrDebEx, $arrFinEx, true);

        if ($TabDossier) {
            //Récupération des comptes Postes
            $MesPostes = dbAcces::getPoste();

            $DOS_NUM = $TabDossier[0]["DOS_NUM"];

            $MaPremiereDate = date("Y-m-00", strtotime($TabDossier[0]["DOS_DEBEX"]));
            $MaDerniereDate = StringHelper::DatePlus($MaPremiereDate, array("dateformat" => "Y-m-00", "moisplus" => 11));

            $PlageBase["DateDeb"] = $MaPremiereDate;
            $PlageBase["DateFin"] = $MaDerniereDate;

            $MesCle = dbAcces::getSaison($PeriodeRechCle, $DOS_NUM, $MesSum, null, $PlageBase);

            $coefProrata = 1;
            $jrDebEx = (int)date("d", strtotime($_SESSION["station_DOS_DEBEX"]));
            $jrFinEx = (int)date("d", strtotime($_SESSION["station_DOS_FINEX"]));

            // Prorata, car on est sur le dernier mois d'exercice et que l'exercice ne se finit pas au dernier jour du mois
            if (
                $jrFinEx != date("t", strtotime(str_replace("-00", "-01", $PeriodeRechCle)))
                && date("Y-m", strtotime(str_replace("-00", "-01", $PeriodeRechCle))) == date("Y-m", strtotime($_SESSION["station_DOS_FINEX"]))
            ) {
                $nbJrMoisFinEx = (int)date("t", strtotime($_SESSION["station_DOS_FINEX"]));

                $coefProrata = round($jrFinEx / $nbJrMoisFinEx, 2);
            }

            // Prorata, car on est sur le premier mois d'exercice et que l'exercice ne débute pas au premier jour du mois
            if (
                $jrDebEx != 1
                && date("Y-m", strtotime(str_replace("-00", "-01", $PeriodeRechCle))) == date("Y-m", strtotime($_SESSION["station_DOS_DEBEX"]))
            ) {
                $nbJrMoisDebEx = (int)date("t", strtotime($_SESSION["station_DOS_DEBEX"]));

                $coefProrata = round((($nbJrMoisDebEx - $jrDebEx + 1) / $nbJrMoisDebEx), 2);
            }

            $TotalProduits = 0;
            $TotalCharges = 0;

            foreach ($TabPost["cle"] as $codePoste => $cle) {
                if ($TabPost["annuel"][$codePoste]) {
                    if ($cle < 0) {
                        $cle = 0;
                    }

                    if ($cle > 3) {
                        $cle = 3;
                    }

                    $MonTab[$codePoste]["cle"] = $cle;
                    $MonTab[$codePoste]["annuel"] = StringHelper::Texte2Nombre($TabPost["annuel"][$codePoste]);
                    $MonTab[$codePoste]["prevtaux"] = StringHelper::Texte2Nombre($TabPost["prevtaux"][$codePoste]);
                    $MonTab[$codePoste]["cor"] = "";

                    $MaPeriode = $TabPost["Periode"][$codePoste];

                    if ($TabPost["cor"][$codePoste] != "0") {
                        if (!$cle) {
                            $MonTab[$codePoste]["montant"] = round((($TabPost["montant"][$codePoste])), 2);
                        } else {
                            $MonTab[$codePoste]["montant"] = round((($TabPost["montant"][$codePoste] * 12 * ($MesCle[$MaPeriode]["SAI_CLE$cle"] / $MesSum["SAI_CLE$cle"]))), 2);
                        }
                    } else {
                        $MonTab[$codePoste]["montant"] = $MonTab[$codePoste]["cor"];
                    }

                    $MonTab[$codePoste]["montant"] = $MonTab[$codePoste]["montant"] * $coefProrata;

                    $montant = $MonTab[$codePoste]["montant"];
                    $Taux = $MonTab[$codePoste]["prevtaux"];

                    if ($MesPostes[$codePoste]['Type'] == "Produits" && $MesPostes[$codePoste]['Famille'] != "Carburants") {
                        $marge = ($montant * $Taux) / 100;
                        if ($Taux == 0) {
                            $marge = $montant;
                        }

                        $TotalProduits += $marge;
                    } elseif ($MesPostes[$codePoste]['Type'] == "Charges") {
                        $TotalCharges += $montant;
                    }
                }
            }

            dbAcces::setPrev($DOS_NUM, $PeriodeRechCle, $MonTab, $TabPost["Type"]);

            //Mise à jour du résultat prev dans la table balance import
            $ResultatPrev = $TotalProduits - $TotalCharges;

            $sql = "update balanceimport set BALI_RESPREV = '$ResultatPrev' where DOS_NUM = '$DOS_NUM' and BALI_MOIS = '$PeriodeRechCle'";
            Database::query($sql);
        }
    }

    static function RefactAllPrev($d)
    {
        if (!$d['CRP_NUM'] || !$d['STA_NUM']) {
            throw new Exception("RefactAllPrev > Variables obligatoires : CRP_NUM (" . $d['CRP_NUM'] . "), STA_NUM (" . $d['STA_NUM'] . ")");
        }

        //Récupération du CRP
        $param = array(
            "tabCriteres" => array("CRP_NUM" => $d['CRP_NUM'])
        );
        $CRP = db_CRP::select_CRP($param);
        $CRP = $CRP[$d['CRP_NUM']];

        //Récupération du détail du CRP
        $param = array("tabCriteres" => array(
            "CRP_NUM" => $d['CRP_NUM'],
            "STA_NUM" => $d['STA_NUM']
        ));
        $MesPrev = db_CRP_Detail::select_CRP_Detail($param);

        //Boucle d'insertion
        if (!isset($DateFin) || !$DateFin) {
            $DateFin = date('Y-m-00', strtotime($CRP["CRP_FIN"]));
        }

        if ($DateFin >= date('Y-m-00', strtotime($CRP["CRP_FIN"]))) {
            $DateFin = date('Y-m-00', strtotime($CRP["CRP_FIN"]));
        }

        if (!isset($DateDbt) || !$DateDbt) {
            $DateDbt = date('Y-m-00', strtotime($CRP["CRP_DBT"]));
        }

        if ($DateDbt <= date('Y-m-00', strtotime($CRP["CRP_DBT"]))) {
            $DateDbt = date('Y-m-00', strtotime($CRP["CRP_DBT"]));
        }

        $DateCourante = $DateDbt;

        while ($DateCourante <= $DateFin) {
            $coefProrata = $coefProrata_prec = $coefProrata_suiv = 1;
            $MesPrev_prec = $MesPrev_suiv = [];
            $premierDuMoisCourant = str_replace("-00", "-01", $DateCourante);

            // Test pour vérifier si CRP debut ou fin sur un mois incomplet
            // ------------------------------------------------------------
            // Si c'est le premier mois du CRP et si le CRP ne commence pas le 1er jour du mois
            if ($DateCourante == date('Y-m-00', strtotime($CRP["CRP_DBT"])) && $CRP["CRP_DBT"] != date('Y-m-01', strtotime($CRP["CRP_DBT"]))) {
                $nbJrMoisDebCrp = (int)date("t", strtotime($CRP["CRP_DBT"]));
                $jrDebCrp = (int)date("d", strtotime($CRP["CRP_DBT"]));
                $coefProrata = round(($nbJrMoisDebCrp - $jrDebCrp + 1) / $nbJrMoisDebCrp, 2);

                //Vérification s'il y a un CRP précédent
                $param = [
                    "tabCriteres" => [
                        "STA_NUM" => $_SESSION["station_STA_NUM"],
                        "CRP_FIN" => [
                            "whereperso" => " BETWEEN '" . $premierDuMoisCourant . "' and '" . date("Y-m-t", strtotime($premierDuMoisCourant)) . "'"
                        ]
                    ]
                ];

                $CRP_PREC = db_CRP::select_CRP($param);

                if ($CRP_PREC) {
                    $array_keys = array_keys($CRP_PREC);
                    $CRP_NUM_PREC = $array_keys[0];
                    $CRP_PREC = $CRP_PREC[$CRP_NUM_PREC];

                    $nbJrMoisFinCrpPrec = (int)date("t", strtotime($CRP_PREC["CRP_FIN"]));
                    $jrFinCrpPrec = (int)date("d", strtotime($CRP_PREC["CRP_FIN"]));
                    $coefProrata_prec = round(($jrFinCrpPrec / $nbJrMoisFinCrpPrec), 2);

                    //Récupération du détail du CRP
                    $param = [
                        "tabCriteres" => [
                            "CRP_NUM" => $CRP_NUM_PREC,
                            "STA_NUM" => $d['STA_NUM']
                        ]
                    ];
                    $MesPrev_prec = db_CRP_Detail::select_CRP_Detail($param);
                }
            }

            //Si c'est le dernier mois du CRP et si le CRP ne se termine pas le dernier jour du mois
            if ($DateCourante == date('Y-m-00', strtotime($CRP["CRP_FIN"])) && $CRP["CRP_FIN"] != date('Y-m-t', strtotime($CRP["CRP_FIN"]))) {
                $nbJrMoisFinCrp = (int)date("t", strtotime($CRP["CRP_FIN"]));
                $jrFinCrp = (int)date("d", strtotime($CRP["CRP_FIN"]));
                $coefProrata = round($jrFinCrp / $nbJrMoisFinCrp, 2);

                //Vérification s'il y a un CRP suivant
                $param = [
                    "tabCriteres" => [
                        "STA_NUM" => $_SESSION["station_STA_NUM"],
                        "CRP_DBT" => [
                            "whereperso" => " BETWEEN '" . $premierDuMoisCourant . "' and '" . date("Y-m-t", strtotime($premierDuMoisCourant)) . "'"
                        ]
                    ]
                ];

                $CRP_SUIV = db_CRP::select_CRP($param);

                if ($CRP_SUIV) {
                    $array_keys = array_keys($CRP_SUIV);
                    $CRP_NUM_SUIV = $array_keys[0];
                    $CRP_SUIV = $CRP_SUIV[$CRP_NUM_SUIV];

                    $nbJrMoisFinCrpSuiv = (int)date("t", strtotime($CRP_SUIV["CRP_DBT"]));
                    $jrFinCrpSuiv = (int)date("d", strtotime($CRP_SUIV["CRP_DBT"]));
                    $coefProrata_suiv = round((($nbJrMoisFinCrpSuiv - $jrFinCrpSuiv + 1) / $nbJrMoisFinCrpSuiv), 2);

                    //Récupération du détail du CRP
                    $param = [
                        "tabCriteres" => [
                            "CRP_NUM" => $CRP_NUM_SUIV,
                            "STA_NUM" => $d['STA_NUM']
                        ]
                    ];
                    $MesPrev_suiv = db_CRP_Detail::select_CRP_Detail($param);
                }
            }

            $DateCourante = $premierDuMoisCourant;

            $MonPrevFormate = [];

            foreach ($MesPrev as $codePoste => $Ln) {
                $MonPrevFormate["cle"]["$codePoste"] = $Ln["SAI_CLE"];
                $MonPrevFormate["annuel"]["$codePoste"] = $Ln["Montant"] * 12;

                if ($coefProrata_prec) {
                    $MonPrevFormate["montant"]["$codePoste"] = $coefProrata * $Ln["Montant"] + $coefProrata_prec * $MesPrev_prec[$codePoste]["Montant"];
                } elseif ($coefProrata_suiv) {
                    $MonPrevFormate["montant"]["$codePoste"] = $coefProrata * $Ln["Montant"] + $coefProrata_suiv * $MesPrev_suiv[$codePoste]["Montant"];
                } else {
                    $MonPrevFormate["montant"]["$codePoste"] = $Ln["Montant"];
                }

                $MonPrevFormate["cor"]["$codePoste"] = "";
                $MonPrevFormate["prevtaux"]["$codePoste"] = $Ln["PrevTaux"];
                $MonPrevFormate["Periode"]["$codePoste"] = date("Y-m-00", strtotime($DateCourante));
            }

            Previsionnel::Update($MonPrevFormate, date("Y-m-00", strtotime($DateCourante)));

            $DateCourante = StringHelper::DatePlus($DateCourante, array("moisplus" => 1, "dateformat" => "Y-m-00"));
        }
    }

    static function EffacePrev($Periode, $Type)
    {
        dbAcces::setPrev($_SESSION["station_DOS_NUM"], $Periode, NULL, $Type);
    }
}
