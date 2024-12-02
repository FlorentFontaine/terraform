<?php

use Helpers\StringHelper;

require_once __DIR__ .'/../dbClasses/AccesDonnees.php';
require_once __DIR__ .'/../htmlClasses/HTMLCreator.class.php';

class objectifSARL
{
    static HTMLElement $TableObjBase;

    static int $TableNumber = 1;

    /**
     * Get the Tab
     *
     * @param string $type The type of the Tab. Default is "Produits".
     * @param bool|null $Marge Flag indicating if the Tab should have Marge. Default is false.
     * @return void
     */
    static function get_Tab(string $type = "Produits", ?bool $Marge = false): void
    {
        $MoisDeb = date("Y-m-00", strtotime($_SESSION["station_DOS_DEBEX"]));
        $PosteCA = dbAcces::get_ObjectifSARL($_SESSION["station_DOS_NUM"], $type, $Marge, $MoisDeb);

        // Récupération de la structure du tableau
        $TableObj = self::generateTableau($type, $Marge);

        self::$TableNumber++;
        $TableObj->Tags["id"] = "tab_Obj" . self::$TableNumber;

        // Produits
        if ($type == "Produits") {
            if ($Marge) {
                objectifSARL::formatTab($TableObj, $PosteCA, true);
            } else {
                objectifSARL::formatTab($TableObj, $PosteCA);
            }
        } else {
            objectifSARL::formatTab($TableObj, $PosteCA);
        }

        $libelleBigTotal = $Marge ? "Marge" : $type;

        $TableObj->add_Total("TOTAL $libelleBigTotal :");

        // Résultat
        $MesResultats = dbAcces::getResultatDossierPrev($_SESSION["station_DOS_NUM"], false, true);


        // ------------------------------------------
        // Définition de la ligne finale : "Résultat"
        $LnBigTotal = new HTMLElement("tr");
        $LnBigTotal->Tags["class"] = "lntotal";

        $UnTd = new HTMLElement("td");
        $UnTd->Value = "R&eacute;sultat : ";
        $LnBigTotal->add_Children($UnTd);

        // Cellule du tableau servant au résultat global du CRP
        $UnTdResultatTotal = new HTMLElement("td");
        $LnBigTotal->add_Children($UnTdResultatTotal);

        $LnBigTotal->add_Children(new HTMLElement("td"));

        $DateCourante = $SaisonDebut = $_SESSION["station_DOS_DEBEX"];
        $SaisonFin = StringHelper::DatePlus(date("Y-m-01", strtotime($SaisonDebut)), array("moisplus" => $_SESSION["station_DOS_NBMOIS"] - 1));

        while (date("Y-m-00", strtotime($SaisonDebut)) <= date("Y-m-00", strtotime($SaisonFin))) {
            $SaisonDebutF = date("Y-m-00", strtotime($SaisonDebut));

            $UnTd = new HTMLElement("td");
            $UnTd->Value = $MesResultats[$SaisonDebutF]["resultat"];
            $LnBigTotal->add_Children($UnTd);

            $SaisonDebut = StringHelper::DatePlus($SaisonDebut, array("moisplus" => 1));
        }
        // ------------------------------------------


        $MonCrpTotProd = dbAcces::getPrev($_SESSION["station_DOS_NUM"], $DateCourante, false, true, false, false, false, 0, true, false, false, true, true);
        $MonCrpTotProdMarge = dbAcces::getPrev($_SESSION["station_DOS_NUM"], $DateCourante, false, true, false, false, false, 1, true, false, false, false, true);
        $MonCrpTotCharges = dbAcces::getPrev($_SESSION["station_DOS_NUM"], $DateCourante, false, true, false, false, false, 0, false, true, false, false, true);

        $ResultatPrev = 0;
        $ResultatPrev += round($MonCrpTotProd["Annuel"]);
        $ResultatPrev -= round($MonCrpTotCharges["Annuel"]);
        $ResultatPrev += round(round($MonCrpTotProdMarge["Annuel"]) * ($MonCrpTotProdMarge["PrevTaux"] / 100));

        // Assignation de la valeur du résultat CRP à la cellule placée plus tôt
        $UnTdResultatTotal->Value = $ResultatPrev;

        // Ajout de la ligne des totaux au tableau
        $TableObj->add_Children($LnBigTotal);

        echo $TableObj->toString();
    }

    /**
     * Generate a table element based on the given type and margin option.
     *
     * @param string $type The type of the table. "Produits" for products, "Charges" for charges.
     * @param bool|null $marge The margin option. Default is false.
     *
     * @return HTMLElement The generated table element.
     */
    private static function generateTableau(string $type, ?bool $marge = false): HTMLElement {
        // ---------------------
        // Définition du tableau
        // ---------------------

        $TableObj = new HTMLElement("table");

        if ($type == "Produits") {
            $TypePlusTitre = $marge ? "Objectif Marges" : "Objectif CA";
        } elseif ($type == "Charges") {
            $TypePlusTitre = "Objectif Charges";
        } else {
            $TypePlusTitre = "Objectif Carburant & commissions";
        }

        $TableObj->Tags = [
            "dir" => "IMP_PDF;TITLETABLE:" . $TypePlusTitre . ";ORIENTATION:LANDSCAPE;FREEZEPLAN:B5;FITHEIGHT:1;",
            "id" => "",
            "style" => "text-align:right;width:0px",
            "cellpadding" => "10",
            "cellspacing" => "0",
            "class" => "tabBalance",
            "bordercolordark" => "#000000",
            "bordercolorlight" => "#000000"
        ];

        // ---------------------
        // Définition de l'entête
        // ---------------------

        $colspan = 3 + $_SESSION["station_DOS_NBMOIS"];

        $TitleTable = " - " . StringHelper::DateComplete(str_replace("-00", "-01", $_SESSION["MoisHisto"])) . " - " . $_SESSION["station_BALI_TYPE_exp"];
        $LnTitreTable = [
            [
                "value" => strtoupper($TypePlusTitre) . $TitleTable,
                "Tags" => [
                    "colspan" => $colspan,
                    "style" => "text-align:center;font-weight:bold;border:none",
                    "class" => "EnteteTab TitreTable"
                ]
            ]
        ];

        $TRTitreTable = new HTMLElement("tr");

        foreach ($LnTitreTable as $UnChamp) {
            $UnTd = new HTMLElement("td");

            $UnTd->Value = $UnChamp["value"];
            $UnTd->Tags = $UnChamp["Tags"];

            $TRTitreTable->add_Children($UnTd);
        }

        $TableObj->add_Children($TRTitreTable);

        // ---------------------
        // Définition des header des colonnes du tableau
        // ---------------------

        $TypeStr = $marge ? "MARGE" : $type;

        $LnTitreProd = [
            [
                "value" => "<div class='div220'></div>$TypeStr",
                "Tags" => [
                    "class" => "tdfixe",
                    "width" => "150"
                ]
            ],
            [
                "value" => "<div class='div60'></div>Budget",
                "Tags" => [
                    "class" => "tdfixe",
                    "width" => "40"
                ]
            ],
            [
                "value" => "<div class='div60'></div>Taux",
                "Tags" => [
                    "class" => "tdfixe",
                    "width" => "40"
                ]
            ]
        ];

        $SaisonDebut = $_SESSION["station_DOS_DEBEX"];
        $SaisonFin = StringHelper::DatePlus(date("Y-m-01", strtotime($SaisonDebut)), array("moisplus" => $_SESSION["station_DOS_NBMOIS"] - 1));

        while (date("Y-m-00", strtotime($SaisonDebut)) <= date("Y-m-00", strtotime($SaisonFin))) {
            $SaisonDebutF = date("Y-m-00", strtotime($SaisonDebut));
            $LnTitreProd[] = [
                "value" => "<div class='div60'></div>" . StringHelper::Mysql2DateFr($SaisonDebutF),
                "Tags" => [
                    "class" => "tdfixe",
                    "width" => "40"
                ]
            ];

            $SaisonDebut = StringHelper::DatePlus($SaisonDebut, array("moisplus" => 1));
        }

        $TRTitre = new HTMLElement("tr");
        $TRTitre->Tags["class"] = "EnteteTab";

        foreach ($LnTitreProd as $UnChamp) {
            $UnTd = new HTMLElement("td");

            $UnTd->Value = $UnChamp["value"];
            $UnTd->Tags = $UnChamp["Tags"];

            $TRTitre->add_Children($UnTd);
        }

        $TableObj->add_Children($TRTitre);

        self::$TableObjBase = clone $TableObj;

        return $TableObj;
    }

    /**
     * Format the Tab
     *
     * @param HTMLElement &$TableObj The object representing the table.
     * @param array $MonTabVal The array containing the Tab values.
     * @param bool $Marge Flag indicating if the Tab should have Marge. Default is false.
     * @param bool $TotalInterm Flag indicating if the Tab should have intermediate totals. Default is false.
     * @param bool $LigneTotalFin Flag indicating if the Tab should have a final total line. Default is true.
     * @param bool $NotTotaliseTable Flag indicating if the Tab should not be totaled. Default is false.
     * @return void
     */
    static function formatTab(HTMLElement &$TableObj, array $MonTabVal, bool $Marge = false, bool $TotalInterm = false, bool $LigneTotalFin = true, bool $NotTotaliseTable = false)
    {
        $FamilleDef = $SFamilleDef = "";
        $STotal = false;
        $ChampEcho = $Marge ? "PrevTauxMontant" : "Montant";
        $lnclass = "bdlignepaireTD";

        foreach ($MonTabVal as $TabLnPeriode) {
            $lnclass = ($lnclass == "bdlignepaireTD") ? "bdligneimpaireTD" : "bdlignepaireTD";

            $ArrayLn = array_keys($TabLnPeriode);

            //Totaux + S Totaux
            if ($SFamilleDef && $SFamilleDef != $TabLnPeriode[$ArrayLn[0]]["SsFamille"] && $FamilleDef == $TabLnPeriode[$ArrayLn[0]]["Famille"]) {
                $SFamilleDefStr = objectifSARL::abrev_famille($SFamilleDef);
                $TableObj->add_STotal("Total $SFamilleDefStr :");
                $STotal = true;
            }

            if ($FamilleDef && $FamilleDef != $TabLnPeriode[$ArrayLn[0]]["Famille"]) {
                $FamilleDefStr = objectifSARL::abrev_famille($FamilleDef);

                if ($STotal) {
                    $TableObj->add_STotal("Total $SFamilleDef: ");
                }

                if (!$NotTotaliseTable) {
                    $TableObj->add_STotal("TOTAL $FamilleDefStr :", $STotal);
                } else {
                    $TableObj->add_Total("TOTAL $FamilleDefStr :", $STotal);
                }

                if ($TabLnPeriode[$ArrayLn[0]]["Famille"] == "ONFR") {
                    echo $TableObj->toString();
                    $TableObj = clone self::$TableObjBase;
                    self::$TableNumber++;
                    $TableObj->Tags["id"] = "tab_Obj" . self::$TableNumber;
                }

                $TableObj->add_Children(objectifSARL::LnFamille($TabLnPeriode[$ArrayLn[0]]["Famille"]));
            }

            if (!$FamilleDef) {
                $TableObj->add_Children(objectifSARL::LnFamille($TabLnPeriode[$ArrayLn[0]]["Famille"]));
            }

            $FamilleDef = $TabLnPeriode[$ArrayLn[0]]["Famille"];
            $SFamilleDef = $TabLnPeriode[$ArrayLn[0]]["SsFamille"];

            // ---------------------------------
            // Initialisation d'une nouvelle ligne
            $UneLigne = new HTMLElement("tr");
            $UneLigne->Tags["class"] = $lnclass;

            // ---------------------------------
            // Poste du CRP
            $UnTd = new HTMLElement("td");

            $Libelle = $TabLnPeriode[$ArrayLn[0]]["Libelle"];

            $NewLibelle = substr($Libelle, 0, 30);
            if (strlen($Libelle) > 30) {
                $NewLibelle .= "...";
            }

            $UnTd->Value = $NewLibelle;
            $UnTd->Tags["align"] = "left";
            $UneLigne->add_Children($UnTd);

            // ---------------------------------
            // Montant annuel CRP
            $UnTd = new HTMLElement("td");

            $TabLnPeriode[$ArrayLn[0]]["Annuel"] = $TabLnPeriode[$ArrayLn[0]]["Annuel"] ?? 0;
            $TabLnPeriode[$ArrayLn[0]]["PrevTaux"] = $TabLnPeriode[$ArrayLn[0]]["PrevTaux"] ?? 0;

            $UnTd->Value = round($TabLnPeriode[$ArrayLn[0]]["Annuel"]);
            if ($Marge) {
                $UnTd->Value = round($TabLnPeriode[$ArrayLn[0]]["Annuel"] * ($TabLnPeriode[$ArrayLn[0]]["PrevTaux"] / 100));
            }

            $UnTd->Tags["align"] = "right";
            $UneLigne->add_Children($UnTd);

            // ---------------------------------
            // Taux (Objectifs marge)
            $UnTd = new HTMLElement("td");

            $UnTd->Value = "";
            if ($Marge && $TabLnPeriode[$ArrayLn[0]]["PrevTaux"]) {
                $UnTd->Value = StringHelper::NombreFr($TabLnPeriode[$ArrayLn[0]]["PrevTaux"]) . " %";
            }

            $UnTd->Tags["align"] = "right";
            $UneLigne->add_Children($UnTd);

            // ---------------------------------
            // Parcours des montants
            foreach ($TabLnPeriode as $Periode => $LnPeriode) {
                $UnTd = new HTMLElement("td");
                $UnTd->Value = round($LnPeriode[$ChampEcho] ?? 0);
                $UnTd->Tags["align"] = "right";

                if ($Periode == $_SESSION["MoisHisto"]) {
                    $UnTd->Tags["style"] = "background-color:#F9DA75; border-bottom:1px solid grey;";
                }

                $UneLigne->add_Children($UnTd);
            }

            // ---------------------------------
            // Ajout de la ligne au tableau des objectifs
            $TableObj->add_Children($UneLigne);
        }

        if ($STotal) {
            $SFamilleDefStr = objectifSARL::abrev_famille($SFamilleDef);
            $TableObj->add_STotal("Total $SFamilleDefStr : ");
        }

        $FamilleDefStr = objectifSARL::abrev_famille($FamilleDef);

        if ($LigneTotalFin) {
            if (!$TotalInterm) {
                $TableObj->add_STotal("TOTAL $FamilleDefStr : ", $STotal);
            } else {
                $TableObj->add_Total("TOTAL $FamilleDefStr : ");
            }
        } else {
            $LigneTotalFin = $TableObj->add_Total("TOTAL $FamilleDefStr : ", true);
        }
    }

    /**
     * Generate a table row for a given Famille
     *
     * @param string $Famille The name of the Famille.
     * @return HTMLElement The generated table row.
     */
    static function LnFamille(string $Famille): HTMLElement
    {
        $UneLigne = new HTMLElement("tr");
        $UneLigne->Tags["class"] = "EnteteTab";

        $TdFamille = new HTMLElement("td", self::abrev_famille($Famille));
        $TdFamille->Tags["colspan"] = $_SESSION["station_DOS_NBMOIS"] + 3;
        $TdFamille->Tags["class"] = "tdflotte";
        $TdFamille->Tags["style"] = "text-align:left;padding:5px";

        $UneLigne->add_Children($TdFamille);

        return $UneLigne;
    }

    /**
     * Abbreviate the given Famille
     *
     * @param string $FamilleDef The Famille to be abbreviated
     * @return string The abbreviated Famille
     */
    static function abrev_famille(string $FamilleDef): string
    {
        $FamilleDefStr = str_replace("CHARGES PERSONNEL ET GERANCE", "CHRG. PERS. ET GER.", $FamilleDef);
        $FamilleDefStr = str_replace("AUTRES PRESTATIONS DE SERVICE", "AUTR. PREST. SERV.", $FamilleDefStr);
        $FamilleDefStr = str_replace("VENTES MARCHANDISES", "VENTES MARCH.", $FamilleDefStr);
        $FamilleDefStr = str_replace("dotation aux amortissements", "dot. aux amort.", $FamilleDefStr);
        $FamilleDefStr = str_replace("Autres services exterieurs", "Autres services ext.", $FamilleDefStr);
        $FamilleDefStr = str_replace("Autres charges de gestion", "Autres chrg. de gest.", $FamilleDefStr);
        $FamilleDefStr = str_replace("CHARGES D'EXPLOITATION", "CHARGES D'EXP.", $FamilleDefStr);
        $FamilleDefStr = str_replace("ONFR", "ACTIVITES ANNEXES", $FamilleDefStr);

        return $FamilleDefStr;
    }
}
