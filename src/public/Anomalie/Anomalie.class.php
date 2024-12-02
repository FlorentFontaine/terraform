<?php


require_once __DIR__ . '/../dbClasses/AccesDonnees.php';
require_once __DIR__ . '/../dbClasses/station.php';

use Classes\DB\Database;
use Helpers\StringHelper;
use Repositories\BilanRepository;

class Anomalie
{

    static function CompterAnomalies(&$Ano = array())
    {
        $Nb = 0;

        if (!isset($_SESSION["station_PREM_BAL"]) || !$_SESSION["station_PREM_BAL"]) {
            Anomalie::Ano_PourcMarge($Ano);
            Anomalie::Ano_Carburant($Ano);
            Anomalie::Ano_RensComp($Ano);
            Anomalie::SensCompteErrone($Ano);
            Anomalie::Ano_MAJBase($Ano);
            Anomalie::Ano_Bilan($Ano);
            Anomalie::Ano_Resultat($Ano);
//            Anomalie::Ano_Inventaire($Ano);
            Anomalie::Rem_TxMarge($Rem);

            $_SESSION["Ano"] = array();
            $_SESSION["AnoStyle"] = array();

            foreach ($Ano as $cle => $Valeur) {
                if ($Valeur) {
                    $_SESSION["Ano"][$cle] = $Valeur;
                    $_SESSION["AnoStyle"][$cle] = "color:red";
                    $Nb++;
                }
            }

            $_SESSION["RemStyle"] = array();

            foreach ($Rem as $cle => $Valeur) {
                if ($Valeur) {
                    $_SESSION["RemStyle"][$cle] = "color:blue";
                }
            }
        }

        $_SESSION["NbAno"] = $Nb;

        return $Nb;
    }

    // --------------------
    //     REMARQUES
    // --------------------



    static function Rem_Objectif(&$Rem)
    {
        if (!dbAcces::getPrev($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"])) {
            $Rem["PrevCc"] = true;
        }
    }

    static function Rem_TxMarge(&$Rem)
    {
        $MesComptes = dbAcces::getComptes(array("and" => array("comptes.Type" => " = 'achat'")), false, false, true);
        $MonPrev = dbAcces::getPrev($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"], false, false, false, false, false, true);
        $MesTxMarge = dbAcces::getTauxMarge($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"]);

        foreach ($MesComptes as $code_compte => $MonCompte) {
            if (!isset($MonPrev[$MonCompte["codePoste"]]["PrevTaux"])) {
                $MonPrev[$MonCompte["codePoste"]]["PrevTaux"] = 0;
            }

            if (!isset($MesTxMarge[$code_compte]["Taux"])) {
                $MesTxMarge[$code_compte]["Taux"] = 0;
            }

            $MaValeurPrev = $MonPrev[$MonCompte["codePoste"]]["PrevTaux"];
            $MonTxSaisi = $MesTxMarge[$code_compte]["Taux"];

            if (!$MonTxSaisi) {
                $MonTxSaisi = 0;
            }

            if ($MonTxSaisi != $MaValeurPrev) {
                if (!isset($Rem["PrevTxModifie"])) {
                    $Rem["PrevTxModifie"] = "";
                }

                $Rem["PrevTxModifie"] .= "$code_compte||#||";
            }
        }
    }

    static function Ano_MAJBase(&$Ano = [])
    {
        if (dbAcces::getDateMAJBase($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"], 2)) {
            $Ano["BALI_DATE_MAJBASE"] = true;
            return true;
        }

        return false;
    }

    static function Ano_PourcMarge(&$Ano)
    {
        $Where = array(
            "and" => array(
                "Marge" => " = 1",
                "Type" => " = 'Produits' "
            )
        );

        $Ano["StockInit"] = $Ano["StockFinal"] = $Ano["VariationStock"] = "";
        $Ano["StockFinalZero"] = $Ano["tauxsup100"] = $Ano["oublimarge"] = "";

        $SaisieSF = $PasSaisieSF = $Stock = array();

        $MoisAcuel = $_SESSION["MoisHisto"];

        // Récupération des postes
        $MesPoste = dbAcces::getPoste($Where);

        // Résultat des renseignements
        $MesResultats = dbAcces::getTauxMarge($_SESSION["station_DOS_NUM"], $MoisAcuel);

        $TotalStockFinal = 0;
        $AnneeMoins1 = StringHelper::DatePlus(str_replace('-00', '-01', $MoisAcuel), array("dateformat" => "Y-m-00", "anneesplus" => -1));

        $MesResultatsMoins1 = dbAcces::getTauxMarge($_SESSION["station_DOS_NUM"], $AnneeMoins1);
        $MesAchatsMoins1 = dbAcces::getResultatsCompte($MoisAcuel, false, true, null, array("type" => "achat", "test" => "="));
        $MesAchats = dbAcces::getResultatsCompte($MoisAcuel, false, false, null, array("type" => "achat", "test" => "="));

        foreach ($MesPoste as $codePoste => $Poste) {
            // Récup des comptes achat de chaque poste
            $MesComptes = dbAcces::getLiaisonBalPoste($codePoste, "achat");
            // parcours des comptes achat du poste
            foreach ($MesComptes as $CodeCompte => $UnCompte) {
                //oubli de taux marge si vente
                if (
                    (!isset($MesResultats[$CodeCompte]["Taux"]) || !$MesResultats[$CodeCompte]["Taux"])
                    && (!isset($MesResultats[$CodeCompte]["TauxZero"]) || !$MesResultats[$CodeCompte]["TauxZero"])
                ) {
                    // Récup des ventes
                    $ResultatVente = dbAcces::getResultatsCompte($MoisAcuel, false, false, false, array("type" => "vente", "test" => "="), false, $CodeCompte);

                    if ($ResultatVente) {
                        $Ano["oublimarge"] .= "$CodeCompte||#||";
                    }
                }

                //taux > 100%
                if (isset($MesResultats[$CodeCompte]["Taux"]) && $MesResultats[$CodeCompte]["Taux"] > 100) {
                    $Ano["tauxsup100"] .= "$CodeCompte||#||";
                }

                // Erreur stock init et final
                $TotalVarStock = 0;
                // Récup des comptes var stock
                $MesVarStock = dbAcces::getResultatsCompte($MoisAcuel, false, false, false, false, false, false, array("test" => "=", "VarStock" => $CodeCompte));

                foreach ($MesVarStock as $UneVar) {
                    $TotalVarStock += $UneVar["BAL_CUMUL"];
                }

                $TotalStock = 0;
                // Récup des stocks
                $MesStock = dbAcces::getResultatsCompte($MoisAcuel, false, false, false, false, false, false, false, array("test" => "=", "Stock" => $CodeCompte));

                foreach ($MesStock as $UnStock) {
                    $TotalStock += $UnStock["BAL_CUMUL"];
                }

                if (
                    (!isset($MesResultats[$CodeCompte]["StockFinal"]) || !$MesResultats[$CodeCompte]["StockFinal"])
                    && (!isset($MesResultats[$CodeCompte]["StockFinalZero"]) || !$MesResultats[$CodeCompte]["StockFinalZero"])
                    && !$TotalVarStock
                    && (isset($MesResultats[$CodeCompte]["StockInit"]) &&  round($TotalStock - $MesResultats[$CodeCompte]["StockInit"]) != 0)
                ) {
                    $Ano["StockInit"] .= "$CodeCompte||#||";
                }

                if (
                    (
                        (isset($MesResultats[$CodeCompte]["StockFinal"]) && $MesResultats[$CodeCompte]["StockFinal"])
                        || (isset($MesResultats[$CodeCompte]["StockFinalZero"]) && $MesResultats[$CodeCompte]["StockFinalZero"])
                    )
                    && $TotalVarStock
                    && (round($TotalStock - $MesResultats[$CodeCompte]["StockFinal"]) != 0)
                ) {
                    $Ano["StockFinal"] .= "$CodeCompte||#||";
                }


                // Erreur variation de stock
                if (isset($MesResultats[$CodeCompte]["StockFinal"]) && $MesResultats[$CodeCompte]["StockFinal"] && $TotalVarStock) {
                    $TestVar = ($MesResultats[$CodeCompte]["StockInit"] - $MesResultats[$CodeCompte]["StockFinal"] - $TotalVarStock);

                    if ($TestVar > 4 || $TestVar < -4) {
                        $Ano["VariationStock"] .= "$CodeCompte||#||";
                    }
                }

                $TotalStockFinal += $MesResultats[$CodeCompte]["StockFinal"] ?? 0;


                // Erreur pas de saisie stock final
                if (
                    (isset($MesResultats[$CodeCompte]["StockFinal"] ) && $MesResultats[$CodeCompte]["StockFinal"])
                    || (isset($MesResultats[$CodeCompte]["StockFinalZero"]) && $MesResultats[$CodeCompte]["StockFinalZero"])
                ) {
                    if (!isset($SaisieSF[$codePoste])) {
                        $SaisieSF[$codePoste] = "";
                    }

                    $SaisieSF[$codePoste] .= $CodeCompte . "||#||";
                }

                if (
                    (!isset($MesResultats[$CodeCompte]["StockFinal"]) || !$MesResultats[$CodeCompte]["StockFinal"])
                    && (!isset($MesResultats[$CodeCompte]["StockFinalZero"]) || !$MesResultats[$CodeCompte]["StockFinalZero"])
                ) {
                    if (!isset($PasSaisieSF[$codePoste])) {
                        $PasSaisieSF[$codePoste] = "";
                    }

                    $PasSaisieSF[$codePoste] .= $CodeCompte . "||#||";
                }


                // Pour erreur sur résultat
                if (
                    (isset($MesResultats[$CodeCompte]["StockFinal"] ) && $MesResultats[$CodeCompte]["StockFinal"])
                    || (isset($MesResultats[$CodeCompte]["StockFinalZero"]) && $MesResultats[$CodeCompte]["StockFinalZero"])
                ) {
                    $Stock[$CodeCompte]["StockBilan"] = $MesResultats[$CodeCompte]["StockFinal"];
                } else {
                    $MesVentesMoins1 = dbAcces::getResultatsCompte($MoisAcuel, false, true, null, array("type" => "vente", "test" => "="), null, $CodeCompte);

                    $Montant = 0;
                    foreach ($MesVentesMoins1 as $Valeurs) {
                        $Montant += $Valeurs["BAL_BALANCE"];
                    }

                    $MesVentesCompteMoins1[$CodeCompte] = $Montant;

                    $MesVentes = dbAcces::getResultatsCompte($MoisAcuel, false, false, null, array("type" => "vente", "test" => "="), null, $CodeCompte);

                    $Montant = 0;
                    foreach ($MesVentes as $Valeurs) {
                        $Montant += $Valeurs["BAL_BALANCE"];
                    }

                    $MesVentesCompte[$CodeCompte] = $Montant;

                    if (!isset($MesAchats[$CodeCompte . "UMois"]["BAL_BALANCE"])) {
                        $MesAchats[$CodeCompte . "UMois"]["BAL_BALANCE"] = 0;
                    }

                    if (!isset($MesAchatsMoins1[$CodeCompte . "UMois"]["BAL_BALANCE"])) {
                        $MesAchatsMoins1[$CodeCompte . "UMois"]["BAL_BALANCE"] = 0;
                    }

                    $MargeReelleMoins1[$CodeCompte]["MargeReelle"] = $MesVentesCompteMoins1[$CodeCompte]
                        - (
                            $MesAchatsMoins1[$CodeCompte . "UMois"]["BAL_BALANCE"]
                            + ($MesResultatsMoins1[$CodeCompte]["StockInit"] ?? 0)
                            - ($MesResultatsMoins1[$CodeCompte]["StockFinal"] ?? 0)
                        );

                    $MargeTheorique[$CodeCompte]["marge"] = $MesVentesCompte[$CodeCompte] * (($MesResultats[$CodeCompte]["Taux"] ?? 0) / 100);

                    $StockTheorique[$CodeCompte]["StockTheorique"] = $MesAchats[$CodeCompte . "UMois"]["BAL_BALANCE"]
                        + ($MesResultats[$CodeCompte]["StockInit"] ?? 0)
                        + ($MargeTheorique[$CodeCompte]["marge"] ?? 0)
                        - ($MesVentesCompte[$CodeCompte] ?? 0);

                    $Stock[$CodeCompte]["StockBilan"] = $MargeReelleMoins1[$CodeCompte]["MargeReelle"] + $StockTheorique[$CodeCompte]["StockTheorique"];
                }
            }

            if ($TotalStockFinal && (isset($PasSaisieSF[$codePoste]) && $PasSaisieSF[$codePoste])) {
                $Ano["AnoStockFinalZero"] .= $codePoste . "||#||";
            }
        }
    }

    static function Ano_Carburant(&$Ano)
    {
        $MesCarb = dbAcces::getLitrageCarb($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"], false, true);

        if (!$MesCarb) {
            $Ano["CARB"] = true;
        }
    }

    static function Ano_RensComp(&$Ano)
    {
        $MaBalImport = dbAcces::getBalImportM1($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"]);
        $MaBalImport = reset($MaBalImport);

        if ($MaBalImport["BALI_TYPE"] == "BI" || $MaBalImport["BALI_TYPE"] == "BD") {
            // Vérification si stocks finaux renseignés
            $MesPoste = dbAcces::getPoste();
            $MesResultats = dbAcces::getTauxMarge($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"]);

            foreach ($MesPoste as $codePoste => $Poste) {
                // Récupération des comptes achat de chaque poste
                $MesComptes = dbAcces::getLiaisonBalPoste($codePoste, "achat");
                // Parcours des comptes achat du poste
                foreach ($MesComptes as $CodeCompte => $UnCompte) {
                    if (!isset($MesResultats[$CodeCompte]["StockFinal"])) {
                        $Ano["StockFinalB"] .= $CodeCompte . "||#||";
                    }
                }
            }
        }
    }

    static function Ano_Bilan(&$Ano)
    {
        require_once __DIR__ . '/../Bilan/Bilan.class.php';

        $MonBilan = Bilan::getTab($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"], "", true);
        
        $ResMois = $MonBilan["MonPassif"] - $MonBilan["ActifNet"];

        if ($ResMois > 5 && $ResMois < -5) {
            $Ano["Bilan"] = $ResMois;
        }
    }

    static function Ano_Resultat(&$Ano)
    {

        //Total Produits
        $sql = "SELECT sum( BAL_CUMUL ) AS Total
                FROM Balance JOIN comptes ON comptes.code_compte = Balance.codeCompte
                WHERE balance.`DOS_NUM` = '" . $_SESSION["station_DOS_NUM"] . "'
                and comptes.CPT_VISIBLE = 1
                and `BAL_MOIS` = '" . $_SESSION["MoisHisto"] . "'
                AND comptes.numero>='7060000'";

        Database::query($sql);
        $TabProduit = Database::fetchArray();
        $TotalProduit = $TabProduit['Total'];

        //Total Charges
        $sql = "SELECT sum( BAL_CUMUL ) AS Total 
                FROM Balance JOIN comptes ON comptes.code_compte = Balance.codeCompte
                WHERE  balance.`DOS_NUM` = '" . $_SESSION["station_DOS_NUM"] . "'
                and comptes.CPT_VISIBLE = 1
                and `BAL_MOIS` = '" . $_SESSION["MoisHisto"] . "'
                AND comptes.numero>='6040000' AND comptes.numero<'7060000'  ";

        Database::query($sql);
        $TabCharge = Database::fetchArray();
        $TotalCharge = $TabCharge['Total'];

        //Total Stock Bilan et stock Init
        $Stock = dbAcces::getTauxMarge($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"], false, false, false, false, true);
        $tabIdStock = array_keys($Stock);
        $IdStock = $tabIdStock[0];
        $Stock = $Stock[$IdStock];
        $stockRetenu = (new BilanRepository())->getStockRetenu($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"]);
        $StockBilan = 0;
        foreach ($stockRetenu as $stock) {
            $StockBilan += $stock["somme"];
        }

        //Résultat théorique et réel
        $ResultatComptable = dbAcces::getResultat($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"]);
        
        $Verif = -$TotalProduit - $TotalCharge + $StockBilan - $Stock["StockInit"] - $ResultatComptable['BALI_RES'];
        $Verif = abs(round($Verif));

        if ($Verif > 5) {
            $Ano["Resultat"] = true;
        }
    }

    static function SensCompteErrone(&$Ano)
    {
        $Where = array(
            "and" => array(
                "Sens" => "!= ''"
            )
        );

        $MesCompte = dbAcces::getComptes($Where);
        $MesResultat = dbAcces::getResultatsCompte($_SESSION["MoisHisto"]);

        foreach ($MesCompte as $codeCompte => $UnCompte) {
            if (!isset($MesResultat[$codeCompte . "UMois"]["BAL_CUMUL"])) {
                $MesResultat[$codeCompte . "UMois"]["BAL_CUMUL"] = 0;
            }

            if (
                ($MesResultat[$codeCompte . "UMois"]["BAL_CUMUL"] > 0 && $UnCompte["Sens"] == "C")
                || ($MesResultat[$codeCompte . "UMois"]["BAL_CUMUL"] < 0 && $UnCompte["Sens"] == "D")
            ) {
                $Ano["AnoSens"] .= $codeCompte . "||#||";
            }
        }
    }

    static function Ano_Inventaire(&$Ano)
    {

        $MonResultat = dbAcces::getRgDivers($_SESSION["MoisHisto"], $_SESSION["station_DOS_NUM"], "dateinv");

        $DateInv = $MonResultat["dateinv"]["RGD_DATE"];

        if (!$DateInv || $DateInv == '0000-00-00') {
            $Ano["Inventaire"] = true;
        }
    }
}
