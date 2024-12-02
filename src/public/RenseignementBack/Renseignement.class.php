<?php

use Classes\DB\Database;
use Helpers\StringHelper;

require_once __DIR__ . '/../ctrl/ctrl.php';
require_once __DIR__ . '/../dbClasses/station.php';
require_once __DIR__ . '/../dbClasses/AccesDonnees.php';
require_once __DIR__ . '/../htmlClasses/table.php';

if (!isset($Imprimer) || !$Imprimer) {
    include_once __DIR__ . '/../Anomalie/Anomalie.class.php';
}

class ListeRenseignement
{

    static function getTab($Mois, $opt = [], &$retour = [])
    {

        $Where = [];

        $Tri = [
            "ordre_tri" => "ASC",
            "numero" => "ASC"
        ];
        $MesComptes = dbAcces::getComptes($Where, $Tri, null, array("with_jeux" => true));

        foreach ($MesComptes as $codeCompte => $LnCpt) {
            $UneLigneTableau = [];
            $UneLigneTableau[] = array(str_replace("ACHATS ", "", substr($LnCpt["numero"], strlen($LnCpt["numero"]) - 4) . " - " . str_replace("Achats", "", $LnCpt["libelle"])) => array("align" => "left"));
            $MesLignesTableau["compte" . $codeCompte] = $UneLigneTableau;
        }

        $MesResultats = dbAcces::getTauxMarge($_SESSION["station_DOS_NUM"], $Mois);

        $Total = [
            "StockInit" => 0,
            "StockFinal" => 0,
        ];

        // Si $opt n'est pas vide, alors on vient depuis le menu "Outils", besoin des anomalies
        if (!empty($opt)) {
            $_SESSION["NbAno"] = Anomalie::CompterAnomalies($Ano);
        }

        $tabindexStI = 500;
        $tabindexTaux = 600;
        $tabindexStF = 700;

        $tabindexStI_ok = 1500;
        $tabindexTaux_ok = 1600;
        $tabindexStF_ok = 1700;

        foreach ($MesLignesTableau as $CodeLigne => &$UneLigneDb) {
            if (stripos($CodeLigne, 'compte') !== false) {
                $MonCodeCompte = str_replace("compte", "", $CodeLigne);

                $MesResultats[$MonCodeCompte]["StockInit"] = $MesResultats[$MonCodeCompte]["StockInit"] ?? 0;
                $MesResultats[$MonCodeCompte]["StockFinal"] = $MesResultats[$MonCodeCompte]["StockFinal"] ?? 0;

                $ErreurStI = "";
                $ErreurTaux = "";
                $ErreurStF = "";

                //erreur stock init
                if (isset($opt["AnoStockInit"]) && $opt["AnoStockInit"]) {
                    $MesAno = explode("||#||", $Ano["StockInit"]);

                    if (in_array($MonCodeCompte, $MesAno)) {
                        $retour["AnoStockInit"][$MonCodeCompte] = true;
                        $ErreurStI = " style='background-color:#F9DA75' tabindex='" . ($tabindexStI++) . "'";
                    }
                }

                //erreur stock Final
                if (isset($opt["AnoStockFinal"]) && $opt["AnoStockFinal"]) {
                    $MesAno = explode("||#||", $Ano["StockFinal"]);

                    if (in_array($MonCodeCompte, $MesAno)) {
                        $retour["AnoStockFinal"][$MonCodeCompte] = true;
                        $ErreurStF = " style='background-color:#F9DA75' tabindex='" . ($tabindexStF++) . "'";
                    }
                }

                //erreur variation stock
                if (isset($opt["AnoVariationStock"]) && $opt["AnoVariationStock"]) {
                    $MesAno = explode("||#||", $Ano["VariationStock"]);

                    if (in_array($MonCodeCompte, $MesAno)) {
                        $retour["AnoVariationStock"][$MonCodeCompte] = true;
                        $ErreurStF = " style='background-color:#F9DA75' tabindex='" . ($tabindexStF++) . "'";
                    }
                }

                //erreur stock oubli marge
                if (isset($opt["oublimarge"]) && $opt["oublimarge"]) {
                    $MesAno = explode("||#||", $Ano["oublimarge"]);

                    if (in_array($MonCodeCompte, $MesAno)) {
                        $retour["oublimarge"][$MonCodeCompte] = true;
                        $ErreurTaux = " style='background-color:#F9DA75' tabindex='" . ($tabindexTaux++) . "'";
                    }
                }

                //erreur stock taux supérieur à 100
                if (isset($opt["AnoTauxSup100"]) && $opt["AnoTauxSup100"]) {
                    $MesAno = explode("||#||", $Ano["tauxsup100"]);

                    if (in_array($MonCodeCompte, $MesAno)) {
                        $retour["AnoTauxSup100"][$MonCodeCompte] = true;
                        $ErreurTaux = " style='background-color:#F9DA75' tabindex='" . ($tabindexTaux++) . "'";
                    }
                }

                //erreur tx different prévisionnel
                if (isset($opt["PrevTxModifie"]) && $opt["PrevTxModifie"]) {
                    Anomalie::Rem_TxMarge($Rem);
                    $MesRem = explode("||#||", $Rem["PrevTxModifie"]);

                    if (in_array($MonCodeCompte, $MesRem)) {
                        $retour["AnoTauxSup100"][$MonCodeCompte] = true;
                        $ErreurTaux = " style='background-color:#F9DA75' tabindex='" . ($tabindexTaux++) . "'";
                    }
                }

                //erreur oublie saisie stocks finaux
                if (isset($opt["AnoStockFinalZero"]) && $opt["AnoStockFinalZero"]) {
                    $MesAno = explode("||#||", $Ano["AnoStockFinalZero"]);

                    if (in_array($MesComptes[$MonCodeCompte]["codePoste"], $MesAno) && !$MesResultats[$MonCodeCompte]["StockFinal"] && !$MesResultats[$MonCodeCompte]["StockFinalZero"]) {
                        $retour["AnoStockFinalZero"][$MonCodeCompte] = true;
                        $ErreurStF = " style='background-color:#F9DA75' tabindex='" . ($tabindexStF++) . "'";
                    }
                }

                if (isset($MesResultats[$MonCodeCompte]["StockFinalZero"]) && $MesResultats[$MonCodeCompte]["StockFinalZero"]) {
                    $StFinal = "0";
                } else {
                    $St = $MesResultats[$MonCodeCompte]["StockFinal"] ?? 0;
                    $StFinal = StringHelper::NombreFr($St, 2, false, true);
                }

                if (isset($MesResultats[$MonCodeCompte]["TauxZero"]) && $MesResultats[$MonCodeCompte]["TauxZero"]) {
                    $TxZero = "0";
                } else {
                    $Tx = $MesResultats[$MonCodeCompte]["Taux"] ?? 0;
                    $TxZero = StringHelper::NombreFr($Tx, 2, false, true);
                }

                $disabled = $_SESSION['ModifOK'] ? "" : " disabled='disabled' ";

                $UneLigneDb[] = array("
                <input type='hidden' name='EcartMarge[" . $MonCodeCompte . "]' value='" . ($MesResultats[$MonCodeCompte]["EcartMarge"] ?? 0) . "'/>
                <input type='hidden' name='EcartMargePrec[" . $MonCodeCompte . "]' value='" . ($MesResultats[$MonCodeCompte]["EcartMargePrec"] ?? 0) . "'/>
                <input type='text' $disabled tabindex='" . $tabindexTaux_ok++ . "' " . $ErreurTaux . " " . $_SESSION["User"]->getAut("Rg", "Taux") . " class='gapiareamontant' size='10' name='Taux[" . $MonCodeCompte . "]' value=\"" . $TxZero . "\"/>" => array("align" => "right"));

                $UneLigneDb[] = array("<input type='text' $disabled tabindex='" . $tabindexStI_ok++ . "' " . $ErreurStI . " " . $_SESSION["User"]->getAut("Rg", "StockInit") . " class='gapiareamontant' size='15' name='StockInit[" . $MonCodeCompte . "]' value=\"" . StringHelper::NombreFr($MesResultats[$MonCodeCompte]["StockInit"], 2, false, true) . "\"/>" => array("align" => "right"));
                $UneLigneDb[] = array("<input type='text' $disabled tabindex='" . $tabindexStF_ok++ . "' " . $ErreurStF . " " . $_SESSION["User"]->getAut("Rg", "StockFinal") . " class='gapiareamontant' size='15' name='StockFinal[" . $MonCodeCompte . "]' value=\"" . $StFinal . "\"/>" => array("align" => "right"));

                $Total["StockInit"] += $MesResultats[$MonCodeCompte]["StockInit"];
                $Total["StockFinal"] += $MesResultats[$MonCodeCompte]["StockFinal"];
            }
        }

        //ln BIG TOTAL
        $UneLigneTableau = [];
        $UneLigneTableau[] = array("TOTAL : " => array("style" => "text-align:right"));
        $UneLigneTableau[] = [];
        $UneLigneTableau[] = array(StringHelper::NombreFr($Total["StockInit"]) => array("style" => "text-align:right"));
        $UneLigneTableau[] = array(StringHelper::NombreFr($Total["StockFinal"]) => array("style" => "text-align:right"));
        $MesLignesTableau["BIGTOTAL"] = $UneLigneTableau;

        return $MesLignesTableau;
    }

    static function CopieBal($Mois, $Type = "StockInit")
    {
        if ($_SESSION["User"]->getAut("option", "copiebal")) {
            return;
        }

        $Tri = ["ordre" => "ASC"];
        $MesComptes = dbAcces::getComptes(null, $Tri, null, array("with_jeux" => true));

        $MesResultats = dbAcces::getTauxMarge($_SESSION["station_DOS_NUM"], $Mois);

        foreach ($MesComptes as $codeCompte => $UnCompte) {
            $TotalStock = 0;
            $MesStock = dbAcces::getResultatsCompte($Mois, false, false, false, false, false, false, false, array("test" => "=", "Stock" => $codeCompte));//recup des ventes

            foreach ($MesStock as $UnStock) {
                $TotalStock += $UnStock["BAL_CUMUL"];
            }

            if ($Type == "StockInit") {
                $MesResultats[$codeCompte]["StockInit"] = $TotalStock;
            }

            if ($Type == "StockFinal") {
                $MesResultats[$codeCompte]["StockFinal"] = $TotalStock;
            }

            if (!$MesResultats[$codeCompte]["Taux"] && !$MesResultats[$codeCompte]["TauxZero"]) {
                $MesResultats[$codeCompte]["Taux"] = "";
            }
        }

        ListeRenseignement::setTaux($MesResultats, $Mois);
    }

    static function CopieTxPrev($Mois)
    {
        $MesComptes = dbAcces::getComptes();
        $MonPrev = dbAcces::getPrev($_SESSION["station_DOS_NUM"], $Mois);

        $MesTx = [];

        foreach ($MesComptes as $codeCompte => $UnCompte) {
            $MesTx[$codeCompte] = $MonPrev[$UnCompte["codePoste"]]["PrevTaux"];

            if (!$MesTx[$codeCompte]) {
                $MesTx[$codeCompte] = "";
            }
        }

        if ($MesTx) {
            dbAcces::setTxMarge($_SESSION["station_DOS_NUM"], $Mois, $MesTx);
        }
    }

    static function setTaux($Tab, $Mois, $Post = false)
    {
        if ($_SESSION["User"]->getAut("Rg", "Taux")) {
            return;
        }

        $MesTaux = [];

        if ($Post) {
            foreach ($Tab["Taux"] as $CodeCompte => $Taux) {
                $MesTaux[$CodeCompte]["Taux"] = StringHelper::Texte2Nombre($Taux);
                $MesTaux[$CodeCompte]["StockInit"] = StringHelper::Texte2Nombre($Tab["StockInit"][$CodeCompte]);
                $MesTaux[$CodeCompte]["StockFinal"] = StringHelper::Texte2Nombre($Tab["StockFinal"][$CodeCompte]);
                $MesTaux[$CodeCompte]["EcartMarge"] = $Tab["EcartMarge"][$CodeCompte];
                $MesTaux[$CodeCompte]["EcartMargePrec"] = $Tab["EcartMargePrec"][$CodeCompte];
            }
        } else {
            $MesTaux = $Tab;
        }

        dbAcces::setTaux($MesTaux, $_SESSION["station_DOS_NUM"], $Mois, false);
        dbAcces::setMAJBaseMois($_SESSION["station_DOS_NUM"], $Mois, 0);//mise à zero de la date maj base pour anomalie
    }

    static function getTabSaison($Periode, &$MesSum)
    {
        if ($Periode) {
            $MaSaison = dbAcces::getSaison($Periode, $_SESSION["station_DOS_NUM"], $MesSum);
            $MaSaison = $MaSaison[$Periode]["SAI_NUMSAISON"];
        }

        $PlageBase = [];
        $MesSaison = dbAcces::getSaison(NULL, $_SESSION["station_DOS_NUM"], $MesSum, $MaSaison, $PlageBase);
        $MesLigneTab = [];

        $disabled = $_SESSION['ModifOK'] ? "" : " disabled='disabled' ";

        foreach ($MesSaison as $Date => $LigneDb) {
            if ($Date >= $PlageBase["DateDeb"] && $Date <= $PlageBase["DateFin"]) {
                $UneLigneTab = [];
                $UneLigneTab["SAI_DATE"] = array(StringHelper::Mysql2DateFr($Date) => array("align" => "center"));

                if (!$MesLigneTab) {
                    $MonNumSaison = "<input type='hidden' name='SAI_NUMSAISON' value='" . $LigneDb["SAI_NUMSAISON"] . "'/>";
                } else {
                    $MonNumSaison = "";
                }

                $UneLigneTab["SAI_CLE1"] = array("$MonNumSaison<input type='text' $disabled size='7' style='text-align:right'  name='SAI_CLE1[$Date]' value=\"" . StringHelper::NombreFr($LigneDb["SAI_CLE1"], 4, true, true) . "\" " . $_SESSION["User"]->getAut("Rg", "Saison") . "/>" => array("align" => "center"));
                $UneLigneTab["SAI_CLE2"] = array("<input type='text' $disabled size='7' style='text-align:right' name='SAI_CLE2[$Date]' value=\"" . StringHelper::NombreFr($LigneDb["SAI_CLE2"], 4, true, true) . "\" " . $_SESSION["User"]->getAut("Rg", "Saison") . "/>" => array("align" => "center"));
                $UneLigneTab["SAI_CLE3"] = array("<input type='text' $disabled size='7' style='text-align:right' name='SAI_CLE3[$Date]' value=\"" . StringHelper::NombreFr($LigneDb["SAI_CLE3"], 4, true, true) . "\" " . $_SESSION["User"]->getAut("Rg", "Saison") . "/>" => array("align" => "center"));

                $MesLigneTab[$Date] = $UneLigneTab;
            }
        }

        return $MesLigneTab;
    }

    static function setSaison($Tab)
    {
        if ($_SESSION["User"]->getAut("Rg", "Saison")) {
            return;
        }

        $MesSaisons = [];

        foreach ($Tab["SAI_CLE1"] as $Periode => $Cle1) {
            $Tab["SAI_CLE1"][$Periode] = StringHelper::Texte2Nombre($Tab["SAI_CLE1"][$Periode]);
            $Tab["SAI_CLE2"][$Periode] = StringHelper::Texte2Nombre($Tab["SAI_CLE2"][$Periode]);
            $Tab["SAI_CLE3"][$Periode] = StringHelper::Texte2Nombre($Tab["SAI_CLE3"][$Periode]);
        }

        foreach ($Tab["SAI_CLE1"] as $Periode => $Cle1) {
            $MesSaisons[$Periode]["SAI_CLE1"] = $Cle1;
            $MesSaisons[$Periode]["SAI_CLE2"] = $Tab["SAI_CLE2"][$Periode];
            $MesSaisons[$Periode]["SAI_CLE3"] = $Tab["SAI_CLE3"][$Periode];
        }

        if ($_SESSION["station_DOS_NBMOIS"] > 12) {
            $Periode = StringHelper::DatePlus($Periode, array("moisplus" => 1));

            while (strtotime($Periode) < strtotime($_SESSION["station_DOS_FINEX"])) {
                $SaisonDebutF = date("Y-m-00", strtotime($Periode));
                $SaisonDebutFN1 = StringHelper::DatePlus($Periode, array("anneesplus" => -1, "dateformat" => "Y-m-00"));

                $MesSaisons[$SaisonDebutF]["SAI_DATE"] = $SaisonDebutF;
                $MesSaisons[$SaisonDebutF]["SAI_CLE1"] = $MesSaisons[$SaisonDebutFN1]["SAI_CLE1"];
                $MesSaisons[$SaisonDebutF]["SAI_CLE2"] = $MesSaisons[$SaisonDebutFN1]["SAI_CLE2"];
                $MesSaisons[$SaisonDebutF]["SAI_CLE3"] = $MesSaisons[$SaisonDebutFN1]["SAI_CLE3"];

                $Periode = StringHelper::DatePlus($Periode, array("moisplus" => 1));
            }
        }

        dbAcces::setSaison($MesSaisons, $_SESSION["station_DOS_NUM"], $Tab["SAI_NUMSAISON"]);
        dbAcces::setMAJBaseMois($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"], 0);//mise à zero de la date maj base pour anomalie
    }

    static function setCarb($Tab)
    {
        if ($_SESSION["User"]->getAut("Rg", "Carburant")) {
            return;
        }

        $Tab = StringHelper::cleanTab("CARV_VOLUME", $Tab);
        $Tab = $Tab["CARV_VOLUME"];

        foreach ($Tab as &$Volume) {
            $Volume = StringHelper::Texte2Nombre($Volume);
        }

        dbAcces::setLitrageCarb($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"], $Tab);

        //mise à zero de la date maj base pour anomalie
        if (!$_SESSION["agip_AG_NUM"]) {
            dbAcces::setMAJBaseMois($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"], 0);
        }
    }

    static function getTabCarburant($Periode, &$Total = 0)
    {
        $MesCarb = dbAcces::getCarb();

        // Récupération des litrages
        $MesLitrage = dbAcces::getLitrageCarb($_SESSION["station_DOS_NUM"], $Periode);

        $MesLigneTableau = [];

        $disabled = $_SESSION['ModifOK'] ? "" : " disabled='disabled' ";

        foreach ($MesCarb as $CARB_NUM => $UnCarb) {
            if (!isset($MesLitrage[$CARB_NUM]["CARV_VOLUME"])) {
                $MesLitrage[$CARB_NUM]["CARV_VOLUME"] = 0;
            }

            $UneLigneTab = [];

            $UneLigneTab[] = [
                $UnCarb["CARB_NOM"] => ["align" => "left", "colspan" => 2]
            ];
            $UneLigneTab[] = "";
            $UneLigneTab[] = [
                "<input type='text' $disabled style='text-align:right' name='CARV_VOLUME[" . $CARB_NUM . "]' value='" . StringHelper::NombreFr($MesLitrage[$CARB_NUM]["CARV_VOLUME"], 0) . "' " . $_SESSION["User"]->getAut("Rg", "Carburant") . "/>"
                => ["align" => "right", "colspan" => 2]
            ];
            $UneLigneTab[] = "";
            $MesLigneTableau[$CARB_NUM] = $UneLigneTab;

            $Total += $MesLitrage[$CARB_NUM]["CARV_VOLUME"];
        }

        return $MesLigneTableau;
    }

    static function getTabDivers($Periode)
    {
        $UneLigneTab = [];

        $UneLigneTab[] = array("Date dernier inventaire" => array("align" => "left", "colspan" => 2));
        $UneLigneTab[] = "";

        $MontantDivers = dbAcces::getRgDivers($Periode, $_SESSION["station_DOS_NUM"], "dateinv");
        $MontantDivers = $MontantDivers["dateinv"]["RGD_DATE"];

        $disabled = $_SESSION['ModifOK'] ? "" : " disabled='disabled' ";

        $UneLigneTab[] = array("<input type='text' $disabled style='text-align:center' id='dateinv' name='Divers[dateinv]' value='" . StringHelper::MySql2DateFr($MontantDivers) . "' " . $_SESSION["User"]->getAut("Rg", "Divers") . "/>" => array("align" => "right", "colspan" => 2));
        $UneLigneTab[] = "";
        $MesLigneTableau["dateinv"] = $UneLigneTab;

        return $MesLigneTableau;
    }

    static function setTabDivers($Tab)
    {
        if ($_SESSION["User"]->getAut("Rg", "Divers")) {
            return;
        }

        $Tab = StringHelper::cleanTab("Divers", $Tab);
        $Tab = $Tab["Divers"];

        foreach ($Tab as &$date) {
            $date = StringHelper::DateFr2MySql($date);
            $MaDateFin = date("Y-m-t", strtotime(str_replace("-00", "-01", $_SESSION["MoisHisto"])));

            if ($date > $MaDateFin) {
                $date = "";
            }
        }

        dbAcces::setRgDivers($_SESSION["MoisHisto"], $_SESSION["station_DOS_NUM"], $Tab);
    }
}
