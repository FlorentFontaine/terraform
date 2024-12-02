<?php
use Helpers\StringHelper;

require_once('../dbClasses/AccesDonnees.php');
require_once('../compChargesBack/compCharges.class.php');

class synthese
{
    private static $allcompte;


    static function getTab($Type, $MoisActuel, $FamilleSelect = false, $SsFamilleSelect = false, $codePosteSelect = false, $opt = null)
    {
        self::$allcompte = true;

        //pour la récupération des résultats avec tous les comptes de la balance sauf les comptes d'achat
        $TypeCompte = array("test" => "!=", "type" => "achat");

        $Where = array(
            "and" =>
                array(

                    "compteposte_synthese.type" => "='$Type' "

                )
        );

        $ONFR = false;

        if ($Type != "ONFR") {
            $Where["and"]["compteposte_synthese.Famille"] = "!='ONFR' ";
        } else {
            $Where["and"] = array();
            $Where["and"]["compteposte_synthese.Famille"] = "='ONFR' ";
            $Type = "";
            $ONFR = true;
        }

        $tri = array(
            "ordre" => "ASC"
        );

        $MesPostes = dbAcces::getPosteSyntheseVisible($Where, $tri, false, $opt["cluster"]);

        $MesLignesTableau = &compChargesProd::IniTableau($MesPostes, false, true, false, true, false, 1, false, false, false);

        //mise en place des résultats + totauxs + sTotaux

        $NbMoisEcart = StringHelper::GetNbMoisEcart(date("Ym", strtotime($_SESSION["station_DOS_DEBEX"])), date("Ym", strtotime(str_replace('-00', '-01', $MoisActuel))));

        if ($_SESSION["station_DOS_PREMDATECP"] > 0) {
            $MoisAnneePrec = StringHelper::DatePlus(str_replace("-00", "-01", $_SESSION["station_DOS_PREMDATECP"]), array("moisplus" => $NbMoisEcart, "dateformat" => "Y-m-00"));
        } else {
            $MoisAnneePrec = StringHelper::DatePlus(str_replace("-00", "-01", $MoisActuel), array("moisplus" => -12, "dateformat" => "Y-m-00"));
        }

        $MesResultats = dbAcces::getResultatsPoste_synthese($MoisActuel, true, true, true, false, $codePosteSelect, $Type, $TypeCompte, false, false, false, $Contenu, $opt["cluster"]);//$opt["MAJBASE"] pour ajouter le prevu agip
        $MesResultatsCumul = dbAcces::getResultatsPoste_synthese($MoisActuel, true, true, true, true, $codePosteSelect, $Type, $TypeCompte, false, false, false, $Rien, $opt["cluster"]);
        $MonPrev = dbAcces::getPrev($_SESSION["station_DOS_NUM"], $MoisActuel, $Type, false, false, false, false, false, false, false, false, false, false, $opt["cluster"]);
        $MonPrevCumul = dbAcces::getPrev($_SESSION["station_DOS_NUM"], $MoisActuel, $Type, true, $codePosteMarge, false, false, false, false, false, false, false, true, $opt["cluster"]);
        $LI_Poste_PosteSynthese = dbAcces::getPosteByPosteSynthese();
        $MesFamille = dbAcces::getFamilleSFamilleSynthese();

        if ($Type == "Produits" || $ONFR) {
            $MesMarges = dbAcces::getMargeTheoriqueGroupe($MoisActuel, $_SESSION["station_DOS_NUM"], false, false, true, $opt["cluster"]);
            $MesMargesN1 = dbAcces::getMargeTheoriqueGroupe($MoisActuel, NULL, false, true, true, $opt["cluster"]);
            $MesMargesCumul = dbAcces::getMargeTheoriqueGroupe($MoisActuel, $_SESSION["station_DOS_NUM"], true, false, true, $opt["cluster"]);
            $MesMargesCumulN1 = dbAcces::getMargeTheoriqueGroupe($MoisActuel, NULL, true, true, true, $opt["cluster"]);
        }


        $TotalCharges = NULL;
        $LnProduitsONFR = false;


        foreach ($MesLignesTableau as $codeCompte => &$UneLigneDb) {

            $LnProduitsONFR = false;

            if ($MesPostes[$codeCompte]["Famille"] == "ONFR" && $MesPostes[$codeCompte]["Type"] == "Produits") {
                $codeCompte = "CMARGE" . $codeCompte;
                $LnProduitsONFR = true;
            }

            if ($codeCompte == 23) { //MISE EN PLACE LIGNE VOLUME CARBURANT (compte synthese numero 24)


                $rea = dbAcces::getLitrageCarb($_SESSION["station_DOS_NUM"], $MoisActuel, true, true, false, false, false, true);
                $anm1 = dbAcces::getLitrageCarb($_SESSION["station_DOS_NUM"], $MoisActuel, true, true, true, false, false, true);
                $prev = dbAcces::getLitrageCarbPrev($_SESSION["station_DOS_NUM"], $MoisActuel, true, true, false, false, false, true);

                $reaC = dbAcces::getLitrageCarb($_SESSION["station_DOS_NUM"], $MoisActuel, true, false, false, false, false, true);
                $anm1C = dbAcces::getLitrageCarb($_SESSION["station_DOS_NUM"], $MoisActuel, true, false, true, false, false, true);
                $prevC = dbAcces::getLitrageCarbPrev($_SESSION["station_DOS_NUM"], $MoisActuel, true, false, false, false, false, true);

                $EcartPrevCarb = $reaC - $prevC;

                if ($anm1C)
                    $EcartN1Carb = $reaC - $anm1C;

                $UneLigneDb[] = "";
                $UneLigneDb[] = array(StringHelper::NombreFr($rea, 0) => array('align' => "right"));
                $UneLigneDb[] = array(StringHelper::NombreFr($prev, 0) => array('align' => "right"));
                $UneLigneDb[] = array(StringHelper::NombreFr($anm1, 0) => array('align' => "right"));
                $UneLigneDb[] = "";
                $UneLigneDb[] = array(StringHelper::NombreFr($reaC, 0) => array('align' => "right"));
                $UneLigneDb[] = array(StringHelper::NombreFr($prevC, 0) => array('align' => "right"));
                $UneLigneDb[] = array(StringHelper::NombreFr($anm1C, 0) => array('align' => "right"));
                $UneLigneDb[] = "";
                if ($prevC)
                    $UneLigneDb[] = array(StringHelper::NombreFr($EcartPrevCarb, 0) => array('align' => "right"));
                else
                    $UneLigneDb[] = array("" => array('align' => "right"));

                if ($anm1C)
                    $UneLigneDb[] = array(StringHelper::NombreFr($EcartRN1Carb, 0) => array('align' => "right"));
                else
                    $UneLigneDb[] = array("" => array('align' => "right"));
            } elseif (is_numeric($codeCompte)) {
                //c'est une ligne d'un poste
                //echo StringHelper::NombreFr($MesResultats[$codeCompte."||#||"."UMoisRealise"]["Montant"])."<br/>";


                $rea = $MesResultats[$codeCompte . "||#||" . "UMoisRealise"]["Montant"];
                $anm1 = $MesResultats[$codeCompte . "||#||" . "UMoisAnneeMoinsUn"]["Montant"];
                $prev = $MesResultats[$codeCompte . "||#||" . "UMoisPrevu"]["Montant"];

                $reaC = $MesResultatsCumul[$codeCompte . "||#||" . "UMoisRealise"]["Montant"];
                $anm1C = $MesResultatsCumul[$codeCompte . "||#||" . "UMoisAnneeMoinsUn"]["Montant"];
                $prevC = $MesResultatsCumul[$codeCompte . "||#||" . "UMoisPrevu"]["Montant"];


                if ($MesFamille[$codeCompte]["Type"] == "Produits") {
                    $rea = -$rea;
                    $anm1 = -$anm1;

                    $reaC = -$reaC;
                    $anm1C = -$anm1C;
                }


                $UneLigneDb[] = "";
                $UneLigneDb[] = array(StringHelper::NombreFr($rea) => array("align" => "right"));
                $UneLigneDb[] = array(StringHelper::NombreFr($prev) => array("align" => "right"));
                $UneLigneDb[] = array(StringHelper::NombreFr($anm1) => array("align" => "right"));


                $UneLigneDb[] = "";

                $UneLigneDb[] = array(StringHelper::NombreFr($reaC) => array("align" => "right"));
                $UneLigneDb[] = array(StringHelper::NombreFr($prevC) => array("align" => "right"));
                $UneLigneDb[] = array(StringHelper::NombreFr($anm1C) => array("align" => "right"));

                $UneLigneDb[] = "";

                $StyleRea = array("align" => "right");

                if ($Contenu["UMoisPrevu"])
                    $UneLigneDb[] = array(StringHelper::Signe($reaC - $prevC) => $StyleRea);
                else
                    $UneLigneDb[] = array("" => $StyleRea);


                if ($_SESSION["station_DOS_PREMDATECP"] > 0) {

                    $StyleRea = array("align" => "right");

                    $UneLigneDb[] = array(StringHelper::Signe($reaC - $anm1C) => $StyleRea);
                } else
                    $UneLigneDb[] = array("" => $StyleRea);


                if ($MesFamille[$codeCompte]["Type"] == "Charges" && $MesFamille[$codeCompte]["Famille"] == "ONFR") {
                    $rea = -$rea;
                    $prev = -$prev;
                    $anm1 = -$anm1;

                    $reaC = -$reaC;
                    $anm1C = -$anm1C;
                    $prevC = -$prevC;
                }


                $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||2"] += $rea;
                $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||3"] += $prev;
                $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||4"] += $anm1;
                $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||2"] += $rea;
                $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||3"] += $prev;
                $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||4"] += $anm1;

                $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||6"] += $reaC;
                $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||7"] += $prevC;
                $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||8"] += $anm1C;
                $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||6"] += $reaC;
                $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||7"] += $prevC;
                $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||8"] += $anm1C;


                if ($Contenu["UMoisPrevu"]) {
                    $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||10"] += ($reaC - $prevC);
                    $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||10"] += ($reaC - $prevC);
                }

                if ($_SESSION["station_DOS_PREMDATECP"] > 0) {
                    $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||11"] += ($reaC - $anm1C);
                    $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||11"] += ($reaC - $anm1C);
                }

                if (!$rea && !$prev && !$anm1 && !$reaC && !$prevC && !$anm1C && !self::$allcompte)
                    $UneLigneDb = NULL;


            } elseif (strpos($codeCompte, "CMARGE") !== false) {
                //MARGE////////////////


                //---| Recup des valeurs |----------------------------------------------------------------------------------------------------------------//

                $codePosteMarge = str_replace("CMARGE", "", $codeCompte);


                $CompteBalance["Poste" . $codePosteMarge] = dbAcces::getLiaisonBalPoste(NULL, "achat", NULL, $codePosteMarge);

                $TotalMarge["Poste" . $codePosteMarge] = 0;
                $TotalMarge["Poste" . $codePosteMarge] += $MesMarges[$codePosteMarge]["Montant"];


                $TotalMargeN1["Poste" . $codePosteMarge] += $MesMargesN1[$codePosteMarge]["Montant"];

                foreach ($LI_Poste_PosteSynthese[$codePosteMarge] as $UnCodePoste => $Liaison)
                    $TotalMargePrev["Poste" . $codePosteMarge] += ($MonPrev[$UnCodePoste]["PrevTauxMontant"]);

                $TotalMargeCumul["Poste" . $codePosteMarge] += $MesMargesCumul[$codePosteMarge]["Montant"];
                $TotalMargeCumulN1["Poste" . $codePosteMarge] += $MesMargesCumulN1[$codePosteMarge]["Montant"];

                foreach ($LI_Poste_PosteSynthese[$codePosteMarge] as $UnCodePoste => $Liaison)
                    $TotalMargePrevCumul["Poste" . $codePosteMarge] += $MonPrevCumul[$UnCodePoste]["PrevTauxMontant"];


                $MonEcartMarge = dbAcces::getEcartMarge($_SESSION["station_DOS_NUM"], $MoisActuel, false, false, false, $codePosteMarge, $opt["cluster"]);
                $MonEcartMargeN1 = dbAcces::getEcartMarge($_SESSION["station_DOS_NUMPREC"], $MoisAnneePrec, false, false, false, $codePosteMarge, $opt["cluster"]);


                $MonEcartMargeCumul = dbAcces::getEcartMarge($_SESSION["station_DOS_NUM"], $MoisActuel, false, false, true, $codePosteMarge, $opt["cluster"]);
                $MonEcartMargeN1Cumul = dbAcces::getEcartMarge($_SESSION["station_DOS_NUMPREC"], $MoisAnneePrec, false, false, true, $codePosteMarge, $opt["cluster"]);


                $TotalMarge["Poste" . $codePosteMarge] += $MonEcartMarge;
                $TotalMargeN1["Poste" . $codePosteMarge] += $MonEcartMargeN1;

                $TotalMargeCumul["Poste" . $codePosteMarge] += $MonEcartMargeCumul;
                $TotalMargeCumulN1["Poste" . $codePosteMarge] += $MonEcartMargeN1Cumul;


                //---| insertion ds tableau |----------------------------------------------------------------------------------------------------------------//


                $UneLigneDb[] = "";

                $UneLigneDb[] = array(StringHelper::NombreFr($TotalMarge["Poste$codePosteMarge"]) => array("align" => "right"));
                $UneLigneDb[] = array(StringHelper::NombreFr($TotalMargePrev["Poste$codePosteMarge"]) => array("align" => "right"));
                $UneLigneDb[] = array(StringHelper::NombreFr($TotalMargeN1["Poste$codePosteMarge"]) => array("align" => "right"));

                $UneLigneDb[] = "";

                $UneLigneDb[] = array(StringHelper::NombreFr($TotalMargeCumul["Poste$codePosteMarge"]) => array("align" => "right"));
                $UneLigneDb[] = array(StringHelper::NombreFr($TotalMargePrevCumul["Poste$codePosteMarge"]) => array("align" => "right"));
                $UneLigneDb[] = array(StringHelper::NombreFr($TotalMargeCumulN1["Poste$codePosteMarge"]) => array("align" => "right"));

                $UneLigneDb[] = "";

                if ($Contenu["UMoisPrevu"])
                    $UneLigneDb[] = array(StringHelper::Signe($TotalMargeCumul["Poste$codePosteMarge"] - $TotalMargePrevCumul["Poste$codePosteMarge"]) => array("align" => "right"));
                else
                    $UneLigneDb[] = array("" => $StyleRea);

                if ($_SESSION["station_DOS_PREMDATECP"] > 0)
                    $UneLigneDb[] = array(StringHelper::Signe($TotalMargeCumul["Poste$codePosteMarge"] - $TotalMargeCumulN1["Poste$codePosteMarge"]) => array("align" => "right"));
                else
                    $UneLigneDb[] = array("" => $StyleRea);


                $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||2"] += $TotalMarge["Poste$codePosteMarge"];
                $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||3"] += $TotalMargePrev["Poste$codePosteMarge"];
                $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||4"] += $TotalMargeN1["Poste$codePosteMarge"];

                $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||2"] += $TotalMarge["Poste$codePosteMarge"];;
                $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||3"] += $TotalMargePrev["Poste$codePosteMarge"];
                $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||4"] += $TotalMargeN1["Poste$codePosteMarge"];


                $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||6"] += $TotalMargeCumul["Poste$codePosteMarge"];
                $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||7"] += $TotalMargePrevCumul["Poste$codePosteMarge"];
                $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||8"] += $TotalMargeCumulN1["Poste$codePosteMarge"];

                $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||6"] += $TotalMargeCumul["Poste$codePosteMarge"];;
                $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||7"] += $TotalMargePrevCumul["Poste$codePosteMarge"];
                $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||8"] += $TotalMargeCumulN1["Poste$codePosteMarge"];

                if ($Contenu["UMoisPrevu"]) {
                    $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||10"] += ($TotalMargeCumul["Poste$codePosteMarge"] - $TotalMargePrevCumul["Poste$codePosteMarge"]);
                    $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||10"] += ($TotalMargeCumul["Poste$codePosteMarge"] - $TotalMargePrevCumul["Poste$codePosteMarge"]);
                }

                if ($_SESSION["station_DOS_PREMDATECP"] > 0) {
                    $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||11"] += ($TotalMargeCumul["Poste$codePosteMarge"] - $TotalMargeCumulN1["Poste$codePosteMarge"]);
                    $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||11"] += ($TotalMargeCumul["Poste$codePosteMarge"] - $TotalMargeCumulN1["Poste$codePosteMarge"]);
                }


                if ($LnProduitsONFR) {

                    $TotauxF["TOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||2"] += $TotalMarge["Poste$codePosteMarge"];
                    $TotauxF["TOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||3"] += $TotalMargePrev["Poste$codePosteMarge"];
                    $TotauxF["TOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||4"] += $TotalMargeN1["Poste$codePosteMarge"];
                    $TotauxSF["STOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||2"] += $TotalMarge["Poste$codePosteMarge"];
                    $TotauxSF["STOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||3"] += $TotalMargePrev["Poste$codePosteMarge"];
                    $TotauxSF["STOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||4"] += $TotalMargeN1["Poste$codePosteMarge"];

                    $TotauxF["TOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||6"] += $TotalMargeCumul["Poste$codePosteMarge"];
                    $TotauxF["TOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||7"] += $TotalMargePrevCumul["Poste$codePosteMarge"];
                    $TotauxF["TOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||8"] += $TotalMargeCumulN1["Poste$codePosteMarge"];
                    $TotauxSF["STOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||6"] += $TotalMargeCumul["Poste$codePosteMarge"];
                    $TotauxSF["STOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||7"] += $TotalMargePrevCumul["Poste$codePosteMarge"];
                    $TotauxSF["STOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||8"] += $TotalMargeCumulN1["Poste$codePosteMarge"];


                    if ($Contenu["UMoisPrevu"]) {
                        $TotauxSF["STOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||10"] += ($TotalMargeCumul["Poste$codePosteMarge"] - $TotalMargePrevCumul["Poste$codePosteMarge"]);
                        $TotauxF["TOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||10"] += ($TotalMargeCumul["Poste$codePosteMarge"] - $TotalMargePrevCumul["Poste$codePosteMarge"]);
                    }

                    if ($_SESSION["station_DOS_PREMDATECP"] > 0) {
                        $TotauxF["TOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||11"] += ($TotalMargeCumul["Poste$codePosteMarge"] - $TotalMargeCumulN1["Poste$codePosteMarge"]);
                        $TotauxSF["STOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||11"] += ($TotalMargeCumul["Poste$codePosteMarge"] - $TotalMargeCumulN1["Poste$codePosteMarge"]);
                    }
                }

                if (!$TotalMarge["Poste$codePosteMarge"] && !$TotalMargePrev["Poste$codePosteMarge"] && !$TotalMargeN1["Poste$codePosteMarge"] && !$TotalMargeCumul["Poste$codePosteMarge"] && !$TotalMargePrevCumul["Poste$codePosteMarge"] && !$TotalMargeCumulN1["Poste$codePosteMarge"] && !self::$allcompte)
                    $UneLigneDb = NULL;


            } elseif (strpos($codeCompte, "TITRE") !== false) {
                $UneLigneDb[] = "";
                $UneLigneDb[] = array("" => array('style' => "border-right:none"));
                $UneLigneDb[] = array("" => array('style' => "border-right:none"));
                $UneLigneDb[] = array("" => "");
                $UneLigneDb[] = "";
                $UneLigneDb[] = array("" => array('style' => "border-right:none"));
                $UneLigneDb[] = array("" => array('style' => "border-right:none"));
                $UneLigneDb[] = array("" => "");
                $UneLigneDb[] = "";
                $UneLigneDb[] = array("" => array('style' => "border-right:none;"));
                $UneLigneDb[] = array("" => array('style' => "border-left:none;"));
            }

        }


        //TOTAUX


        $col = 11;


        if ($opt['mensuel']) {
            $array1 = array(1 => array("" => array("class" => "colvide")), 2 => "", 3 => "", 4 => "", 5 => array("" => array("class" => "colvide")), 6 => "", 7 => "");
        } elseif ($opt['cumul']) {
            $array1 = array(1 => array("" => array("class" => "colvide")), 2 => "", 3 => "", 4 => "", 5 => array("" => array("class" => "colvide")), 6 => "", 7 => "", 8 => "", 9 => array("" => array("class" => "colvide")), 10 => "", 11 => "");
        } else {
            $array1 = array(1 => array("" => array("class" => "colvide")), 2 => "", 3 => "", 4 => "", 5 => array("" => array("class" => "colvide")), 6 => "", 7 => "", 8 => "", 9 => array("" => array("class" => "colvide")), 10 => "", 11 => "");
        }

        foreach ($TotauxF as $codeLigneTableau => $Valeur) {
            //recherche de la ligne total pour lui affecter les valeurs
            $tab = explode("||col||", $codeLigneTableau);

            $MaCleTab1 = $tab[0];
            $Position = $tab[1];

            if ($MaCleTab1 != "TOTAL" && $MaCleTab1 != "STOTAL" && $MesLignesTableau[$MaCleTab1])// pas egal a cela tout seul mais peut etre "TOTAL charge de ..."
            {

                $MaLigneTotal = &$MesLignesTableau[$MaCleTab1];

                $MaLigneTotal[$Position] = array(StringHelper::NombreFr($Valeur) => array("align" => "right"));

                $MtabTran = array_diff_key($array1, $MaLigneTotal);


                foreach ($MtabTran as $cle => $v) {

                    if (is_array($v) || is_array($array1[$cle]))
                        $MaLigneTotal[$cle] = $v;
                    else
                        $MaLigneTotal[$cle] = array("" => "");
                }
                ksort($MaLigneTotal);

                if ($MaCleTab1 != "TOTALVENTES MARCHANDISES")//pour ajout du sous totla precedent sur le suivant
                    $TotalCharges[$Position] += $Valeur;


            }


        }

        //si la dernière famille à le même nom que les type(charges ou produits), on n'affiche pas ce total
        unset($MesLignesTableau["TOTALCHARGES"]);


        if ($opt['mensuel'])
            $array1 = array(1 => array("" => array("class" => "colvide")), 2 => "", 3 => "", 4 => "", 5 => array("" => array("class" => "colvide")), 6 => "", 7 => "");
        elseif ($opt['cumul'])
            $array1 = array(1 => array("" => array("class" => "colvide")), 2 => "", 3 => "", 4 => "", 5 => array("" => array("class" => "colvide")), 6 => "", 7 => "", 8 => "", 9 => array("" => array("class" => "colvide")), 10 => "", 11 => "");
        else
            $array1 = array(1 => array("" => array("class" => "colvide")), 2 => "", 3 => "", 4 => "", 5 => array("" => array("class" => "colvide")), 6 => "", 7 => "", 8 => "", 9 => array("" => array("class" => "colvide")), 10 => "", 11 => "");


        foreach ($TotauxSF as $codeLigneTableau => $Valeur) {
            //recherche de la ligne Stotal pour lui affecter les valeurs
            $tab = explode("||col||", $codeLigneTableau);

            $MaCleTab1 = $tab[0];

            $Position = $tab[1];


            if ($MaCleTab1 == "STOTALChiffres d'affaires||#||2") {
                $Valeur += $ValeurAncien[$Position];
                //var_dump($ValeurAncien);
            }

            if ($MaCleTab1 == "MARGESTOTALChiffres d'affaires||#||2") {
                $Valeur += $ValeurAncien[$Position];

                //var_dump($ValeurAncien);
            }

            if ($MaCleTab1 != "TOTAL" && $MaCleTab1 != "STOTAL" && $MesLignesTableau[$MaCleTab1]) {

                $MaLigneTotal = &$MesLignesTableau[$MaCleTab1];

                $MaLigneTotal[$Position] = array(StringHelper::NombreFr($Valeur) => array("align" => "right"));

                $MtabTran = array_diff_key($array1, $MaLigneTotal);


                foreach ($MtabTran as $cle => $v) {

                    if (is_array($v) || is_array($array1[$cle]))
                        $MaLigneTotal[$cle] = $v;
                    else
                        $MaLigneTotal[$cle] = array("" => "");
                }
                ksort($MaLigneTotal);

                $ValeurAncien[$Position] = $Valeur;
            }


        }

        $Nb = 11;


        if ($Type) {
            $LnBigTotal = NULL;

            $LnBigTotal[0] = array("Total des $Type :" => array("libelle" => "1", "align" => "right"));

            for ($i = 1; $i <= $col; $i++) {
                //if($i !=4 && $i!= 8)
                if ($TotalCharges[$i])
                    $LnBigTotal[$i] = array(StringHelper::NombreFr($TotalCharges[$i]) => array("align" => "right"));
            }

            $MesLignesTableau[] = "";

            $MtabTran = array_diff_key($array1, $LnBigTotal);

            foreach ($MtabTran as $cle => $v) {

                if (is_array($v) || is_array($array1[$cle]))
                    $LnBigTotal[$cle] = $v;
                else
                    $LnBigTotal[$cle] = array("" => "");
            }

            ksort($LnBigTotal);

            $MesLignesTableau["BIGTOTAL"] = $LnBigTotal;
        }

        if ($Type == "Charges") {
            $_SESSION["TotalCharges"] = NULL;
            $_SESSION["TotalCharges"] = $TotalCharges;

            $MesLignesTableau[] = "";

            //$_SESSION["TotalProduit"] = NULL;


            //compChargesProd::getTab("Produits",$MoisActuel,$FamilleSelect,$SsFamilleSelect,$codePosteSelect,$opt);

            $Resultat[0] = array("R&eacute;sultat :" => array("libelle" => "1", "align" => "right"));

            foreach ($_SESSION["TotalProduit"] as $cle => $Montant) {
                if ($cle <= $Nb)
                    $Resultat[$cle] = array(StringHelper::NombreFr($Montant - $TotalCharges[$cle]) => array("align" => "right"));
            }

            if ($opt['mensuel'])
                $array1 = array(1 => array("" => array("class" => "colvide")), 2 => "", 3 => "", 4 => "", 5 => array("" => array("class" => "colvide")), 6 => "", 7 => "");
            elseif ($opt['cumul'])
                $array1 = array(1 => array("" => array("class" => "colvide")), 2 => "", 3 => "", 4 => "", 5 => array("" => array("class" => "colvide")), 6 => "", 7 => "", 8 => "", 9 => array("" => array("class" => "colvide")), 10 => "", 11 => "");
            else
                $array1 = array(1 => array("" => array("class" => "colvide")), 2 => "", 3 => "", 4 => "", 5 => array("" => array("class" => "colvide")), 6 => "", 7 => "", 8 => "", 9 => array("" => array("class" => "colvide")), 10 => "", 11 => "");


            $MtabTran = array_diff_key($array1, $Resultat);

            foreach ($MtabTran as $cle => $v) {
                if (is_array($v) || is_array($array1[$cle]))
                    $Resultat[$cle] = $v;
                else
                    $Resultat[$cle] = array("" => "");
            }

            ksort($Resultat);

            $MesLignesTableau["TOTALRESULTAT"] = $Resultat;//array(0=>array("R&eacute;sultat"=>array("align"=>"right","style"=>"font-weight: bolder")));

        } elseif ($Type == "Produits") {
            $_SESSION["TotalProduit"] = NULL;
            $_SESSION["TotalProduit"] = $TotalCharges;
        } else {
            $MesLignesTableau[] = "";

            //$_SESSION["TotalProduit"] = NULL;


            //compChargesProd::getTab("Produits",$MoisActuel,$FamilleSelect,$SsFamilleSelect,$codePosteSelect,$opt);

            $Resultat[0] = array("R&eacute;sultat global :" => array("align" => "right"));

            foreach ($_SESSION["TotalProduit"] as $cle => $Montant) {
                if ($cle <= $Nb)
                    $Resultat[$cle] = array(StringHelper::NombreFr($Montant - $_SESSION["TotalCharges"][$cle] + $TotalCharges[$cle]) => array("align" => "right"));
            }

            if ($opt['mensuel'])
                $array1 = array(1 => array("" => array("class" => "colvide")), 2 => "", 3 => "", 4 => "", 5 => array("" => array("class" => "colvide")), 6 => "", 7 => "");
            elseif ($opt['cumul'])
                $array1 = array(1 => array("" => array("class" => "colvide")), 2 => "", 3 => "", 4 => "", 5 => array("" => array("class" => "colvide")), 6 => "", 7 => "");
            else
                $array1 = array(1 => array("" => array("class" => "colvide")), 2 => "", 3 => "", 4 => "", 5 => array("" => array("class" => "colvide")), 6 => "", 7 => "", 8 => "", 9 => array("" => array("class" => "colvide")), 10 => "", 11 => "");


            $MtabTran = array_diff_key($array1, $Resultat);

            foreach ($MtabTran as $cle => $v) {
                $Resultat[$cle] = $v;
            }

            ksort($Resultat);

            $MesLignesTableau["TOTALRESULTAT"] = $Resultat;//array(0=>array("R&eacute;sultat"=>array("align"=>"right","style"=>"font-weight: bolder")));
        }

        return $MesLignesTableau;

    }


    static function getTabProjection($Type, $MoisActuel, $FamilleSelect = false, $SsFamilleSelect = false, $codePosteSelect = false, $opt = null)
    {

        
        self::$allcompte = true;

        //pour la récupération des résultats avec tous les comptes de la balance sauf les comptes d'achat
        $TypeCompte = array("test" => "!=", "type" => "achat");

        $Where = array(
            "and" =>
                array(

                    "compteposte_synthese.type" => "='$Type' "

                )
        );

        $ONFR = false;

        if ($Type != "ONFR") {
            $Where["and"]["compteposte_synthese.Famille"] = "!='ONFR' ";
        } else {
            $Where["and"] = array();
            $Where["and"]["compteposte_synthese.Famille"] = "='ONFR' ";
            $Type = "";
            $ONFR = true;
        }

        // On enlève des postes des charges pour n'avoir que les charges externes, Impots et taxer et frais de personnel
        if ($Type === "Charges") {
            $Where["and"]["compteposte_synthese.codePoste_synthese"] = " NOT IN (14, 15, 18, 19, 20, 21) ";
        } else {
            $Where["and"]["compteposte_synthese.codePoste_synthese"] = " NOT IN (7, 9, 10, 11) ";
        }

        $tri = array(
            "ordre" => "ASC"
        );

        $MesPostes = dbAcces::getPosteSyntheseVisible($Where, $tri, false, $opt["cluster"]);


        $MesLignesTableau = &compChargesProd::IniTableau($MesPostes, false, true, false, true, false, 1, false, false, false);

        //mise en place des résultats + totauxs + sTotaux

        $NbMoisEcart = StringHelper::GetNbMoisEcart(date("Ym", strtotime($_SESSION["station_DOS_DEBEX"])), date("Ym", strtotime(str_replace('-00', '-01', $MoisActuel))));

        if ($_SESSION["station_DOS_PREMDATECP"] > 0) {
            $MoisAnneePrec = StringHelper::DatePlus(str_replace("-00", "-01", $_SESSION["station_DOS_PREMDATECP"]), array("moisplus" => $NbMoisEcart, "dateformat" => "Y-m-00"));
        } else {
            $MoisAnneePrec = StringHelper::DatePlus(str_replace("-00", "-01", $MoisActuel), array("moisplus" => -12, "dateformat" => "Y-m-00"));
        }

        $MesResultats = dbAcces::getResultatsPoste_synthese($MoisActuel, true, true, true, false, $codePosteSelect, $Type, $TypeCompte, false, false, false, $Contenu, $opt["cluster"]);//$opt["MAJBASE"] pour ajouter le prevu agip
        $MesResultatsCumul = dbAcces::getResultatsPoste_synthese($MoisActuel, true, true, true, true, $codePosteSelect, $Type, $TypeCompte, false, false, false, $Rien, $opt["cluster"]);
        $MonPrev = dbAcces::getPrev($_SESSION["station_DOS_NUM"], $MoisActuel, $Type, false, false, false, false, false, false, false, false, false, false, $opt["cluster"]);
        $MonPrevCumul = dbAcces::getPrev($_SESSION["station_DOS_NUM"], $MoisActuel, $Type, true, $codePosteMarge, false, false, false, false, false, false, false, true, $opt["cluster"]);
        $LI_Poste_PosteSynthese = dbAcces::getPosteByPosteSynthese();
        $MesFamille = dbAcces::getFamilleSFamilleSynthese();

        if ($Type == "Produits" || $ONFR) {
            $MesMarges = dbAcces::getMargeTheoriqueGroupe($MoisActuel, $_SESSION["station_DOS_NUM"], true, false, true, $opt["cluster"]);
            $MesMargesN1 = dbAcces::getMargeTheoriqueGroupe($MoisActuel, NULL, true, true, true, $opt["cluster"]);
            $MesMargesCumul = dbAcces::getMargeTheoriqueGroupe($MoisActuel, $_SESSION["station_DOS_NUM"], true, false, true, $opt["cluster"]);
            $MesMargesCumulN1 = dbAcces::getMargeTheoriqueGroupe($MoisActuel, NULL, true, true, true, $opt["cluster"]);
        }

        // if ($Type === "Charges") {
        //     echo '<pre>$MesResultats :<br /> '.print_r($MesResultats, true).'</pre>';
        // }

        $TotalCharges = NULL;
        $LnProduitsONFR = false;
        
        foreach ($MesLignesTableau as $codeCompte => &$UneLigneDb) {

            $LnProduitsONFR = false;

            if ($MesPostes[$codeCompte]["Famille"] == "ONFR" && $MesPostes[$codeCompte]["Type"] == "Produits") {
                $codeCompte = "CMARGE" . $codeCompte;
                $LnProduitsONFR = true;
            }

            if ($codeCompte == 23) { //MISE EN PLACE LIGNE VOLUME CARBURANT (compte synthese numero 24)

//                $rea = dbAcces::getLitrageCarb($_SESSION["station_DOS_NUM"], $MoisActuel, true, true, false, false, false, true);
//                $anm1 = dbAcces::getLitrageCarb($_SESSION["station_DOS_NUM"], $MoisActuel, true, true, true, false, false, true);
//                $prev = dbAcces::getLitrageCarbPrev($_SESSION["station_DOS_NUM"], $MoisActuel, true, true, false, false, false, true);
//
//                $reaC = dbAcces::getLitrageCarb($_SESSION["station_DOS_NUM"], $MoisActuel, true, false, false, false, false, true);
//                $anm1C = dbAcces::getLitrageCarb($_SESSION["station_DOS_NUM"], $MoisActuel, true, false, true, false, false, true);
//                $prevC = dbAcces::getLitrageCarbPrev($_SESSION["station_DOS_NUM"], $MoisActuel, true, false, false, false, false, true);

//                $EcartPrevCarb = $reaC - $prevC;
//
//                if ($anm1C)
//                    $EcartN1Carb = $reaC - $anm1C;
//
//                $UneLigneDb[] = "";
//                $UneLigneDb[] = array(StringHelper::NombreFr($rea, 0) => array('align' => "right"));
//                $UneLigneDb[] = array(StringHelper::NombreFr($prev, 0) => array('align' => "right"));
//                $UneLigneDb[] = array(StringHelper::NombreFr($anm1, 0) => array('align' => "right"));
//                $UneLigneDb[] = "";
//                $UneLigneDb[] = array(StringHelper::NombreFr($reaC, 0) => array('align' => "right"));
//                $UneLigneDb[] = array(StringHelper::NombreFr($prevC, 0) => array('align' => "right"));
//                $UneLigneDb[] = array(StringHelper::NombreFr($anm1C, 0) => array('align' => "right"));
//                $UneLigneDb[] = "";
//                if ($prevC)
//                    $UneLigneDb[] = array(StringHelper::NombreFr($EcartPrevCarb, 0) => array('align' => "right"));
//                else
//                    $UneLigneDb[] = array("" => array('align' => "right"));
//
//                if ($anm1C)
//                    $UneLigneDb[] = array(StringHelper::NombreFr($EcartRN1Carb, 0) => array('align' => "right"));
//                else
//                    $UneLigneDb[] = array("" => array('align' => "right"));

                $UneLigneDb[] = "";
                $UneLigneDb[] = [];
                $UneLigneDb[] = [];
                $UneLigneDb[] = [];

                $UneLigneDb[] = "";

                $UneLigneDb[] = [];
                $UneLigneDb[] = [];

                $UneLigneDb[] = "";

                $UneLigneDb[] = [];
                $UneLigneDb[] = [];
            } elseif (is_numeric($codeCompte)) {
                //c'est une ligne d'un poste
                //echo StringHelper::NombreFr($MesResultats[$codeCompte."||#||"."UMoisRealise"]["Montant"])."<br/>";


                $nbMoisAAjouter = $_SESSION['station_DOS_NBMOIS'] - $_SESSION['NbMois'];

                $rea = $MesResultats[$codeCompte . "||#||" . "UMoisRealise"]["Montant"];
                $anm1 = $MesResultats[$codeCompte . "||#||" . "UMoisAnneeMoinsUn"]["Montant"];
                $prev = $MesResultats[$codeCompte . "||#||" . "UMoisPrevu"]["Montant"];

                $anm1C = $MesResultatsCumul[$codeCompte . "||#||" . "UMoisAnneeMoinsUn"]["Montant"];
                $prevC = $MesResultatsCumul[$codeCompte . "||#||" . "UMoisPrevu"]["Montant"] + ($nbMoisAAjouter * $prev);
                $reaC = $MesResultatsCumul[$codeCompte . "||#||" . "UMoisRealise"]["Montant"];


                $reaReaC = $MesResultatsCumul[$codeCompte . "||#||" . "UMoisRealise"]["Montant"] + ($nbMoisAAjouter * $rea);

                if ($MesFamille[$codeCompte]["Type"] == "Produits") {
                    $reaPrevC = $MesResultatsCumul[$codeCompte . "||#||" . "UMoisRealise"]["Montant"] - ($nbMoisAAjouter * $prev);

                    $rea = -$rea;
                    $anm1 = -$anm1;

                    $reaPrevC = -$reaPrevC;
                    $anm1C = -$anm1C;

                    $reaC = -$reaC;
                    $anm1C = -$anm1C;
                    $reaReaC = -$reaReaC;
                    
                } else {
                    $reaPrevC = $MesResultatsCumul[$codeCompte . "||#||" . "UMoisRealise"]["Montant"] + ($nbMoisAAjouter * $prev);
                }

                $ecartReaPrevPrev = $reaPrevC - $prevC;
                $ecartReaPrevPrevPourc = $prevC != 0 ? $ecartReaPrevPrev / $prevC * 100 : 0;

                $ecartReaReaPrev = $reaReaC - $prevC;
                $ecartReaReaPrevPourc = $prevC != 0 ? $ecartReaReaPrev / $prevC * 100 : 0;

                $UneLigneDb[] = "";
                $UneLigneDb[] = array(StringHelper::NombreFr($prevC) => array("align" => "right"));
                $UneLigneDb[] = array(StringHelper::NombreFr($reaPrevC) => array("align" => "right"));
                $UneLigneDb[] = array(StringHelper::NombreFr($reaReaC) => array("align" => "right"));

                $UneLigneDb[] = "";

                $UneLigneDb[] = array(StringHelper::NombreFr($ecartReaPrevPrev) => array("align" => "right"));
                $UneLigneDb[] = array(StringHelper::NombreFr($ecartReaPrevPrevPourc, 0, false, false) => array("align" => "right"));

                $UneLigneDb[] = "";

                $UneLigneDb[] = array(StringHelper::NombreFr($ecartReaReaPrev) => array("align" => "right"));
                $UneLigneDb[] = array(StringHelper::NombreFr($ecartReaReaPrevPourc, 0, false, false) => array("align" => "right"));


                if ($MesFamille[$codeCompte]["Type"] == "Charges" && $MesFamille[$codeCompte]["Famille"] == "ONFR") {
                    $rea = -$rea;
                    $prev = -$prev;
                    $anm1 = -$anm1;

                    $reaC = -$reaC;
                    $anm1C = -$anm1C;
                    $prevC = -$prevC;
                }


                $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||2"] += $prevC;
                $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||3"] += $reaPrevC;
                $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||4"] += $reaReaC;
                $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||2"] += $rea;
                $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||3"] += $prev;
                $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||4"] += $anm1;

                $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||6"] += $ecartReaPrevPrev;
                $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||7"] += 0;
                $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||6"] += $ecartReaPrevPrev;
                $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||7"] += 0;


                if ($Contenu["UMoisPrevu"]) {
                    $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||9"] += $ecartReaReaPrev;
                    $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||9"] += $ecartReaReaPrev;
                }

                if ($_SESSION["station_DOS_PREMDATECP"] > 0) {
                    $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||10"] += 0;
                    $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||10"] += 0;
                }

                if (!$rea && !$prev && !$anm1 && !$reaC && !$prevC && !$anm1C && !self::$allcompte)
                    $UneLigneDb = NULL;


            } elseif (strpos($codeCompte, "CMARGE") !== false) {
                //MARGE////////////////


                //---| Recup des valeurs |----------------------------------------------------------------------------------------------------------------//

                $codePosteMarge = str_replace("CMARGE", "", $codeCompte);


                $CompteBalance["Poste" . $codePosteMarge] = dbAcces::getLiaisonBalPoste(NULL, "achat", NULL, $codePosteMarge);

                $TotalMarge["Poste" . $codePosteMarge] = 0;
                $TotalMarge["Poste" . $codePosteMarge] += $MesMarges[$codePosteMarge]["Montant"];


                $TotalMargeN1["Poste" . $codePosteMarge] += $MesMargesN1[$codePosteMarge]["Montant"];

                foreach ($LI_Poste_PosteSynthese[$codePosteMarge] as $UnCodePoste => $Liaison)
                    $TotalMargePrev["Poste" . $codePosteMarge] += ($MonPrevCumul[$UnCodePoste]["Montant"]);

                $TotalMargeCumul["Poste" . $codePosteMarge] += $MesMargesCumul[$codePosteMarge]["Montant"];
                $TotalMargeCumulN1["Poste" . $codePosteMarge] += $MesMargesCumulN1[$codePosteMarge]["Montant"];

                foreach ($LI_Poste_PosteSynthese[$codePosteMarge] as $UnCodePoste => $Liaison)
                    $TotalMargePrevCumul["Poste" . $codePosteMarge] += $MonPrevCumul[$UnCodePoste]["Montant"];


                $MonEcartMarge = dbAcces::getEcartMarge($_SESSION["station_DOS_NUM"], $MoisActuel, false, false, true, $codePosteMarge, $opt["cluster"]);
                $MonEcartMargeN1 = dbAcces::getEcartMarge($_SESSION["station_DOS_NUMPREC"], $MoisAnneePrec, false, false, true, $codePosteMarge, $opt["cluster"]);


                $MonEcartMargeCumul = dbAcces::getEcartMarge($_SESSION["station_DOS_NUM"], $MoisActuel, false, false, true, $codePosteMarge, $opt["cluster"]);
                $MonEcartMargeN1Cumul = dbAcces::getEcartMarge($_SESSION["station_DOS_NUMPREC"], $MoisAnneePrec, false, false, true, $codePosteMarge, $opt["cluster"]);


                $TotalMarge["Poste" . $codePosteMarge] += $MonEcartMarge;
                $TotalMargeN1["Poste" . $codePosteMarge] += $MonEcartMargeN1;

                $TotalMargeCumul["Poste" . $codePosteMarge] += $MonEcartMargeCumul;
                $TotalMargeCumulN1["Poste" . $codePosteMarge] += $MonEcartMargeN1Cumul;


                //---| insertion ds tableau |----------------------------------------------------------------------------------------------------------------//


                $UneLigneDb[] = "";


                $UneLigneDb[] = array(StringHelper::NombreFr($TotalMarge["Poste$codePosteMarge"]) => array("align" => "right"));
                $UneLigneDb[] = array(StringHelper::NombreFr($TotalMargePrev["Poste$codePosteMarge"]) => array("align" => "right"));
                $UneLigneDb[] = array(StringHelper::NombreFr($TotalMargeN1["Poste$codePosteMarge"]) => array("align" => "right"));

                $UneLigneDb[] = "";

                $margeCumul += $TotalMarge["Poste$codePosteMarge"];
                $margeEcartProjPrev = $TotalMargePrev["Poste$codePosteMarge"] - $TotalMarge["Poste$codePosteMarge"];
                $margeEcartProjReal = $TotalMargeN1["Poste$codePosteMarge"] - $TotalMarge["Poste$codePosteMarge"];
                $pourMargeEcartProjPrev = $TotalMarge["Poste$codePosteMarge"] != 0 ? StringHelper::NombreFr($margeEcartProjPrev / $TotalMarge["Poste$codePosteMarge"] * 100) : "";
                $pourMargeEcartProjReal = $TotalMarge["Poste$codePosteMarge"] != 0 ? StringHelper::NombreFr($margeEcartProjReal / $TotalMarge["Poste$codePosteMarge"] * 100) : "";

                $UneLigneDb[] = array(StringHelper::NombreFr($margeEcartProjPrev) => array("align" => "right"));
                $UneLigneDb[] = array($pourMargeEcartProjPrev => array("align" => "right"));

                $UneLigneDb[] = "";

                $UneLigneDb[] = array(StringHelper::NombreFr($margeEcartProjReal) => array("align" => "right"));
                $UneLigneDb[] = array($pourMargeEcartProjReal  => array("align" => "right"));


                $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||2"] += $TotalMarge["Poste$codePosteMarge"];
                $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||3"] += $TotalMargePrev["Poste$codePosteMarge"];
                $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||4"] += $TotalMargeN1["Poste$codePosteMarge"];

                $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||2"] += $TotalMarge["Poste$codePosteMarge"];;
                $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||3"] += $TotalMargePrev["Poste$codePosteMarge"];
                $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||4"] += $TotalMargeN1["Poste$codePosteMarge"];


                $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||6"] += $margeEcartProjPrev;
                $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||7"] += 0;

                $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||6"] += $margeEcartProjPrev;
                $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||7"] += 0;

                if ($Contenu["UMoisPrevu"]) {
                    $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||9"] += $margeEcartProjReal;
                    $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||9"] += $margeEcartProjReal;
                }

                if ($_SESSION["station_DOS_PREMDATECP"] > 0) {
                    $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||10"] += 0;
                    $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||10"] += 0;
                }


                if ($LnProduitsONFR) {

                    $TotauxF["TOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||2"] += $TotalMarge["Poste$codePosteMarge"];
                    $TotauxF["TOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||3"] += $TotalMargePrev["Poste$codePosteMarge"];
                    $TotauxF["TOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||4"] += $TotalMargeN1["Poste$codePosteMarge"];
                    $TotauxSF["STOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||2"] += $TotalMarge["Poste$codePosteMarge"];
                    $TotauxSF["STOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||3"] += $TotalMargePrev["Poste$codePosteMarge"];
                    $TotauxSF["STOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||4"] += $TotalMargeN1["Poste$codePosteMarge"];

                    $TotauxF["TOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||6"] += $TotalMargeCumul["Poste$codePosteMarge"];
                    $TotauxF["TOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||7"] += $TotalMargePrevCumul["Poste$codePosteMarge"];
                    $TotauxF["TOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||8"] += $TotalMargeCumulN1["Poste$codePosteMarge"];
                    $TotauxSF["STOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||6"] += $TotalMargeCumul["Poste$codePosteMarge"];
                    $TotauxSF["STOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||7"] += $TotalMargePrevCumul["Poste$codePosteMarge"];
                    $TotauxSF["STOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||8"] += $TotalMargeCumulN1["Poste$codePosteMarge"];


                    if ($Contenu["UMoisPrevu"]) {
                        $TotauxSF["STOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||10"] += ($TotalMargeCumul["Poste$codePosteMarge"] - $TotalMargePrevCumul["Poste$codePosteMarge"]);
                        $TotauxF["TOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||10"] += ($TotalMargeCumul["Poste$codePosteMarge"] - $TotalMargePrevCumul["Poste$codePosteMarge"]);
                    }

                    if ($_SESSION["station_DOS_PREMDATECP"] > 0) {
                        $TotauxF["TOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||11"] += ($TotalMargeCumul["Poste$codePosteMarge"] - $TotalMargeCumulN1["Poste$codePosteMarge"]);
                        $TotauxSF["STOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||11"] += ($TotalMargeCumul["Poste$codePosteMarge"] - $TotalMargeCumulN1["Poste$codePosteMarge"]);
                    }
                }

                if (!$TotalMarge["Poste$codePosteMarge"] && !$TotalMargePrev["Poste$codePosteMarge"] && !$TotalMargeN1["Poste$codePosteMarge"] && !$TotalMargeCumul["Poste$codePosteMarge"] && !$TotalMargePrevCumul["Poste$codePosteMarge"] && !$TotalMargeCumulN1["Poste$codePosteMarge"] && !self::$allcompte)
                    $UneLigneDb = NULL;


            } elseif (strpos($codeCompte, "TITRE") !== false) {
                $UneLigneDb[] = "";
                $UneLigneDb[] = array("" => array('style' => "border-right:none"));
                $UneLigneDb[] = array("" => array('style' => "border-right:none"));
                $UneLigneDb[] = array("" => "");
                $UneLigneDb[] = "";
                $UneLigneDb[] = array("" => array('style' => "border-right:none"));
                $UneLigneDb[] = array("" => array('style' => "border-right:none"));
                $UneLigneDb[] = "";
                $UneLigneDb[] = array("" => array('style' => "border-right:none;"));
                $UneLigneDb[] = array("" => array('style' => "border-left:none;"));
            }

        }


        //TOTAUX


        $col = 11;


        $array1 = array(1 => array("" => array("class" => "colvide")), 2 => "", 3 => "", 4 => "", 5 => array("" => array("class" => "colvide")), 6 => "", 7 => "", 8 => array("" => array("class" => "colvide")), 9 => "", 10 => "");

        if($TotauxF["TOTALVENTES MARCHANDISES||col||2"]) {
            $TotauxF["TOTALVENTES MARCHANDISES||col||7"] = ($TotauxF["TOTALVENTES MARCHANDISES||col||3"] - $TotauxF["TOTALVENTES MARCHANDISES||col||2"]) / $TotauxF["TOTALVENTES MARCHANDISES||col||2"] * 100;
            $TotauxF["TOTALVENTES MARCHANDISES||col||7"] = StringHelper::NombreFr($TotauxF["TOTALVENTES MARCHANDISES||col||7"], 0, false, false, true);
            $TotauxF["TOTALVENTES MARCHANDISES||col||10"] = ($TotauxF["TOTALVENTES MARCHANDISES||col||4"] - $TotauxF["TOTALVENTES MARCHANDISES||col||2"]) / $TotauxF["TOTALVENTES MARCHANDISES||col||2"] * 100;
            $TotauxF["TOTALVENTES MARCHANDISES||col||10"] = StringHelper::NombreFr($TotauxF["TOTALVENTES MARCHANDISES||col||10"], 0, false, false, true);
        }

        if($TotauxF["MARGETOTALVENTES MARCHANDISES||col||2"]) {
            $TotauxF["MARGETOTALVENTES MARCHANDISES||col||7"] = ($TotauxF["MARGETOTALVENTES MARCHANDISES||col||3"] - $TotauxF["MARGETOTALVENTES MARCHANDISES||col||2"]) / $TotauxF["MARGETOTALVENTES MARCHANDISES||col||2"] * 100;
            $TotauxF["MARGETOTALVENTES MARCHANDISES||col||7"] = StringHelper::NombreFr($TotauxF["MARGETOTALVENTES MARCHANDISES||col||7"], 0, false, false, true);
            $TotauxF["MARGETOTALVENTES MARCHANDISES||col||10"] = ($TotauxF["MARGETOTALVENTES MARCHANDISES||col||4"] - $TotauxF["MARGETOTALVENTES MARCHANDISES||col||2"]) / $TotauxF["MARGETOTALVENTES MARCHANDISES||col||2"] * 100;
            $TotauxF["MARGETOTALVENTES MARCHANDISES||col||10"] = StringHelper::NombreFr($TotauxF["MARGETOTALVENTES MARCHANDISES||col||10"], 0, false, false, true);
        }
        
        if($TotauxF["TOTALCHARGES||col||2"]) {
            $TotauxF["TOTALCHARGES||col||7"] = ($TotauxF["TOTALCHARGES||col||3"] - $TotauxF["TOTALCHARGES||col||2"]) / $TotauxF["TOTALCHARGES||col||2"] * 100 ;
            $TotauxF["TOTALCHARGES||col||7"] = StringHelper::NombreFr($TotauxF["TOTALCHARGES||col||7"], 0, false, false, true);
            
            $TotauxF["TOTALCHARGES||col||10"] = ($TotauxF["TOTALCHARGES||col||4"] - $TotauxF["TOTALCHARGES||col||2"]) / $TotauxF["TOTALCHARGES||col||2"] * 100;
            $TotauxF["TOTALCHARGES||col||10"] = StringHelper::NombreFr($TotauxF["TOTALCHARGES||col||10"], 0, false, false, true);
        }


        foreach ($TotauxF as $codeLigneTableau => $Valeur) {
            //recherche de la ligne total pour lui affecter les valeurs
            $tab = explode("||col||", $codeLigneTableau);

            $MaCleTab1 = $tab[0];
            $Position = $tab[1];

            if ($MaCleTab1 != "TOTAL" && $MaCleTab1 != "STOTAL" && $MesLignesTableau[$MaCleTab1])// pas egal a cela tout seul mais peut etre "TOTAL charge de ..."
            {

                $MaLigneTotal = &$MesLignesTableau[$MaCleTab1];

                $MaLigneTotal[$Position] = array(StringHelper::NombreFr($Valeur) => array("align" => "right"));

                $MtabTran = array_diff_key($array1, $MaLigneTotal);


                foreach ($MtabTran as $cle => $v) {

                    if (is_array($v) || is_array($array1[$cle]))
                        $MaLigneTotal[$cle] = $v;
                    else
                        $MaLigneTotal[$cle] = array("" => "");
                }
                ksort($MaLigneTotal);

                if ($MaCleTab1 != "TOTALVENTES MARCHANDISES")//pour ajout du sous totla precedent sur le suivant
                    $TotalCharges[$Position] += $Valeur;


            }

        }

        //si la dernière famille à le même nom que les type(charges ou produits), on n'affiche pas ce total
        unset($MesLignesTableau["TOTALCHARGES"]);

        if($TotauxSF["STOTALChiffres d'affaires||col||2"]) {
            $TotauxSF["STOTALChiffres d'affaires||col||7"] = ($TotauxSF["STOTALChiffres d'affaires||col||3"] - $TotauxSF["STOTALChiffres d'affaires||col||2"]) / $TotauxSF["STOTALChiffres d'affaires||col||2"] * 100;
            $TotauxSF["STOTALChiffres d'affaires||col||7"] = StringHelper::NombreFr($TotauxSF["STOTALChiffres d'affaires||col||7"], 0, false, false, true);
            $TotauxSF["STOTALChiffres d'affaires||col||10"] = ($TotauxSF["STOTALChiffres d'affaires||col||4"] - $TotauxSF["STOTALChiffres d'affaires||col||2"]) / $TotauxSF["STOTALChiffres d'affaires||col||2"] * 100;
            $TotauxSF["STOTALChiffres d'affaires||col||10"] = StringHelper::NombreFr($TotauxSF["STOTALChiffres d'affaires||col||10"], 0, false, false, true);
        }

        if($TotauxSF["STOTALCharges||col||2"]) {
            $TotauxSF["STOTALCharges||col||7"] = ($TotauxSF["STOTALCharges||col||3"] - $TotauxSF["STOTALCharges||col||2"]) / $TotauxSF["STOTALCharges||col||2"] * 100;
            $TotauxSF["STOTALCharges||col||7"] = StringHelper::NombreFr($TotauxSF["STOTALCharges||col||7"], 0, false, false, true);
            $TotauxSF["STOTALCharges||col||10"] = ($TotauxSF["STOTALCharges||col||4"] - $TotauxSF["STOTALCharges||col||2"]) / $TotauxSF["STOTALCharges||col||2"] * 100;
            $TotauxSF["STOTALCharges||col||10"] = StringHelper::NombreFr($TotauxSF["STOTALCharges||col||10"], 0, false, false, true);
        }

        if($TotauxSF["MARGESTOTALChiffres d'affaires||col||2"]) {
            $TotauxSF["MARGESTOTALChiffres d'affaires||col||7"] = ($TotauxSF["MARGESTOTALChiffres d'affaires||col||3"] - $TotauxSF["MARGESTOTALChiffres d'affaires||col||2"]) / $TotauxSF["MARGESTOTALChiffres d'affaires||col||2"] * 100;
            $TotauxSF["MARGESTOTALChiffres d'affaires||col||7"] = StringHelper::NombreFr($TotauxSF["MARGESTOTALChiffres d'affaires||col||7"], 0, false, false, true);
            $TotauxSF["MARGESTOTALChiffres d'affaires||col||10"] = ($TotauxSF["MARGESTOTALChiffres d'affaires||col||4"] - $TotauxSF["MARGESTOTALChiffres d'affaires||col||2"]) / $TotauxSF["MARGESTOTALChiffres d'affaires||col||2"] * 100;
            $TotauxSF["MARGESTOTALChiffres d'affaires||col||10"] = StringHelper::NombreFr($TotauxSF["MARGESTOTALChiffres d'affaires||col||10"], 0, false, false, true);
        }

        $array1 = array(1 => array("" => array("class" => "colvide")), 2 => "", 3 => "", 4 => "", 5 => array("" => array("class" => "colvide")), 6 => "", 7 => "", 8 => array("" => array("class" => "colvide")), 9 => "", 10 => "");

        foreach ($TotauxSF as $codeLigneTableau => $Valeur) {
            //recherche de la ligne Stotal pour lui affecter les valeurs
            $tab = explode("||col||", $codeLigneTableau);

            $MaCleTab1 = $tab[0];

            $Position = $tab[1];


            if ($MaCleTab1 == "STOTALChiffres d'affaires||#||2") {
                $Valeur += $ValeurAncien[$Position];
                //var_dump($ValeurAncien);
            }

            if ($MaCleTab1 == "MARGESTOTALChiffres d'affaires||#||2") {
                $Valeur += $ValeurAncien[$Position];

                //var_dump($ValeurAncien);
            }

            if ($MaCleTab1 != "TOTAL" && $MaCleTab1 != "STOTAL" && $MesLignesTableau[$MaCleTab1]) {

                $MaLigneTotal = &$MesLignesTableau[$MaCleTab1];

                $MaLigneTotal[$Position] = array(StringHelper::NombreFr($Valeur) => array("align" => "right"));

                $MtabTran = array_diff_key($array1, $MaLigneTotal);


                foreach ($MtabTran as $cle => $v) {

                    if (is_array($v) || is_array($array1[$cle]))
                        $MaLigneTotal[$cle] = $v;
                    else
                        $MaLigneTotal[$cle] = array("" => "");
                }
                ksort($MaLigneTotal);

                $ValeurAncien[$Position] = $Valeur;
            }


        }

        $Nb = 11;


        if ($Type) {
            $LnBigTotal = NULL;

            if ($Type == "Charges") {
                $LnBigTotal[0] = array("TOTAL " .  strtoupper($Type) . " :" => array("libelle" => "1", "align" => "right"));
            }

            for ($i = 1; $i <= $col; $i++) {
                //if($i !=4 && $i!= 8)

                if ($TotalCharges[$i])
                    $LnBigTotal[$i] = array(StringHelper::NombreFr($TotalCharges[$i]) => array("align" => "right"));
            }

            $MesLignesTableau[] = "";

            $MtabTran = array_diff_key($array1, $LnBigTotal);

            foreach ($MtabTran as $cle => $v) {

                if (is_array($v) || is_array($array1[$cle]))
                    $LnBigTotal[$cle] = $v;
                else
                    $LnBigTotal[$cle] = array("" => "");
            }

            ksort($LnBigTotal);
            if ($Type == "Charges") {
                $MesLignesTableau["BIGTOTAL"] = $LnBigTotal;
            }
        }

        if ($Type == "Charges") {
            $_SESSION["TotalCharges"] = NULL;
            $_SESSION["TotalCharges"] = $TotalCharges;

            $MesLignesTableau[] = "";

            //$_SESSION["TotalProduit"] = NULL;


            //compChargesProd::getTab("Produits",$MoisActuel,$FamilleSelect,$SsFamilleSelect,$codePosteSelect,$opt);

            $Resultat[0] = array("Exc&eacute;dent brut d'exploitation :" => array("libelle" => "1", "align" => "right"));
            foreach ($_SESSION["TotalProduit"] as $cle => $Montant) {
                if ($cle <= $Nb)
                    $Resultat[$cle] = array(StringHelper::NombreFr($Montant - $TotalCharges[$cle]) => array("align" => "right"));
            }


            $array1 = array(1 => array("" => array("class" => "colvide")), 2 => "", 3 => "", 4 => "", 5 => array("" => array("class" => "colvide")), 6 => "", 7 => "", 8 => array("" => array("class" => "colvide")), 9 => "", 10 => "");

            $MtabTran = array_diff_key($array1, $Resultat);

            foreach ($MtabTran as $cle => $v) {
                if (is_array($v) || is_array($array1[$cle]))
                    $Resultat[$cle] = $v;
                else
                    $Resultat[$cle] = array("" => "");
            }

            ksort($Resultat);

            $MesLignesTableau["TOTALRESULTAT"] = $Resultat;//array(0=>array("R&eacute;sultat"=>array("align"=>"right","style"=>"font-weight: bolder")));

        } elseif ($Type == "Produits") {
            $_SESSION["TotalProduit"] = NULL;
            $_SESSION["TotalProduit"] = $TotalCharges;
        } else {
            $MesLignesTableau[] = "";

            //$_SESSION["TotalProduit"] = NULL;


            //compChargesProd::getTab("Produits",$MoisActuel,$FamilleSelect,$SsFamilleSelect,$codePosteSelect,$opt);

            $Resultat[0] = array("R&eacute;sultat global :" => array("align" => "right"));

            foreach ($_SESSION["TotalProduit"] as $cle => $Montant) {
                if ($cle <= $Nb)
                    $Resultat[$cle] = array(StringHelper::NombreFr($Montant - $_SESSION["TotalCharges"][$cle] + $TotalCharges[$cle]) => array("align" => "right"));
            }

            $array1 = array(1 => array("" => array("class" => "colvide")), 2 => "", 3 => "", 4 => "", 5 => array("" => array("class" => "colvide")), 6 => "", 7 => "", 8 => array("" => array("class" => "colvide")), 9 => "", 10 => "");

            $MtabTran = array_diff_key($array1, $Resultat);

            foreach ($MtabTran as $cle => $v) {
                $Resultat[$cle] = $v;
            }

            ksort($Resultat);

            $MesLignesTableau["TOTALRESULTAT"] = $Resultat;//array(0=>array("R&eacute;sultat"=>array("align"=>"right","style"=>"font-weight: bolder")));
        }


        if($MesLignesTableau["TOTALRESULTAT"]) {

            $montantEBEPrev = StringHelper::Texte2Nombre(array_key_first($MesLignesTableau["TOTALRESULTAT"][3]));
            $montantEBEReaPrev = StringHelper::Texte2Nombre(array_key_first($MesLignesTableau["TOTALRESULTAT"][6]));
            $montantEBEReaRea = StringHelper::Texte2Nombre(array_key_first($MesLignesTableau["TOTALRESULTAT"][9]));

            $pourMontantEBEReaPrev = $montantEBEPrev != 0 ? StringHelper::NombreFr($montantEBEReaPrev / $montantEBEPrev * 100) : "";
            $pourMontantEBEReaPrev = $montantEBEReaPrev > 0 && $montantEBEPrev < 0 ? -$pourMontantEBEReaPrev : $pourMontantEBEReaPrev;

            $pourMontantEBEReaRea = $montantEBEPrev != 0 ? StringHelper::NombreFr(($montantEBEReaRea) / $montantEBEPrev * 100) : "";
            $pourMontantEBEReaRea = $montantEBEReaRea > 0 && $montantEBEPrev < 0 ? -$pourMontantEBEReaRea : $pourMontantEBEReaRea;

            $styleSept = reset($MesLignesTableau["TOTALRESULTAT"][7]);
            $styleDix = reset($MesLignesTableau["TOTALRESULTAT"][10]);
            unset($MesLignesTableau["TOTALRESULTAT"][7]);
            unset($MesLignesTableau["TOTALRESULTAT"][10]);
            $MesLignesTableau["TOTALRESULTAT"][7][$pourMontantEBEReaPrev] = $styleSept;
            $MesLignesTableau["TOTALRESULTAT"][10][$pourMontantEBEReaRea] = $styleDix;
        }

        // mettre en pourcentage la colone 7 et 9
        foreach ($MesLignesTableau as $key => $ligneTableau) {
            foreach ($ligneTableau as $position => $case) {
                if ($position == 7 || $position == 10) {
                    foreach ($case as $value => $style) {
                        if($value) {
                            $MesLignesTableau[$key][$position][$value . " %"] = $style;
                            unset($MesLignesTableau[$key][$position][$value]);
                        }
                    }
                }
            }
        }

        return $MesLignesTableau;

    }


    static function getTabDetail($Type, $MoisActuel, $FAMILLE = false, $SFAMILLE = false, $CodePoste = false)
    {

        global $Imprimer;


        if (!$Periode["BAL_MOIS_DEB"] || !$Periode["BAL_MOIS_FIN"])
            $Periode = NULL;

        $d = NULL;
        $d = NULL;
        $d = array(
            "index" => "CPTB_NUM"
        );
        if ($CPTB_NUM)
            $d["tabCriteres"]["comptebilan.CPTB_NUM"] = $CPTB_NUM;

        $MesPostes = db_BilanDetail::getComptesBilan($d);

        $MaCleTab = NULL;
        $TotauxF = NULL;
        $TotauxSF = NULL;
        $PremF = true;
        $PremSF = true;
        $FaireSToataux = true;


        //initialisation du tableau avec ligne total + stotal de chaque postes
        foreach ($MesPostes as $codePoste => $UneLignePoste) {

            $UneLigneTableau = NULL;


            $d = NULL;
            $d = array(
                "join" => " join AS_comptes_comptepostebilan ON AS_comptes_comptepostebilan.CPTB_NUM = comptebilan.CPTB_NUM 
                            join comptes ON comptes.code_compte = AS_comptes_comptepostebilan.codeCompte ",
                "triRequete" => " ORDER BY comptes.numero ASC",
                "distinct * "
            );

            if ($codePoste)
                $d["tabCriteres"]["comptebilan.CPTB_NUM"] = $codePoste;

            $MesComptes = db_BilanDetail::getComptesBilan($d);

            $MesResultatsCompte = dbAcces::getResultatsCompte($MoisActuel, false, false, false);

            $MaSsFamille = explode("||#||", $UneLignePoste["CPTB_SFAMILLE"]);
            $MaSsFamille = $MaSsFamille[0];

            if ($UneLignePoste["CPTB_SFAMILLE"] != $SsFamilleDef) {

                if ($UneLignePoste["CPTB_FAMILLE"] == $FamilleDef)//ln nom sous-famille
                {

                    $UneLigneTableau[] = array($MaSsFamille => array("style" => "padding: 5px;text-align:left; ", "class" => "tdfixe", "width" => "250"));

                    if ($MoisActuel) {

                        $UneLigneTableau[] = array(StringHelper::Mysql2DateFr($MoisActuel) => array("class" => "tdfixe", "width" => "40", "style" => "border:none"));
                    }


                    $MesLignesTableau["ENCADRE" . $UneLignePoste["CPTB_SFAMILLE"]] = $UneLigneTableau;
                    $UneLigneTableau = NULL;
                }
                $SsFamilleDef = NULL;
                $SsFamilleDef = $UneLignePoste["CPTB_SFAMILLE"];

            }

            if ($UneLignePoste["CPTB_FAMILLE"] != $FamilleDef)//changement de famille
            {
                //ln nom famille

                if ($FamilleDef) {//séparation
                    $UneLigneTableau = NULL;

                    $UneLigneTableau[] = "<div style='height:100px;'></div>";

                    $MesLignesTableau["SEPAR" . $UneLignePoste["CPTB_FAMILLE"]] = $UneLigneTableau;
                }
                $UneLigneTableau = NULL;

                $UneLignePoste["CPTB_FAMILLE"] = str_replace("ONFR", "ACTIVITES ANNEXES", $UneLignePoste["CPTB_FAMILLE"]);


                $UneLigneTableau = NULL;
                $UneLigneTableau[] = array($MaSsFamille . "<div class='div200' style='width:350px'></div>" => array("style" => "padding: 5px;text-align:left; ", "class" => "tdfixe", "width" => "250"));

                $UneLigneTableau[] = array(StringHelper::Mysql2DateFr($MoisActuel) . "<div class='div70'></div>" => array("class" => "tdfixe", "width" => "40"));


                $MesLignesTableau["ENCADRE" . $UneLignePoste["CPTB_SFAMILLE"]] = $UneLigneTableau;
                $UneLigneTableau = NULL;

                $FamilleDef = $UneLignePoste["CPTB_FAMILLE"];
                $PremSF = true;
                $FaireSToataux = false;
            }

            $UneLigneTableauTitre = NULL;

            $UneLigneTableauTitre[] = array(" - $toto" . strtoupper(StringHelper::TextFilter($UneLignePoste["CPTB_LIB"])) => array("align" => "left", "style" => "font-weight:bolder;"));

            $UneLigneTableau = NULL;

            $AuMoinUnCompte = false;

            foreach ($MesComptes as $codeCompte => $UneLigneCompte) {

                if ((!$Imprimer) || ($MesResultatsCompte[$codeCompte . "UMois"]["BAL_BALANCE"] || $MesResultatsCompte[$codeCompte . "UMois"]["BAL_CUMUL"] || $MesResultatsCompteNMoins1[$codeCompte . "UMois"]["BAL_BALANCE"] || $MesResultatsCompteNMoins1Cumul[$codeCompte . "UMois"]["BAL_CUMUL"])) {
                    if (!$AuMoinUnCompte)
                        $MesLignesTableau["Poste" . $codePoste] = $UneLigneTableauTitre;
                    $AuMoinUnCompte = true;
                    $UneLigneTableau = NULL;


                    $UneLigneTableau[] = array($UneLigneCompte["numero"] . " &nbsp; " . $UneLigneCompte["libelle"] => array("align" => "left"));

                    $ComplementNom = "";

                    if ($UneLignePoste["Famille"] == "ONFR")
                        $ComplementNom = "||#||ONFR";

                    $MesLignesTableau[$codeCompte . $ComplementNom] = $UneLigneTableau;
                }
            }

            if ($AuMoinUnCompte && $MoisActuel) {


                $UneLigneTableau = NULL;
                //$UneLigneTableau[] = array("Total -"=>array("align"=>"right","style"=>"font-weight: bolder")); Orel 03022010
                $UneLigneTableau[] = array("" => array("align" => "right", "style" => "font-weight: bolder"));
                $MesLignesTableau["Total" . $codePoste] = $UneLigneTableau;
                $PosteDef = $UneLignePoste["Libelle"];
                $UneLigneTableau = NULL;
                //Ln vide
                $UneLigneTableau[] = array("" => array("colspan" => "8"));//Orel 03022010 (colspan 6
                $MesLignesTableau["VIDE2" . $codePoste] = $UneLigneTableau;
                $UneLigneTableau = NULL;
            }


            if ($PremSF) $PremSF = false;
            if ($PremF) $PremF = false;
        }


        //***************************************/


        $UneLigneTableau = NULL;


        //mise en place des résultats comptes Période N

        if ($MoisActuel) {
            //var_dump($MesResultatsCompte);
            foreach ($MesLignesTableau as $codeCompte => &$UneLigneDb) {

                $codeCompte = str_replace("||#||ONFR", "", $codeCompte);

                if (strpos($codeCompte, "ENCADRE") !== false) {
                    $Famille = str_replace("ENCADRE", "", $codeCompte);
                } elseif (strpos($codeCompte, "Poste") !== false) {
                    $codePoste = str_replace("Poste", "", $codeCompte);
                } elseif (is_numeric($codeCompte)) {
                    // C'est une ligne d'un compte
                    $reacompte = $MesResultatsCompte[$codeCompte . "UMois"]["BAL_CUMUL"];
                
                    $MesResultats[$codePoste . "||#||" . "UMoisRealise"]["Montant"] += $reacompte;
                    $MesResultats[$codePoste . "||#||" . "UMoisRealise"]["Famille"] = $Famille;
                    $UneLigneDb[] = array(StringHelper::NombreFr($reacompte) => array("align" => "right"));
                }
                
            }
        }


        //mise en place des résultats + totauxs + sTotaux

        //$MesResultats = dbAcces::getResultatsPoste($MoisActuel,true,true,true,false,$codePosteSelect,$Type,$TypeCompte);

        if ($MoisActuel) {

            foreach ($MesLignesTableau as $codePoste => &$UneLigneDb) {

                $codeCompte = str_replace("||#||ONFR", "", $codeCompte);

                if (strpos($codePoste, "Total") !== false) {
                    $codePoste = str_replace("Total", "", $codePoste);

                    //c'est une ligne d'un poste

                    $rea = $MesResultats[$codePoste . "||#||" . "UMoisRealise"]["Montant"];

                    $Total["Total" . $codePoste . "||col||1"] = $rea;

                    $TotauxF["TOTAL" . $MesResultats[$codePoste . "||#||" . "UMoisRealise"]["Famille"] . "||col||1"] += $rea;

                    $TotauxSF["STOTAL" . $MesResultats[$codePoste . "||#||" . "UMoisRealise"]["Famille"] . "||col||1"] += $rea;

                }
            }
        }

        if ($MoisActuel) {

            $TotalCharges = NULL;

            //TOTAUX
            //echo "<pre>";
            //var_dump($TotauxF);
            $array1 = array(1 => "", 2 => "", 3 => "", 4 => "", 5 => "");

            foreach ($Total as $codeLigneTableau => $Valeur) {
                //recherche de la ligne Stotal pour lui affecter les valeurs
                $tab = explode("||col||", $codeLigneTableau);
                $MaCleTab1 = $tab[0];
                $Position = $tab[1];

                if ($MaCleTab1 != "TOTAL" && $MaCleTab1 != "STOTAL" && $MaCleTab1 != "Total" && $MesLignesTableau[$MaCleTab1]) {
                    $MaLigneTotalPoste = &$MesLignesTableau[$MaCleTab1];
                    $MaLigneTotalPoste[$Position] = array(StringHelper::NombreFr($Valeur) => array("style" => "background-color:#EEEEEE;font-weight:bolder;border-top:1px dotted black;padding:3px", "class" => "lnstotal", "align" => "right"));//
                    $MtabTran = array_diff_key($array1, $MaLigneTotal);

                    foreach ($MtabTran as $cle => $v) {
                        $MaLigneTotal[$cle] = $v;
                    }
                    ksort($MaLigneTotalPoste);
                }
            }

            foreach ($TotauxF as $codeLigneTableau => $Valeur) {
                //var_dump($codeLigneTableau);
                //recherche de la ligne total pour lui affecter les valeurs
                $tab = explode("||col||", $codeLigneTableau);
                $MaCleTab1 = $tab[0];
                $Position = $tab[1];

                if ($MaCleTab1 != "TOTAL" && $MaCleTab1 != "STOTAL" && $MesLignesTableau[$MaCleTab1])// pas egal a cela tout seul mais peut etre "TOTAL charge de ..."
                {
                    $MaLigneTotal = &$MesLignesTableau[$MaCleTab1];
                    $MaLigneTotal[$Position] = array(StringHelper::NombreFr($Valeur) => array("style" => "font-weight:bolder;", "align" => "right"));
                    $MtabTran = array_diff_key($array1, $MaLigneTotal);

                    foreach ($MtabTran as $cle => $v) {
                        $MaLigneTotal[$cle] = $v;
                    }
                    ksort($MaLigneTotal);
                }
            }
            //$array1 = array(1=>"",2=>"",3=>"",4=>array(""=>array("class"=>"colvide")),5=>"",6=>"",7=>"",8=>array(""=>array("class"=>"colvide")),9=>"",10=>"");

            foreach ($TotauxSF as $codeLigneTableau => $Valeur) {
                //recherche de la ligne Stotal pour lui affecter les valeurs
                $tab = explode("||col||", $codeLigneTableau);
                $MaCleTab1 = $tab[0];
                $Position = $tab[1];

                if ($MaCleTab1 != "TOTAL" && $MaCleTab1 != "STOTAL" && $MesLignesTableau[$MaCleTab1]) {
                    $MaLigneTotal = &$MesLignesTableau[$MaCleTab1];
                    $MaLigneTotal[$Position] = array(StringHelper::NombreFr($Valeur) => array("style" => "font-weight:bolder;font-weight: bolder;background-color:#EEEEEE;border-top:1px dotted black;", "align" => "right"));
                    $MtabTran = array_diff_key($array1, $MaLigneTotal);

                    foreach ($MtabTran as $cle => $v) {
                        $MaLigneTotal[$cle] = $v;
                    }
                    ksort($MaLigneTotal);

                    $TotalCharges[$Position] += $Valeur;
                }
            }
        }
        return $MesLignesTableau;
    }


}

?>