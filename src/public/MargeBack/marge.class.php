<?php

use Helpers\StringHelper;

require_once __DIR__ . '/../dbClasses/AccesDonnees.php';
require_once __DIR__ . '/../htmlClasses/table.php';

class Marge
{
    static function getTab($Mois, $opt = array())
    {
        $MesLignesTableau = array();
        $MesAchatsAvecRRO = $MesVentesCompte = $StockTheorique = $MargeReelle = $EcartMarge = array();

        $MesComptes = dbAcces::getComptes(null, null, null, true);

        foreach ($MesComptes as $codeCompte => $LnCpt) {
            $accountLine = array();
            $accountLine[] = array(
                str_ireplace("ACHATS ", "", substr($LnCpt["numero"], strlen($LnCpt["numero"]) - 4) . " - " . $LnCpt["libelle"])
                => array("libelle" => 1, "align" => "left")
            );
            $MesLignesTableau[$codeCompte] = $accountLine;
        }

        $Total = array(
            'StockInit' => 0,
            'Achats' => 0,
            'Ventes' => 0,
            'MargeTheorique' => 0,
            'StockThéorique' => 0,
            'MargeReelle' => 0,
            'TxMargeReelle' => 0,
            'StockFinal' => 0,
            'EcartMarge' => 0,
            'EcartMargeReelle' => 0,
            'StockBilan' => 0
        );

        $typeCptAchat = array("type" => "achat", "test" => "=");
        $typeCptVente = array("type" => "vente", "test" => "=");

        $allAccountsID = array_keys($MesLignesTableau);

        $MesAchats = dbAcces::getResultatsCompte($Mois, false, false, null, $typeCptAchat, array("RRRO" => 0, "test" => "="));
        $MesVentes = dbAcces::getResultatsCompte($Mois, false, false, null, $typeCptVente, null, $allAccountsID);
        $MesRRRO = dbAcces::getResultatsCompte($Mois, false, false, null, $typeCptAchat, array("RRRO" => $allAccountsID, "test" => "IN"));
        $MesResultats = dbAcces::getTauxMarge($_SESSION["station_DOS_NUM"], $Mois);
        $MargeTheorique = dbAcces::getMargeTheorique($allAccountsID, $Mois);

        foreach ($MesLignesTableau as $CodeLigne => &$UneLigneDb) {
            // ----------------
            // Stocks initiaux

            $stockInit = 0;
            if (isset($MesResultats[$CodeLigne]["StockInit"]) && $MesResultats[$CodeLigne]["StockInit"]) {
                $stockInit = $MesResultats[$CodeLigne]["StockInit"];
            } else {
                $MesResultats[$CodeLigne]["StockInit"] = 0;
            }

            $Total["StockInit"] += $stockInit;

            $UneLigneDb[] = array(
                StringHelper::NombreFr($stockInit) => array("align" => "right")
            );

            // ----------------
            // Achats

            $Montant = 0;
            if (!empty($MesAchats[$CodeLigne . "UMois"])) {
                $Montant = $MesAchats[$CodeLigne . "UMois"]["BAL_CUMUL"];
            }

            if (!empty($MesRRRO[$CodeLigne . "UMois"])) {
                $Montant += $MesRRRO[$CodeLigne . "UMois"]["BAL_CUMUL"];
            }

            $MesAchatsAvecRRO[$CodeLigne] = $Montant;
            $Total["Achats"] += $Montant;

            $UneLigneDb[] = array(
                StringHelper::NombreFr($Montant) => array("align" => "right")
            );

            // ----------------
            // Ventes

            $Montant = 0;
            if (!empty($MesVentes[$CodeLigne . "UMois"])) {
                $Montant = -$MesVentes[$CodeLigne . "UMois"]["BAL_CUMUL"];
            }

            $MesVentesCompte[$CodeLigne] = $Montant;
            $Total["Ventes"] += $Montant;

            $UneLigneDb[] = array(
                StringHelper::NombreFr($Montant) => array("align" => "right")
            );

            // ----------------
            // Marge théorique

            $Montant = 0;
            if (isset($MargeTheorique[$CodeLigne]) && $MargeTheorique[$CodeLigne]) {
                $Montant = $MargeTheorique[$CodeLigne];
            } else {
                $MargeTheorique[$CodeLigne] = 0;
            }

            $Total["MargeTheorique"] += $Montant;

            $UneLigneDb[] = array(
                StringHelper::NombreFr($Montant) => array("align" => "right")
            );

            // ----------------
            // Taux de marge théorique

            $calculTaux = 0;
            if (isset($MesVentesCompte[$CodeLigne]) && $MesVentesCompte[$CodeLigne] != 0) {
                $calculTaux = $MargeTheorique[$CodeLigne] / $MesVentesCompte[$CodeLigne];
            }

            $Taux = StringHelper::NombreFr($calculTaux * 100, 2, false, true);
            $Taux .= ($Taux) ? " %" : "";

            $UneLigneDb[] = array(
                $Taux => array("align" => "right")
            );

            // ----------------
            // stock théorique

            $StockTheorique[$CodeLigne]["StockThéorique"] = $MesAchatsAvecRRO[$CodeLigne]
                + $MesResultats[$CodeLigne]["StockInit"]
                - $MesVentesCompte[$CodeLigne]
                + $MargeTheorique[$CodeLigne];

            if (isset($opt["updEcartMarge"]) && $opt["updEcartMarge"]) {
                dbAcces::setStockTheorique($_SESSION["station_DOS_NUM"], $Mois, $CodeLigne, round($StockTheorique[$CodeLigne]["StockThéorique"], 2));
            }

            $Total["StockThéorique"] += $StockTheorique[$CodeLigne]["StockThéorique"];

            $UneLigneDb[] = array(
                StringHelper::NombreFr($StockTheorique[$CodeLigne]["StockThéorique"]) => array("align" => "right")
            );

            // ----------------
            // Marge réelle

            $MargeReelle[$CodeLigne]["MargeReelle"] = 0;
            if (
                (isset($MesResultats[$CodeLigne]["StockFinalZero"]) && $MesResultats[$CodeLigne]["StockFinalZero"])
                || (isset($MesResultats[$CodeLigne]["StockFinal"]) && $MesResultats[$CodeLigne]["StockFinal"])
            ) {
                $MargeReelle[$CodeLigne]["MargeReelle"] =
                    ($MesResultats[$CodeLigne]["StockFinal"] - $StockTheorique[$CodeLigne]["StockThéorique"])
                    + $MargeTheorique[$CodeLigne];
            }

            if (isset($opt["updEcartMarge"]) && $opt["updEcartMarge"]) {
                if ($MesResultats[$CodeLigne]["StockFinalZero"] || $MesResultats[$CodeLigne]["StockFinal"]) {
                    $Taux = round((round(($MargeReelle[$CodeLigne]["MargeReelle"] / $MesVentesCompte[$CodeLigne]), 2) * 100), 2);
                } else {
                    $Taux = 0;
                }

                dbAcces::setTauxReelCompte($_SESSION["station_DOS_NUM"], $Mois, $CodeLigne, $Taux);
            }

            $Total["MargeReelle"] += $MargeReelle[$CodeLigne]["MargeReelle"];

            $UneLigneDb[] = array(
                StringHelper::NombreFr($MargeReelle[$CodeLigne]["MargeReelle"]) => array("align" => "right")
            );

            // ----------------
            // Taux de marge réelle

            $calculTaux = 0;
            if (isset($MesVentesCompte[$CodeLigne]) && $MesVentesCompte[$CodeLigne] != 0) {
                $calculTaux = $MargeReelle[$CodeLigne]["MargeReelle"] / $MesVentesCompte[$CodeLigne];
            }

            $Taux = StringHelper::NombreFr($calculTaux * 100, 2, false, true);
            $Taux .= ($Taux) ? " %" : "";

            $Total["TxMargeReelle"] += $calculTaux;

            $UneLigneDb[] = array(
                $Taux => array("align" => "right")
            );

            // ----------------
            // Stock réel (stock final)

            $Montant = 0;
            if (isset($MesResultats[$CodeLigne]["StockFinalZero"]) && $MesResultats[$CodeLigne]["StockFinalZero"]) {
                $Montant = "0,00";
            } elseif (isset($MesResultats[$CodeLigne]["StockFinal"]) && $MesResultats[$CodeLigne]["StockFinal"]) {
                $Montant = StringHelper::NombreFr($MesResultats[$CodeLigne]["StockFinal"]);
            }

            $Total["StockFinal"] += $Montant;

            $UneLigneDb[] = array(
                StringHelper::NombreFr($Montant) => array("align" => "right")
            );

            // ----------------
            // Ecart sur marge (Stock réel - Stock théorique)

            $EcartMarge[$CodeLigne]["EcartMarge"] = 0;
            if (
                (isset($MesResultats[$CodeLigne]["StockFinalZero"]) && $MesResultats[$CodeLigne]["StockFinalZero"])
                || (isset($MesResultats[$CodeLigne]["StockFinal"]) && $MesResultats[$CodeLigne]["StockFinal"])
            ) {
                $EcartMarge[$CodeLigne]["EcartMarge"] = $MesResultats[$CodeLigne]["StockFinal"] - $StockTheorique[$CodeLigne]["StockThéorique"];
            }

            if (isset($opt["updEcartMarge"]) && $opt["updEcartMarge"]) {
                Marge::UpdateEcartMarge($Mois, $CodeLigne, $EcartMarge[$CodeLigne]["EcartMarge"], $MesResultats[$CodeLigne]);
            }

            $Total["EcartMarge"] += $EcartMarge[$CodeLigne]["EcartMarge"];

            $UneLigneDb[] = array(
                StringHelper::NombreFr($EcartMarge[$CodeLigne]["EcartMarge"]) => array("align" => "right")
            );

            // ----------------
            // Ecart sur marge réelle

            $Ecart[$CodeLigne]["MargeReelle"] = 0;
            if (isset($MesResultats[$CodeLigne]["EcartMargePrec"]) && $MesResultats[$CodeLigne]["EcartMargePrec"]) {
                $Ecart[$CodeLigne]["MargeReelle"] = $MesResultats[$CodeLigne]["EcartMargePrec"];
            }

            $Total["EcartMargeReelle"] += $Ecart[$CodeLigne]["MargeReelle"];

            $UneLigneDb[] = array(
                StringHelper::NombreFr($Ecart[$CodeLigne]["MargeReelle"]) => array("align" => "right")
            );

            // ----------------
            // Stock sur bilan

            if (
                (isset($MesResultats[$CodeLigne]["StockFinalZero"]) && $MesResultats[$CodeLigne]["StockFinalZero"])
                || (isset($MesResultats[$CodeLigne]["StockFinal"]) && $MesResultats[$CodeLigne]["StockFinal"])
            ) {
                $Stock[$CodeLigne]["StockBilan"] = $MesResultats[$CodeLigne]["StockFinal"];
            } else {
                $Stock[$CodeLigne]["StockBilan"] = $Ecart[$CodeLigne]["MargeReelle"] + $StockTheorique[$CodeLigne]["StockThéorique"];
            }

            $Total["StockBilan"] += $Stock[$CodeLigne]["StockBilan"];

            if (isset($opt["updEcartMarge"]) && $opt["updEcartMarge"]) {
                dbAcces::setStockRetenuBilan($_SESSION["station_DOS_NUM"], $Mois, $CodeLigne, round($Stock[$CodeLigne]["StockBilan"], 2));
            }

            $UneLigneDb[] = array(
                StringHelper::NombreFr($Stock[$CodeLigne]["StockBilan"]) => array("align" => "right")
            );
        }

        $UneLigneTableau = array();
        $UneLigneTableau[] = "TOTAL : ";
        $UneLigneTableau[] = array(StringHelper::NombreFr($Total["StockInit"]) => array("style" => "text-align:right;"));
        $UneLigneTableau[] = array(StringHelper::NombreFr($Total["Achats"]) => array("style" => "text-align:right;"));
        $UneLigneTableau[] = array(StringHelper::NombreFr($Total["Ventes"]) => array("style" => "text-align:right;"));
        $UneLigneTableau[] = array(StringHelper::NombreFr($Total["MargeTheorique"]) => array("style" => "text-align:right;"));
        $UneLigneTableau[] = array("" => array("style" => "text-align:right;font-size:12px;"));
        $UneLigneTableau[] = array(StringHelper::NombreFr($Total["StockThéorique"]) => array("style" => "text-align:right;"));
        $UneLigneTableau[] = array(StringHelper::NombreFr($Total["MargeReelle"]) => array("style" => "text-align:right;"));
        $Total["TxMargeReelle"] = ($Total["MargeReelle"] / $Total["Ventes"]) * 100;
        $Total["TxMargeReelle"] = $Total["TxMargeReelle"] != 0 ? $Total["TxMargeReelle"] . " %" : 0;
        $UneLigneTableau[] = array(StringHelper::NombreFr($Total["TxMargeReelle"], 2, false, true) => array("style" => "text-align:right;"));
        $UneLigneTableau[] = array(StringHelper::NombreFr($Total["StockFinal"]) => array("style" => "text-align:right;"));
        $UneLigneTableau[] = array(StringHelper::NombreFr($Total["EcartMarge"]) => array("style" => "text-align:right;"));
        $UneLigneTableau[] = array(StringHelper::NombreFr($Total["EcartMargeReelle"]) => array("style" => "text-align:right;"));
        $UneLigneTableau[] = array(StringHelper::NombreFr($Total["StockBilan"]) => array("style" => "text-align:right;"));
        $MesLignesTableau["BIGTOTAL"] = $UneLigneTableau;

        return $MesLignesTableau;
    }


    static function UpdateEcartMarge($MoisDeb, $MonCodeCompte, $MontantEcart, $MonResultatLn)
    {
        dbAcces::setEcartMarge($_SESSION["station_DOS_NUM"], $MoisDeb, $MonCodeCompte, round($MontantEcart, 2));

        if ($MonResultatLn["StockFinalZero"] || $MonResultatLn["StockFinal"]) {
            $MontantEcartPrec = $MontantEcart;
        } else {
            $MontantEcartPrec = $MonResultatLn["EcartMargePrec"];
        }

        //mise à jour ecart marge prec à partir du mois actuel + 1 jusqu'à la derniere balance importŽe
        $MoisFin = str_replace("-00", "-01", station::GetLastBal($_SESSION["station_DOS_NUM"]));
        $MoisDeb = str_replace("-00", "-01", $MoisDeb);

        $breakafter = false;

        $MoisDeb = StringHelper::DatePlus($MoisDeb, array("moisplus" => 1));

        while (strtotime($MoisDeb) <= strtotime($MoisFin)) {
            $Moiscourant = date("Y-m-00", strtotime($MoisDeb));
            $ResultCourant = dbAcces::getTauxMarge($_SESSION["station_DOS_NUM"], $Moiscourant, false, $MonCodeCompte);
            $ResultCourant = $ResultCourant[$MonCodeCompte];

            foreach ($ResultCourant as $cle => &$Montant) {
                if ($cle == "EcartMarge" && ($Montant || $ResultCourant["StockFinalZero"] == 1 || $ResultCourant["StockFinal"] > 0)) {
                    $breakafter = true;
                }

                if (($cle == "EcartMargePrec" && !$ResultCourant["StockFinalZero"] && !$ResultCourant["StockFinal"]) || $breakafter) {
                    $Montant = $MontantEcartPrec;
                }
            }

            dbAcces::setEcartMargePrec($_SESSION["station_DOS_NUM"], $Moiscourant, $MonCodeCompte, round($ResultCourant["EcartMargePrec"], 2));

            if ($breakafter) {
                break;
            }

            $MoisDeb = StringHelper::DatePlus($MoisDeb, array("moisplus" => 1));
        }
    }
}
