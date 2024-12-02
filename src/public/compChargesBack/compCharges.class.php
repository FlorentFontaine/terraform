<?php

use Facades\Modules\Commentaire\Commentaire;
use Helpers\StringHelper;

require_once __DIR__ . '/../dbClasses/AccesDonnees.php';

global $Arrondir;

$Arrondir = true;

class compChargesProd
{
    static function abrev($FamilleDef, $Type = '')
    {
        $FamilleDefStr = str_replace("CHARGES DE PERSONNEL ET DE GERANCE", "CHRG. PERS. ET GER.", $FamilleDef);
        $FamilleDefStr = str_replace("dotation aux amortissements", "dot. aux amort.", $FamilleDefStr);
        $FamilleDefStr = str_replace("Autres services exterieurs", "Autres services ext.", $FamilleDefStr);
        $FamilleDefStr = str_replace("Autres charges de gestion", "Autres chrg. de gest.", $FamilleDefStr);
        $FamilleDefStr = str_replace("CHARGES D'EXPLOITATION", "CHARGES D'EXP.", $FamilleDefStr);
        $FamilleDefStr = str_replace("Baie - MIDAS - Lavage manuel", "Baie Midas Lav.", $FamilleDefStr);
        $FamilleDefStr = str_replace("Accessoires", "Acces.", $FamilleDefStr);
        $FamilleDefStr = str_replace("automatiques", "auto.", $FamilleDefStr);
        $FamilleDefStr = str_replace("dï¿½tachï¿½es", "dï¿½tach.", $FamilleDefStr);
        $FamilleDefStr = str_replace("exceptionnels", "except.", $FamilleDefStr);
        $FamilleDefStr = str_replace("dotation amortis. et provisions", "dot. amort. prov.", $FamilleDefStr);
        $FamilleDefStr = str_replace("Autres services exterieurs", "Autres services ext.", $FamilleDefStr);
        $FamilleDefStr = str_replace("fourniture consommables", "fourniture cons.", $FamilleDefStr);
        $FamilleDefStr = str_replace("Taxe d'enlï¿½vement ordures mï¿½nagï¿½res", "Taxe d'enlï¿½v. ord. mï¿½nag.", $FamilleDefStr);
        $FamilleDefStr = str_replace("Dotations aux amortissement", "Dotations amort.", $FamilleDefStr);
        $FamilleDefStr = str_replace("Dotations aux provisions", "Dotations prov.", $FamilleDefStr);
        $FamilleDefStr = str_replace("exceptionnelles", "excep.", $FamilleDefStr);
        $FamilleDefStr = str_replace("PRODUITS HORS MANDAT", "PROD. HORS MANDAT", $FamilleDefStr);
        $FamilleDefStr = str_replace("CHARGES PERSONNEL ET GERANCE", "CHRG. PERS. ET GER.", $FamilleDefStr);
        $FamilleDefStr = str_replace("Carburants", "Volumes Carburants en M3", $FamilleDefStr);

        if ($Type == "Produits") {
            $FamilleDefStr = str_replace("ONFR", "CA ACTIVITES ANNEXES", $FamilleDefStr);
        } else {
            $FamilleDefStr = str_replace("ONFR", "ACTIVITES ANNEXES", $FamilleDefStr);
        }

        $FamilleDefStr = str_replace("VENTES MARCHANDISES", "CA BOUTIQUE ET AUTRES PRESTATIONS DE SERVICE", $FamilleDefStr);
        return str_replace("QUOTE PART", "Q.P.", $FamilleDefStr);
    }

    static function IniTableau(&$MesPostes, $Pourc = false, $AfficheMarges = true, $PourcPlusMarge = false, $TotauxMarge = true, $ReductionLib = false, $NbCols = 7, $ChargesONFR = false)
    {
        $MesLignesTableau = array();
        $PremF = true;
        $PremSF = true;
        $FaireSToataux = false;
        $SsFamilleDef = $FamilleDef = "";

        //initialisation du tableau avec ligne total + stotal de chaque poste
        foreach ($MesPostes as $codePoste => $UneLignePoste) {
            $UneLigneTableau = array();

            //pour savoir combien il y a de sous familles
            if ($UneLignePoste["SsFamille"] != $SsFamilleDef && $UneLignePoste["Famille"] == $FamilleDef) {
                $FaireSToataux = true;
            }

            //changement de Sousfamille
            if ($UneLignePoste["SsFamille"] != $SsFamilleDef) {
                $NameTotal = "";

                if (!$PremSF && $SsFamilleDef != $FamilleDef && $FaireSToataux) {
                    //LnsTotal
                    $SsFamilleDefStr = self::abrev($SsFamilleDef);

                    $Nom = explode("||#||", $SsFamilleDefStr);

                    if (!$NameTotal) {
                        if (count($Nom) > 1) {
                            $Nom = "Sous total :";
                        } elseif ($FamilleDef == "VENTES MARCHANDISES") {
                            $Nom = "CA " . $SsFamilleDefStr . " :";
                        } else {
                            $Nom = "" . $SsFamilleDefStr . " :";
                        }
                    } else {
                        $Nom = $NameTotal;
                    }

                    $UneLigneTableau[] = array($Nom => array("libelle" => "1", 'align' => 'right', 'style' => 'font-weight: bolder', 'name' => "mytr"));
                    $MesLignesTableau["STOTAL" . $SsFamilleDef] = $UneLigneTableau;

                    $UneLigneTableau = array();
                }

                $SsFamilleDef = $UneLignePoste["SsFamille"];
            }

            //changement de famille
            if ($UneLignePoste["Famille"] != $FamilleDef) {
                if (!$PremF) {
                    //LnTotal
                    $FamilleDefStr = self::abrev($FamilleDef);

                    if ($FamilleDef == "VENTES MARCHANDISES") {
                        $UneLigneTableau[] = array("TOTAL CA :" => array("libelle" => "1", 'style' => 'font-weight: bolder'));
                    } else {
                        $UneLigneTableau[] = array("TOTAL " . $FamilleDefStr . " :" => array("libelle" => "1", 'style' => 'font-weight: bolder'));
                    }
                    $MesLignesTableau["TOTAL" . $FamilleDef] = $UneLigneTableau;

                    $UneLigneTableau = array();

                    if ($FamilleDef == "VENTES MARCHANDISES" && $UneLignePoste["Type"] == "Produits" && $AfficheMarges) {
                        $TabMarge = true;

                        $EcarMarge = true;
                        if ($Pourc && $PourcPlusMarge) {
                            $EcarMarge = false;
                        }

                        $MesLignesTableau = compChargesProd::setTabMarges($MesLignesTableau, $Pourc, $EcarMarge, $TotauxMarge, $NbCols);

                        if ($PourcPlusMarge) {
                            $MesLignesTableau = compChargesProd::setTabMarges($MesLignesTableau, false, true, false, $NbCols);
                        }
                    }
                }

                $NewLibelle = self::abrev($UneLignePoste["Famille"], $UneLignePoste["Type"]);

                //ln nom famille
                if (!$ReductionLib) {
                    $UneLigneTableau[] = array($NewLibelle => array("libelle" => "1", "style" => "font-weight: bolder;text-align:left;", "colspan" => $NbCols, "class" => "sticky"));
                } else {
                    $NewLibelle = substr($NewLibelle, 0, 30);

                    if (strlen($NewLibelle) > 30) {
                        $NewLibelle .= "...";
                    }

                    $UneLigneTableau[] = array($NewLibelle => array("libelle" => "1", "style" => "font-weight: bolder;text-align:left;", "colspan" => $NbCols, "class" => "sticky"));
                }

                $MesLignesTableau["TITRE" . $UneLignePoste["Famille"]] = $UneLigneTableau;

                $UneLigneTableau = array();
                $FamilleDef = $UneLignePoste["Famille"];
                $PremSF = true;
                $FaireSToataux = false;
            }

            $UneLignePoste["Libelle"] = self::abrev($UneLignePoste["Libelle"], $UneLignePoste["Type"]);

            if ($ReductionLib) {
                $NewLibelle = substr($UneLignePoste["Libelle"], 0, 30);

                if (strlen($UneLignePoste["Libelle"]) > 30) {
                    $NewLibelle .= "...";
                }

                $UneLignePoste["Libelle"] = $NewLibelle;
            }

            $data = [
                "key" => $UneLignePoste["codePoste"],
                "value" => $UneLignePoste["Libelle"]
            ];


            if ($UneLignePoste["codePoste"]) {
                $UneLigneTableau[] = array(
                    "<span data-type='poste' data-id='" . $UneLignePoste["codePoste"] . "' class='detail_tooltip' >&#8505;</span>" .   Commentaire::actionOnPoste($data)
                    => array("libelle" => "1", "class" => "LibelleTab"));

                    
            } elseif ($UneLignePoste["codePoste_synthese"]) {
                $UneLigneTableau[] = array(
                    $UneLignePoste["Libelle"]
                    => array("libelle" => "1", "class" => "LibelleTab"));
            } else {
                $UneLigneTableau[] = array($UneLignePoste["Libelle"] => array("libelle" => "1", "class" => "LibelleTab"));
            }

            $MesLignesTableau[$codePoste] = $UneLigneTableau;

            if ($PremSF) {
                $PremSF = false;
            }
            if ($PremF) {
                $PremF = false;
            }
        }

        //LnsTotal
        if ($FaireSToataux || ($UneLignePoste["Type"] == "Produits" && $UneLignePoste["Famille"] == "AUTRES PRESTATIONS DE SERVICES" && $FamilleDef == "AUTRES PRESTATIONS DE SERVICES" && $ChargesONFR)) {
            $NameTotal = "";
            $SsFamilleDefStr = self::abrev($SsFamilleDef);

            $Nom = explode("||#||", $SsFamilleDef);

            if (!$NameTotal) {
                if (count($Nom) > 1) {
                    $Nom = "Sous total :";
                } else {
                    $Nom = "Total " . $SsFamilleDefStr . " :";
                }
            } else {
                $Nom = $NameTotal;
            }

            $UneLigneTableau = array();
            $UneLigneTableau[] = array($Nom => array("libelle" => "1", 'align' => 'right', 'style' => 'font-weight: bolder'));

            $MesLignesTableau["STOTAL" . $SsFamilleDef] = $UneLigneTableau;
        }

        $UneLigneTableau = array();

        if ($FamilleDef == "VENTES MARCHANDISES") {
            $UneLigneTableau[] = array("TOTAL CA :" => array("libelle" => "1", 'style' => 'font-weight: bolder'));
        } else {
            $UneLigneTableau[] = array("TOTAL " . self::abrev($FamilleDef) . " :" => array("libelle" => "1", 'style' => 'font-weight: bolder'));
        }

        $MesLignesTableau["TOTAL" . $FamilleDef] = $UneLigneTableau;

        if ($AfficheMarges && ($FamilleDef == "AUTRES PRESTATIONS DE SERVICES" && $UneLignePoste["Type"] == "Produits" || ($FamilleDef == "VENTES MARCHANDISES" && $UneLignePoste["Type"] == "Produits"))) {
            $EcarMarge = true;
            if ($Pourc && $PourcPlusMarge) {
                $EcarMarge = false;
            }

            $MesLignesTableau = compChargesProd::setTabMarges($MesLignesTableau, $Pourc, $EcarMarge, $TotauxMarge, $NbCols, "TITRE$FamilleDef", $Charges);

            if ($PourcPlusMarge) {
                $MesLignesTableau = compChargesProd::setTabMarges($MesLignesTableau, false, true, false, $NbCols);
            }
        }

        return $MesLignesTableau;
    }


    static function getTabBench($Type, $MoisDeb, $MoisFin, $optRestri, $etude)
    {
        require_once __DIR__ . '/../BenchMark/benchmark.class.php';

        $BenchMark = $etude === "benchmark";

        $simpleRea = $lieNum = false;
        if ($BenchMark && (isset($optRestri["LIE_NUM"]) && $optRestri["LIE_NUM"])) {
            $simpleRea = true;
            $lieNum = $optRestri["LIE_NUM"];
        }

        $MonBench = Benchmark::getBench($MoisDeb, $MoisFin, $Type, $lieNum, $simpleRea, $optRestri);

        $Where = array("and" =>
            array(
                "compteposte.type" => "='$Type' ",
            )
        );

        if (isset($codePosteSelect) && $codePosteSelect) {
            $plus = &$Where["and"];
            $plus["comptePoste.codePoste"] = "='$codePosteSelect'";
        }

        if (isset($SsFamilleSelect) && $SsFamilleSelect) {
            $plus = &$Where["and"];
            $plus["comptePoste.SsFamille"] = "=\"$SsFamilleSelect\"";
        }

        if (isset($FamilleSelect) && $FamilleSelect) {
            $plus = &$Where["and"];
            $plus["comptePoste.Famille"] = "=\"$FamilleSelect\"";
        }

        $tri = array(
            "ordre" => "ASC"
        );

        $MesPostes = dbAcces::getPosteVisible($Where, $tri);
        $iniTableau = compChargesProd::IniTableau($MesPostes, false, true, false, true, false, ($BenchMark) ? 8 : 7);
        $MesLignesTableau = &$iniTableau;
        $MesFamille = dbAcces::getFamilleSFamillePoste();

        $allCodesCompte = array_keys($MesLignesTableau);

        if ($BenchMark) {
            if ($Type == "Produits") {
                list($MesPosteMoins, $MesPostePlus) = Benchmark::getMinMaxPosteStation($MoisDeb, $MoisFin, $allCodesCompte, "0", $optRestri);
                // Marges
                list($MesMargesMoins, $MesMargesPlus) = Benchmark::getMinMaxPosteStation($MoisDeb, $MoisFin, $allCodesCompte, "1", $optRestri);
                $MesMargesMoy = Benchmark::getMoyPosteStation($MoisDeb, $MoisFin, $allCodesCompte, "1", $optRestri);
            } else {
                //on inverse le sens
                list($MesPostePlus, $MesPosteMoins) = Benchmark::getMinMaxPosteStation($MoisDeb, $MoisFin, $allCodesCompte, "0", $optRestri);
            }

            $MesPosteMoy = Benchmark::getMoyPosteStation($MoisDeb, $MoisFin, $allCodesCompte, "0", $optRestri);
        }

        foreach ($MesLignesTableau as $codeCompte => &$UneLigneDb) {
            if (!isset($MonBench[$codeCompte . "||#||0"]["BPC_REA"]) || !$MonBench[$codeCompte . "||#||0"]["BPC_REA"]) {
                $MonBench[$codeCompte . "||#||0"]["BPC_REA"] = 0;
            }

            if (!isset($MonBench[$codeCompte . "||#||0"]["BPC_PREV"]) || !$MonBench[$codeCompte . "||#||0"]["BPC_PREV"]) {
                $MonBench[$codeCompte . "||#||0"]["BPC_PREV"] = 0;
            }

            if (!isset($MonBench[$codeCompte . "||#||0"]["BPC_N1"]) || !$MonBench[$codeCompte . "||#||0"]["BPC_N1"]) {
                $MonBench[$codeCompte . "||#||0"]["BPC_N1"] = 0;
            }

            if (!isset($MonBench[$codeCompte . "||#||1"]["BPC_REA"]) || !$MonBench[$codeCompte . "||#||1"]["BPC_REA"]) {
                $MonBench[$codeCompte . "||#||1"]["BPC_REA"] = 0;
            }

            if (!isset($MonBench[$codeCompte . "||#||1"]["BPC_PREV"]) || !$MonBench[$codeCompte . "||#||1"]["BPC_PREV"]) {
                $MonBench[$codeCompte . "||#||1"]["BPC_PREV"] = 0;
            }

            if (!isset($MonBench[$codeCompte . "||#||1"]["BPC_N1"]) || !$MonBench[$codeCompte . "||#||1"]["BPC_N1"]) {
                $MonBench[$codeCompte . "||#||1"]["BPC_N1"] = 0;
            }

            if (is_numeric($codeCompte) || strpos( $codeCompte, 'ECARTMARGE') !== false) {
                if (strpos($codeCompte, 'ECARTMARGE') !== false) {
                    $codeCompte = str_replace("ECARTMARGE", "", $codeCompte);
                    $codeCompte = -$codeCompte;
                }

                if (!isset($MesPosteMoy[$codeCompte . "||#||0"]["BPC_MOY"]) || !$MesPosteMoy[$codeCompte . "||#||0"]["BPC_MOY"]) {
                    $MesPosteMoy[$codeCompte . "||#||0"]["BPC_MOY"] = 0;
                }

                if (!isset($MesPostePlus[$codeCompte . "||#||0"]["BPC_REA"]) || !$MesPostePlus[$codeCompte . "||#||0"]["BPC_REA"]) {
                    $MesPostePlus[$codeCompte . "||#||0"]["BPC_REA"] = 0;
                }

                if (!isset($MesPostePlus[$codeCompte . "||#||0"]["LIE_NOM"]) || !$MesPostePlus[$codeCompte . "||#||0"]["LIE_NOM"]) {
                    $MesPostePlus[$codeCompte . "||#||0"]["LIE_NOM"] = '';
                }

                if (!isset($MesPosteMoins[$codeCompte . "||#||0"]["BPC_REA"]) || !$MesPosteMoins[$codeCompte . "||#||0"]["BPC_REA"]) {
                    $MesPosteMoins[$codeCompte . "||#||0"]["BPC_REA"] = 0;
                }

                if (!isset($MesPosteMoins[$codeCompte . "||#||0"]["LIE_NOM"]) || !$MesPosteMoins[$codeCompte . "||#||0"]["LIE_NOM"]) {
                    $MesPosteMoins[$codeCompte . "||#||0"]["LIE_NOM"] = '';
                }

                $rea = $MonBench[$codeCompte . "||#||0"]["BPC_REA"];
                $prev = $MonBench[$codeCompte . "||#||0"]["BPC_PREV"];
                $anm1 = $MonBench[$codeCompte . "||#||0"]["BPC_N1"];
                $moy = $MesPosteMoy[$codeCompte . "||#||0"]["BPC_MOY"];
                $max = $MesPostePlus[$codeCompte . "||#||0"]["BPC_REA"];
                $min = $MesPosteMoins[$codeCompte . "||#||0"]["BPC_REA"];

                $StaMoins = addslashes($MesPosteMoins[$codeCompte . "||#||0"]["LIE_NOM"]);
                $StaPlus = addslashes($MesPostePlus[$codeCompte . "||#||0"]["LIE_NOM"]);

                if ($Type == "Charges") {
                    $rea = -$rea;
                    $prev = -$prev;
                    $anm1 = -$anm1;
                    $moy = -$moy;
                    $max = -$max;
                    $min = -$min;
                }

                if (isset($optRestri["color"]) && $optRestri["color"]) {
                    if (($rea >= $moy && $Type == "Produits") || ($rea <= $moy && $Type == "Charges")) {
                        $StyleRea = array("align" => "right", "style" => "background-color:#DFFFDF");
                    } else {
                        $StyleRea = array("align" => "right", "style" => "background-color:#FFCECE");
                    }
                } else {
                    $StyleRea = array("align" => "right");
                }

                if ($BenchMark) {
                    $UneLigneDb[] = array(StringHelper::NombreFr($rea) => $StyleRea);
                    $UneLigneDb[] = "";
                    $UneLigneDb[] = array(StringHelper::NombreFr($min) => array("align" => "right", "title" => $StaMoins, "style" => "cursor:help"));
                    $UneLigneDb[] = array(StringHelper::NombreFr($moy) => array("align" => "right"));
                    $UneLigneDb[] = array(StringHelper::NombreFr($max) => array("align" => "right", "title" => $StaPlus, "style" => "cursor:help"));
                    $UneLigneDb[] = "";
                    $UneLigneDb[] = array(StringHelper::Signe($rea - $moy) => array("align" => "right"));
                } else {
                    $UneLigneDb[] = array(StringHelper::NombreFr($rea) => $StyleRea);
                    $UneLigneDb[] = array(StringHelper::NombreFr($prev) => array("align" => "right"));
                    $UneLigneDb[] = array(StringHelper::NombreFr($anm1) => array("align" => "right"));
                    $UneLigneDb[] = "";
                    $UneLigneDb[] = array(StringHelper::Signe($rea - $prev) => array("align" => "right"));
                    $UneLigneDb[] = array(StringHelper::Signe($rea - $anm1) => array("align" => "right"));
                }

                if (!isset($TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||1"]) || !$TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||1"]) {
                    $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||1"] = 0;
                }

                if (!isset($TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||1"]) || !$TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||1"]) {
                    $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||1"] = 0;
                }

                if ($BenchMark) {
                    if (!isset($TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||4"]) || !$TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||4"]) {
                        $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||4"] = 0;
                    }

                    if (!isset($TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||7"]) || !$TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||7"]) {
                        $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||7"] = 0;
                    }

                    if (!isset($TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||4"]) || !$TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||4"]) {
                        $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||4"] = 0;
                    }

                    if (!isset($TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||7"]) || !$TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||7"]) {
                        $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||7"] = 0;
                    }

                    $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||1"] += $rea;
                    $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||4"] += $moy;
                    $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||7"] += $rea - $moy;

                    $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||1"] += $rea;
                    $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||4"] += $moy;
                    $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||7"] += $rea - $moy;
                } else {
                    if (!isset($TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||2"]) || !$TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||2"]) {
                        $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||2"] = 0;
                    }

                    if (!isset($TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||3"]) || !$TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||3"]) {
                        $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||3"] = 0;
                    }

                    if (!isset($TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||5"]) || !$TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||5"]) {
                        $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||5"] = 0;
                    }

                    if (!isset($TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||6"]) || !$TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||6"]) {
                        $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||6"] = 0;
                    }

                    if (!isset($TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||2"]) || !$TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||2"]) {
                        $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||2"] = 0;
                    }

                    if (!isset($TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||3"]) || !$TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||3"]) {
                        $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||3"] = 0;
                    }

                    if (!isset($TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||5"]) || !$TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||5"]) {
                        $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||5"] = 0;
                    }

                    if (!isset($TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||6"]) || !$TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||6"]) {
                        $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||6"] = 0;
                    }

                    $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||1"] += $rea;
                    $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||2"] += $prev;
                    $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||3"] += $anm1;
                    $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||5"] += $rea - $prev;
                    $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||6"] += $rea - $anm1;

                    $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||1"] += $rea;
                    $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||2"] += $prev;
                    $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||3"] += $anm1;
                    $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||5"] += $rea - $prev;
                    $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||6"] += $rea - $anm1;
                }
            } elseif (strpos($codeCompte, 'CMARGE') !== false) {
                $codeCompte = str_replace("CMARGE","",$codeCompte);

                if (!isset($MesMargesMoy[$codeCompte . "||#||1"]["BPC_MOY"]) || !$MesMargesMoy[$codeCompte . "||#||1"]["BPC_MOY"]) {
                    $MesMargesMoy[$codeCompte . "||#||1"]["BPC_MOY"] = 0;
                }

                if (!isset($MesMargesPlus[$codeCompte . "||#||1"]["BPC_REA"]) || !$MesMargesPlus[$codeCompte . "||#||1"]["BPC_REA"]) {
                    $MesMargesPlus[$codeCompte . "||#||1"]["BPC_REA"] = 0;
                }

                if (!isset($MesMargesMoins[$codeCompte . "||#||1"]["BPC_REA"]) || !$MesMargesMoins[$codeCompte . "||#||1"]["BPC_REA"]) {
                    $MesMargesMoins[$codeCompte . "||#||1"]["BPC_REA"] = 0;
                }
                
                

                $rea = $MonBench[$codeCompte . "||#||1"]["BPC_REA"];
                $prev = $MonBench[$codeCompte . "||#||1"]["BPC_PREV"];
                $anm1 = $MonBench[$codeCompte . "||#||1"]["BPC_N1"];
                $moy = $MesMargesMoy[$codeCompte . "||#||1"]["BPC_MOY"];
                $max = $MesMargesPlus[$codeCompte . "||#||1"]["BPC_REA"];
                $min = $MesMargesMoins[$codeCompte . "||#||1"]["BPC_REA"];

                if (isset($optRestri["color"]) && $optRestri["color"]) {
                    if (($rea >= $moy && $Type == "Produits") || ($rea <= $moy && $Type == "Charges")) {
                        $StyleRea = array("align" => "right", "style" => "background-color:#DFFFDF");
                    } else {
                        $StyleRea = array("align" => "right", "style" => "background-color:#FFCECE");
                    }
                } else {
                    $StyleRea = array("align" => "right");
                }

                $UneLigneDb[] = array(StringHelper::NombreFr($rea) => $StyleRea);

                if ($BenchMark) {
                    $UneLigneDb[] = "";
                    $UneLigneDb[] = array(StringHelper::NombreFr($min) => array("align" => "right"));
                    $UneLigneDb[] = array(StringHelper::NombreFr($moy) => array("align" => "right"));
                    $UneLigneDb[] = array(StringHelper::NombreFr($max) => array("align" => "right"));
                    $UneLigneDb[] = "";
                    $UneLigneDb[] = array(StringHelper::NombreFr($rea - $moy) => array("align" => "right"));
                } else {
                    $UneLigneDb[] = array(StringHelper::NombreFr($prev) => array("align" => "right"));
                    $UneLigneDb[] = array(StringHelper::NombreFr($anm1) => array("align" => "right"));
                    $UneLigneDb[] = "";
                    $UneLigneDb[] = array(StringHelper::NombreFr($rea - $prev) => $StyleRea);
                    $UneLigneDb[] = array(StringHelper::NombreFr($rea - $anm1) => array("align" => "right"));
                }

                if (!isset($TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||1"]) || !$TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||1"]) {
                    $TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||1"] = 0;
                }

                if (!isset($TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||1"]) || !$TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||1"]) {
                    $TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||1"] = 0;
                }

                if ($BenchMark) {
                    if (!isset($TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||4"]) || !$TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||4"]) {
                        $TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||4"] = 0;
                    }

                    if (!isset($TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||7"]) || !$TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||7"]) {
                        $TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||7"] = 0;
                    }

                    if (!isset($TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||4"]) || !$TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||4"]) {
                        $TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||4"] = 0;
                    }

                    if (!isset($TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||7"]) || !$TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||7"]) {
                        $TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||7"] = 0;
                    }
                    $TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||1"] += $rea;
                    $TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||4"] += $moy;
                    $TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||7"] += $rea - $moy;

                    $TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||1"] += $rea;
                    $TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||4"] += $moy;
                    $TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||7"] += $rea - $moy;
                } else {
                    if (!isset($TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||2"]) || !$TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||2"]) {
                        $TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||2"] = 0;
                    }

                    if (!isset($TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||3"]) || !$TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||3"]) {
                        $TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||3"] = 0;
                    }

                    if (!isset($TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||5"]) || !$TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||5"]) {
                        $TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||5"] = 0;
                    }

                    if (!isset($TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||6"]) || !$TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||6"]) {
                        $TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||6"] = 0;
                    }

                    if (!isset($TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||2"]) || !$TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||2"]) {
                        $TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||2"] = 0;
                    }

                    if (!isset($TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||3"]) || !$TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||3"]) {
                        $TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||3"] = 0;
                    }

                    if (!isset($TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||5"]) || !$TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||5"]) {
                        $TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||5"] = 0;
                    }

                    if (!isset($TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||6"]) || !$TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||6"]) {
                        $TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||6"] = 0;
                    }
                    $TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||1"] += $rea;
                    $TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||2"] += $prev;
                    $TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||3"] += $anm1;
                    $TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||5"] += $rea - $prev;
                    $TotauxF["MARGETOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||6"] += $rea - $anm1;

                    $TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||1"] += $rea;
                    $TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||2"] += $prev;
                    $TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||3"] += $anm1;
                    $TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||5"] += $rea - $prev;
                    $TotauxSF["MARGESTOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||6"] += $rea - $anm1;
                }
            }
        }

        if ($BenchMark) {
            $array1 = array(1 => "", 2 => array("" => array("class" => "colvide")), 3 => "", 4 => "", 5 => "", 6 => array("" => array("class" => "colvide")), 7 => "");
        } else {
            $array1 = array(1 => "", 2 => "", 3 => "", 4 => array("" => array("class" => "colvide")), 5 => "", 6 => "");
        }

        foreach ($TotauxSF as $codeLigneTableau => $Valeur) {
            //recherche de la ligne Stotal pour lui affecter les valeurs
            $tab = explode("||col||", $codeLigneTableau);

            $MaCleTab1 = $tab[0];
            $Position = $tab[1];

            if ($MaCleTab1 != "TOTAL" && $MaCleTab1 != "STOTAL" && $MesLignesTableau[$MaCleTab1]) {
                $MaLigneTotal = &$MesLignesTableau[$MaCleTab1];
                $MaLigneTotal[$Position] = array(StringHelper::NombreFr($Valeur) => array("style" => "font-weight:bolder;", "align" => "right"));
                $MtabTran = array_diff_key($array1, $MaLigneTotal);

                foreach ($MtabTran as $cle => $v) {
                    if (is_array($v) || is_array($array1[$cle])) {
                        $MaLigneTotal[$cle] = $v;
                    } else {
                        $MaLigneTotal[$cle] = array("" => "");
                    }
                }

                ksort($MaLigneTotal);
            }
        }

        foreach ($TotauxF as $codeLigneTableau => $Valeur) {
            //recherche de la ligne total pour lui affecter les valeurs
            $tab = explode("||col||", $codeLigneTableau);

            $MaCleTab1 = $tab[0];
            $Position = $tab[1];

            // pas egal a cela tout seul mais peut etre "TOTAL charge de ..."
            if ($MaCleTab1 != "TOTAL" && $MaCleTab1 != "STOTAL" && $MesLignesTableau[$MaCleTab1]) {
                $MaLigneTotal = &$MesLignesTableau[$MaCleTab1];
                $MaLigneTotal[$Position] = array(StringHelper::NombreFr($Valeur) => array("style" => "font-weight:bolder;", "align" => "right"));
                $MtabTran = array_diff_key($array1, $MaLigneTotal);

                foreach ($MtabTran as $cle => $v) {
                    if (is_array($v) || is_array($array1[$cle])) {
                        $MaLigneTotal[$cle] = $v;
                    } else {
                        $MaLigneTotal[$cle] = array("" => "");
                    }
                }

                ksort($MaLigneTotal);

                //pour ajout du sous total precedent sur le suivant
                if ($MaCleTab1 != "TOTALVENTES MARCHANDISES") {
                    if (!isset($TotalCharges[$Position])) {
                        $TotalCharges[$Position] = 0;
                    }

                    $TotalCharges[$Position] += $Valeur;
                }
            }
        }

        if ($BenchMark) {
            $array1 = array(1 => "", 2 => array("" => array("class" => "colvide")), 3 => "", 4 => "", 5 => "", 6 => array("" => array("class" => "colvide")), 7 => "");
        } else {
            $array1 = array(1 => "", 2 => "", 3 => "", 4 => array("" => array("class" => "colvide")), 5 => "", 6 => "");
        }

        $LnBigTotal = array();
        $LnBigTotal[0] = array("Total des $Type :" => array("libelle" => 1, "style" => "font-weight:bolder;font-size:11px;", "align" => "right"));

        for ($i = 1; $i <= count($array1); $i++) {
            if (isset($TotalCharges[$i]) && $TotalCharges[$i]) {
                $LnBigTotal[$i] = array(StringHelper::NombreFr($TotalCharges[$i]) => array("style" => "font-weight:bolder;font-size:11px;", "align" => "right"));
            }
        }

        $MesLignesTableau[] = "";
        $MtabTran = array_diff_key($array1, $LnBigTotal);

        foreach ($MtabTran as $cle => $v) {
            if (is_array($v) || is_array($array1[$cle])) {
                $LnBigTotal[$cle] = $v;
            } else {
                $LnBigTotal[$cle] = array("" => "");
            }
        }

        ksort($LnBigTotal);

        $MesLignesTableau["BIGTOTAL"] = $LnBigTotal;

        if ($Type == "Charges") {
            $MesLignesTableau[] = "";
            $Resultat[0] = array("R&eacute;sultat :" => array("libelle" => 1, "style" => "font-weight:bolder;font-size:11px;", "align" => "right"));

            foreach ($_SESSION["TotalProduit"] as $cle => $Montant) {
                $Resultat[$cle] = array(StringHelper::NombreFr($Montant - $TotalCharges[$cle]) => array("style" => "font-weight:bolder;font-size:11px;", "align" => "right"));
            }

            if ($BenchMark) {
                $array1 = array(1 => "", 2 => array("" => array("class" => "colvide")), 3 => "", 4 => "", 5 => "", 6 => array("" => array("class" => "colvide")), 7 => "");
            } else {
                $array1 = array(1 => "", 2 => "", 3 => "", 4 => array("" => array("class" => "colvide")), 5 => "", 6 => "");
            }


            $MtabTran = array_diff_key($array1, $Resultat);

            foreach ($MtabTran as $cle => $v) {
                if (is_array($v) || is_array($array1[$cle])) {
                    $Resultat[$cle] = $v;
                } else {
                    $Resultat[$cle] = array("" => "");
                }
            }

            ksort($Resultat);

            $MesLignesTableau["TOTALRESULTAT"] = $Resultat;
        } else {
            $_SESSION["TotalProduit"] = $TotalCharges;
        }

        //si la dernière famille a le même nom que les types (charges ou produits), on n'affiche pas ce total
        unset($MesLignesTableau["TOTALCharges"]);

        return $MesLignesTableau;
    }


    static $TableNumber = 0;

    static function display_EnteteTab($titleTable, $Type, $cluster, $Intitule = NULL, $Imprimer = NULL)
    {
        self::$TableNumber++;

        $FREEZPLAN = 2;
        $OptionPlus = "FITHEIGHT:1;";

        if ($cluster) {
            $FREEZPLAN++;
            $ROWTOREPEAT++;
        }

        ?>
    <table
        dir="IMP_PDF;TITLETABLE:<?php echo $titleTable; ?>;FREEZEPLAN:I<?php echo $FREEZPLAN; ?>;ROWTOREPEAT:3,3;<?php echo $OptionPlus; ?>"
        style="width:0px" class="tabBalance" bordercolordark="#000000" bordercolorlight="#000000" align="center"
        id="tab_<?php echo $Type . self::$TableNumber ?>">
        <thead>
        <?php if ($Intitule && !$Imprimer) {
            echo '<tr><td colspan="100" class="tdfixe" style="text-align:center;font-weight:bold">' . $Intitule . '</td></tr>';
        } ?>
        <?php if ($cluster) { ?>
            <tr>
                <td class="EnteteTab">
                    Stations du CLUSTER :
                </td>
                <td colspan="7" style="padding: 5px;white-space: normal;width: 1px" align="center">
                    <?php
                    $MesCluster = dbAcces::get_LieByCluster($_SESSION["station_STA_NUM_CLUSTER"]);

                    $MesLieu = array();

                    foreach ($MesCluster as $LIE_NUM => $MonLieu) {
                        $MesLieu[] = $MonLieu["LIE_NOM"];
                    }

                    echo "<b>" . implode(" / ", $MesLieu) . "</b>";
                    ?>
                </td>
            </tr>
        <?php } ?>
        <tr class="EnteteTab">
            <td class="tdfixe" width="140"><?php echo $Type; ?>
                <div class='div200'></div>
            </td>
            <td width="40" class="tdfixe">R&eacute;alis&eacute;<div style="width: 90px;height:0px;"></div>
            </td>
            <td width="40" class="tdfixe">Pr&eacute;vu
                <div style="width: 90px;height:0px;"></div>
            </td>
            <td width="40" class="tdfixe">N-1
                <div style="width: 90px;height:0px;"></div>
            </td>
            <td class="tdfixe" width="1"></td>

            <td width="40" class="tdfixe">R&eacute;a.-Pr&eacute;v.
                <div style="width: 90px;height:0px;"></div>
            </td>
            <td width="40" class="tdfixe">R&eacute;a.-(N-1)
                <div style="width: 90px;height:0px;"></div>
            </td>

        </tr>
        </thead>
        <tbody>
        <?php
    }

    static function getTab($Type, $MoisActuel, $FamilleSelect = false, $SsFamilleSelect = false, $codePosteSelect = false, $opt = NULL)
    {
        if ($opt["MAJBASE"]) {//pour avoir le N-1 ds la conso sur -12mois
            $HOLD_station_DOS_PREMDATECP = $_SESSION["station_DOS_PREMDATECP"];
            $_SESSION["station_DOS_PREMDATECP"] = false;
        }

        $TypeCompte = array("test" => "!=", "type" => "achat");//pour la rï¿½cupï¿½ration des rï¿½sultat avec tout les compte de la balance sauf les compte d'achat

        $Where = array(
            "and" =>
                array(
                    "compteposte.type" => "='$Type' "
                ),
            array(
                "compteposte.Famille" => "!='Carburants' "
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
        
        $MesPostes = dbAcces::getPoste($Where,$tri);
        
        $MesLignesTableau = &compChargesProd::IniTableau($MesPostes,false,true,false,true,false,1,true);

        //mise en place des rï¿½sultats + totauxs + sTotaux
        $NbMoisEcart = StringHelper::GetNbMoisEcart(date("Ym", strtotime($_SESSION["station_DOS_DEBEX"])), date("Ym", strtotime(str_replace('-00', '-01', $MoisActuel))));

        if ($_SESSION["station_DOS_PREMDATECP"] > 0) {
            $MoisAnneePrec = StringHelper::DatePlus(str_replace("-00", "-01", $_SESSION["station_DOS_PREMDATECP"]), array("moisplus" => $NbMoisEcart, "dateformat" => "Y-m-00"));
        } else {
            $MoisAnneePrec = StringHelper::DatePlus(str_replace("-00", "-01", $MoisActuel), array("moisplus" => -12, "dateformat" => "Y-m-00"));
        }

        if ($opt['mensuel']) {
            $MesResultats = dbAcces::getResultatsPoste($MoisActuel, true, true, true, false, $codePosteSelect, $Type, $TypeCompte, false, false, false, $Contenu, $opt["cluster"]);
        } else {
            $MesResultatsCumul = dbAcces::getResultatsPoste($MoisActuel, true, true, true, true, $codePosteSelect, $Type, $TypeCompte, false, false, false, $Contenu, $opt["cluster"]);
        }

        $MonPrev = dbAcces::getPrev($_SESSION["station_DOS_NUM"],$MoisActuel,$Type,false,false,false,false,false,false,false,false,false,false,$opt["cluster"]);

        if($Type == "Produits")
                    $MonPrevCumul = dbAcces::getPrev($_SESSION["station_DOS_NUM"],$MoisActuel,$Type,true,$codePosteMarge,false,false,false,false,false,false,false,true,$opt["cluster"]);


        $MesFamille = dbAcces::getFamilleSFamillePoste();

        if ($Type == "Produits") {
            if ($opt['mensuel']) {
                $MesMarges = dbAcces::getMargeTheoriqueGroupe($MoisActuel, $_SESSION["station_DOS_NUM"], false, false, FALSE, $opt["cluster"]);
                $MesMargesN1 = dbAcces::getMargeTheoriqueGroupe($MoisActuel, NULL, false, true, false, $opt["cluster"]);
            } else {
                $MesMargesCumul = dbAcces::getMargeTheoriqueGroupe($MoisActuel, $_SESSION["station_DOS_NUM"], true, false, false, $opt["cluster"]);
                $MesMargesCumulN1 = dbAcces::getMargeTheoriqueGroupe($MoisActuel, NULL, true, true, false, $opt["cluster"]);
            }
        }

        $TotalCharges = NULL;
        $allCompte = true;

        foreach ($MesLignesTableau as $codeCompte => &$UneLigneDb) {
            if (is_numeric($codeCompte)) {
                if ($opt['mensuel']) {
                    $rea = $MesResultats[$codeCompte . "||#||" . "UMoisRealise"]["Montant"];
                    $anm1 = $MesResultats[$codeCompte . "||#||" . "UMoisAnneeMoinsUn"]["Montant"];
                    $prev = $MesResultats[$codeCompte . "||#||" . "UMoisPrevu"]["Montant"];
                } else {
                    $rea = $MesResultatsCumul[$codeCompte . "||#||" . "UMoisRealise"]["Montant"];
                    $anm1 = $MesResultatsCumul[$codeCompte . "||#||" . "UMoisAnneeMoinsUn"]["Montant"];
                    $prev = $MesResultatsCumul[$codeCompte . "||#||" . "UMoisPrevu"]["Montant"];
                }

                if ($Type == "Produits") {
                    $rea = -$rea;
                    $anm1 = -$anm1;
                }

                $UneLigneDb[] = array(StringHelper::NombreFr($rea) => array("align" => "right"));
                $UneLigneDb[] = array(StringHelper::NombreFr($prev) => array("align" => "right"));
                $UneLigneDb[] = array(StringHelper::NombreFr($anm1) => array("align" => "right"));
                $UneLigneDb[] = "";

                $StyleRea = array("align" => "right");

                if ($Contenu["UMoisPrevu"]) {
                    $UneLigneDb[] = array(StringHelper::Signe($rea - $prev) => $StyleRea);
                } else {
                    $UneLigneDb[] = array("" => $StyleRea);
                }

                if ($_SESSION["station_DOS_PREMDATECP"] > 0) {
                    $StyleRea = array("align" => "right");
                    $UneLigneDb[] = array(StringHelper::Signe($rea - $anm1) => $StyleRea);
                } else {
                    $UneLigneDb[] = array("" => $StyleRea);
                }


                $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||1"] += $TotRea = $rea;
                $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||2"] += $TotPrev = $prev;
                $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||3"] += $TotN1 = $anm1;

                $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||1"] += $rea;
                $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||2"] += $prev;
                $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||3"] += $anm1;

                if ($Contenu["UMoisPrevu"]) {
                    $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||5"] += ($rea - $prev);
                    $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||5"] += ($rea - $prev);
                }

                if ($_SESSION["station_DOS_PREMDATECP"] > 0) {
                    $TotauxSF["STOTAL" . $MesFamille[$codeCompte]["SsFamille"] . "||col||6"] += ($rea - $anm1);
                    $TotauxF["TOTAL" . $MesFamille[$codeCompte]["Famille"] . "||col||6"] += ($rea - $anm1);
                }

                if (!$TotRea && !$TotPrev && !$TotN1 && !$allCompte) {
                    $UneLigneDb = array();
                } else {
                    $HaveValues[] = "STOTAL" . $MesFamille[$codeCompte]["SsFamille"];
                }

            } elseif (strpos( $codeCompte, 'CMARGE') !== false) {
                //MARGE////////////////
                $codePosteMarge = str_replace("CMARGE", "", $codeCompte);

                $CompteBalance["Poste" . $codePosteMarge] = dbAcces::getLiaisonBalPoste($codePosteMarge, "achat");
                $TotalMarge["Poste" . $codePosteMarge] = 0;

                if ($opt['mensuel']) {
                    $TotalMarge["Poste" . $codePosteMarge] += $MesMarges[$codePosteMarge]["Montant"];
                    $TotalMargeN1["Poste" . $codePosteMarge] += $MesMargesN1[$codePosteMarge]["Montant"];
                } else {
                    $stotal["MargeCumulRea"]["$codePosteMarge"] += $MesMargesCumul[$codePosteMarge]["Montant"];
                    $TotalMargeCumulN1["Poste" . $codePosteMarge] += $MesMargesCumulN1[$codePosteMarge]["Montant"];
                    $stotal["MargeCumulPrev"]["$codePosteMarge"] += $MonPrevCumul[$codePosteMarge]["PrevTauxMontant"];
                }

                //totaux
                if ($opt['mensuel']) {
                    $MonEcartMarge = dbAcces::getEcartMarge($_SESSION["station_DOS_NUM"], $MoisActuel, $codePosteMarge);
                    $MonEcartMargeN1 = dbAcces::getEcartMarge($_SESSION["station_DOS_NUMPREC"], $MoisActuel, $codePosteMarge, false, false, false, false, true);

                    $TotalMarge["Poste" . $codePosteMarge] += $MonEcartMarge;
                    $TotalMarge["Poste" . $codePosteMarge] = round($TotalMarge["Poste" . $codePosteMarge], 2);
                    $TotalMargeN1["Poste" . $codePosteMarge] += $MonEcartMargeN1;
                    $TotalMargeN1["Poste" . $codePosteMarge] = round($TotalMargeN1["Poste" . $codePosteMarge], 2);

                    //rea
                    if (!$opt["MAJBASE"]) {
                        $pourc = ($TotalMarge["Poste" . $codePosteMarge] / -$MesResultats[$codePosteMarge . "||#||" . "UMoisRealise"]["Montant"]) * 100;
                        $pourc = StringHelper::NombreFr($pourc);

                        if ($pourc) {
                            $pourc = $pourc . " %";
                        }

                        $UneLigneDb[] = array(($pourc) => array("align" => "right"));
                    } else {
                        $UneLigneDb[] = array(StringHelper::NombreFr($TotalMarge["Poste" . $codePosteMarge]) => array("align" => "right"));
                    }

                    //prevu
                    $ValPrev = $MonPrev[$codePosteMarge]["PrevTauxMontant"];

                    if (!$opt["MAJBASE"]) {
                        $pourc = ($ValPrev / $MesResultats[$codePosteMarge . "||#||" . "UMoisPrevu"]["Montant"]) * 100;
                        $pourc = StringHelper::NombreFr($pourc);

                        if ($pourc) {
                            $pourc = $pourc . " %";
                        }

                        $UneLigneDb[] = array($pourc => array("align" => "right"));
                    } else {
                        $UneLigneDb[] = array(StringHelper::NombreFr($ValPrev) => array("align" => "right"));
                    }

                    //N-1
                    if (!$opt["MAJBASE"]) {
                        $pourc = -($TotalMargeN1["Poste" . $codePosteMarge] / $MesResultats[$codePosteMarge . "||#||" . "UMoisAnneeMoinsUn"]["Montant"]) * 100;
                        $pourc = StringHelper::NombreFr($pourc);

                        if ($pourc) {
                            $pourc = $pourc . " %";
                        }

                        $UneLigneDb[] = array($pourc => array("align" => "right"));
                    } else {
                        $UneLigneDb[] = array(StringHelper::NombreFr($TotalMargeN1["Poste" . $codePosteMarge]) => array("align" => "right"));
                    }

                    $UneLigneDb[] = "";
                } else {
                    $MonEcartMarge = dbAcces::getEcartMarge($_SESSION["station_DOS_NUM"], $MoisActuel, $codePosteMarge, false, true, false, $opt["cluster"]);
                    $MonEcartMargeN1 = dbAcces::getEcartMarge($_SESSION["station_DOS_NUMPREC"], $MoisAnneePrec, $codePosteMarge, false, true, false, $opt["cluster"]);

                    $stotal["MargeCumulRea"][$codePosteMarge] += $MonEcartMarge;
                    $TotalMargeCumulN1["Poste" . $codePosteMarge] += $MonEcartMargeN1;

                    $UneLigneDb[] = array((StringHelper::NombreFr($stotal["MargeCumulRea"][$codePosteMarge])) => array("align" => "right"));


                    //prevu
                    $ValPrev = ($stotal["MargeCumulPrev"][$codePosteMarge]);

                    $ValPrev = StringHelper::NombreFr($ValPrev);
                    $UneLigneDb[] = array($ValPrev => array("align" => "right"));

                    //N-1
                    $UneLigneDb[] = array(StringHelper::NombreFr($TotalMargeCumulN1["Poste" . $codePosteMarge]) => array("align" => "right"));
                    $UneLigneDb[] = "";

                    $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||1"] += $TotRea = $stotal["MargeCumulRea"][$codePosteMarge];
                    $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||2"] += $TotPrev = $stotal["MargeCumulPrev"][$codePosteMarge];
                    $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||3"] += $TotN1 = $TotalMargeCumulN1["Poste" . $codePosteMarge];

                    $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||1"] += $stotal["MargeCumulRea"][$codePosteMarge];
                    $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||2"] += $stotal["MargeCumulPrev"][$codePosteMarge];
                    $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||3"] += $TotalMargeCumulN1["Poste" . $codePosteMarge];
                }

                //ecarts prev
                if ($opt['cumul']) {
                    if ($Contenu["UMoisPrevu"]) {
                        $StyleRea = array("align" => "right");
                        $UneLigneDb[] = array(StringHelper::Signe($stotal["MargeCumulRea"][$codePosteMarge] - $stotal["MargeCumulPrev"][$codePosteMarge]) => $StyleRea);
                    } else {
                        $UneLigneDb[] = array("" => $StyleRea);
                    }

                    if ($_SESSION["station_DOS_PREMDATECP"] > 0) {
                        $StyleRea = array("align" => "right");
                        $UneLigneDb[] = array(StringHelper::Signe($stotal["MargeCumulRea"][$codePosteMarge] - $TotalMargeCumulN1["Poste" . $codePosteMarge]) => $StyleRea);
                    } else {
                        $UneLigneDb[] = array("" => $StyleRea);
                    }
                }

                //ecart n-1
                if ($opt['mensuel'] && !$opt["MAJBASE"]) {
                    // réél - prévu
                    if ($Contenu["UMoisPrevu"]) {
                        $StyleRea = array("align" => "right");
                        $UneLigneDb[] = array(StringHelper::Signe($TotalMarge["Poste" . $codePosteMarge] - $ValPrev) => $StyleRea);
                    } else {
                        $UneLigneDb[] = "";
                    }
                    //réel - (N-1)
                    if ($_SESSION["station_DOS_PREMDATECP"] > 0) {
                        $StyleRea = array("align" => "right");
                        $UneLigneDb[] = array(StringHelper::Signe($TotalMarge["Poste" . $codePosteMarge] - $TotalMargeN1["Poste" . $codePosteMarge]) => $StyleRea);
                    } else {
                        $UneLigneDb[] = "";
                    }
                }

                if ($opt["mensuel"]) {
                    $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||1"] += $TotRea = $TotalMarge["Poste" . $codePosteMarge];
                    $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||2"] += $TotPrev = $ValPrev;
                    $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||3"] += $TotN1 = $TotalMargeN1["Poste" . $codePosteMarge];

                    $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||1"] += $TotalMarge["Poste" . $codePosteMarge];
                    $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||2"] += $ValPrev;
                    $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||3"] += $TotalMargeN1["Poste" . $codePosteMarge];
                }

                if ($opt['cumul']) {
                    if ($Contenu["UMoisPrevu"]) {
                        $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||5"] += ($stotal["MargeCumulRea"]["$codePosteMarge"] - $stotal["MargeCumulPrev"]["$codePosteMarge"]);
                        $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||5"] += ($stotal["MargeCumulRea"]["$codePosteMarge"] - $stotal["MargeCumulPrev"]["$codePosteMarge"]);
                    }

                    if ($_SESSION["station_DOS_PREMDATECP"] > 0) {
                        $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||6"] += $stotal["MargeCumulRea"]["$codePosteMarge"] - $TotalMargeCumulN1["Poste" . $codePosteMarge];
                        $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||6"] += $stotal["MargeCumulRea"]["$codePosteMarge"] - $TotalMargeCumulN1["Poste" . $codePosteMarge];
                    }
                }

                if ($opt['mensuel'] && !$opt["MAJBASE"]) {
                    if ($Contenu["UMoisPrevu"]) {
                        $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||5"] += ($TotalMarge["Poste" . $codePosteMarge] - $ValPrev);
                        $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||5"] += ($TotalMarge["Poste" . $codePosteMarge] - $ValPrev);
                    }

                    if ($_SESSION["station_DOS_PREMDATECP"] > 0) {
                        $TotauxF["MARGETOTAL" . $MesFamille[$codePosteMarge]["Famille"] . "||col||6"] += ($TotalMarge["Poste" . $codePosteMarge] - $TotalMargeN1["Poste" . $codePosteMarge]);
                        $TotauxSF["MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"] . "||col||6"] += ($TotalMarge["Poste" . $codePosteMarge] - $TotalMargeN1["Poste" . $codePosteMarge]);
                    }
                }


                if (!$TotRea && !$TotPrev && !$TotN1 && !$allCompte) {
                    $UneLigneDb = array();
                } else {
                    $HaveValues[] = "MARGESTOTAL" . $MesFamille[$codePosteMarge]["SsFamille"];
                    $HaveValues[] = "STOTALRES_" . $MesFamille[$codePosteMarge]["SsFamille"];
                }

            } elseif (strpos( $codeCompte, 'TITRE') !== false) {
                $UneLigneDb[] = "";
                $UneLigneDb[] = "";
                $UneLigneDb[] = "";
                $UneLigneDb[] = "";
                $UneLigneDb[] = "";
                $UneLigneDb[] = "";
                $UneLigneDb[] = "";
                $UneLigneDb[] = "";
                $UneLigneDb[] = "";
                $UneLigneDb[] = "";
                $UneLigneDb[] = "";
                $UneLigneDb[] = "";
                $UneLigneDb[] = array("" => array("style" => "border-right:none"));
                $UneLigneDb[] = array("" => array("style" => "border-right:none"));
                $UneLigneDb[] = array("" => array("align" => "right"));
                $UneLigneDb[] = "";
                $UneLigneDb[] = array("" => array("style" => "border-right:none"));
                $UneLigneDb[] = array("" => array("align" => "right"));
            }
        }

        //TOTAUX
        if (!$opt['mensuel'] && !$opt['cumul']) {
            $opt['mensuel'] = 1;
        }

        if ($opt['mensuel'] || $opt['cumul']) {
            $col = 6;
        } else {
            $col = 10;
        }

        if ($opt['mensuel']) {
            $array1 = array(1 => "", 2 => "", 3 => "", 4 => array("" => array("class" => "colvide")), 5 => "", 6 => "");
        } elseif ($opt['cumul']) {
            $array1 = array(1 => "", 2 => "", 3 => "", 4 => array("" => array("class" => "colvide")), 5 => "", 6 => "");
        } else {
            $array1 = array(1 => "", 2 => "", 3 => "", 4 => array("" => array("class" => "colvide")), 5 => "", 6 => "", 7 => "", 8 => array("" => array("class" => "colvide")), 9 => "", 10 => "");
        }

        foreach ($TotauxF as $codeLigneTableau => $Valeur) {
            //recherche de la ligne totale pour lui affecter les valeurs
            $tab = explode("||col||", $codeLigneTableau);

            $MaCleTab1 = $tab[0];
            $Position = $tab[1];

            // pas égal à cela tout seul, mais peut-être "TOTAL charge de ..."
            if ($MaCleTab1 != "TOTAL" && $MaCleTab1 != "STOTAL" && $MesLignesTableau[$MaCleTab1] || $MaCleTab1 == "TOTALONFRCharges") {
                if (stripos( $MaCleTab1, 'TOTALONFRCharges') !== false) {
                    $MaLigneTotal = &$MesLignesTableau["TOTALONFR"];

                    $oldValeur = array_keys($MaLigneTotal[$Position]);
                    $oldValeur = StringHelper::Texte2Nombre($oldValeur[0]);

                    $Valeur += $oldValeur;
                } else {
                    $MaLigneTotal = &$MesLignesTableau[$MaCleTab1];
                }


                $MaLigneTotal[$Position] = array(StringHelper::NombreFr($Valeur) => array("align" => "right"));
                $MtabTran = array_diff_key($array1, $MaLigneTotal);

                foreach ($MtabTran as $cle => $v) {
                    if (is_array($v) || is_array($array1[$cle])) {
                        $MaLigneTotal[$cle] = $v;
                    } else {
                        $MaLigneTotal[$cle] = array("" => "");
                    }
                }

                ksort($MaLigneTotal);

                //pour ajout du sous total precedent sur le suivant
                if ($MaCleTab1 != "TOTALCarburants" && ($MaCleTab1 != "TOTALONFR" || $Type == "Charges") && $MaCleTab1 != "TOTALVENTES MARCHANDISES" && $MaCleTab1 != "TOTALONFRCharges") {
                    $TotalCharges[$Position] += $Valeur;
                }
            }
        }

        if ($opt['mensuel']) {
            $array1 = array(1 => "", 2 => "", 3 => "", 4 => array("" => array("class" => "colvide")), 5 => "", 6 => "");
        } elseif ($opt['cumul']) {
            $array1 = array(1 => "", 2 => "", 3 => "", 4 => array("" => array("class" => "colvide")), 5 => "", 6 => "");
        } else {
            $array1 = array(1 => "", 2 => "", 3 => "", 4 => array("" => array("class" => "colvide")), 5 => "", 6 => "", 7 => "", 8 => array("" => array("class" => "colvide")), 9 => "", 10 => "");
        }


        foreach ($TotauxSF as $codeLigneTableau => $Valeur) {
            //recherche de la ligne Stotal pour lui affecter les valeurs
            $tab = explode("||col||", $codeLigneTableau);

            $MaCleTab1 = $tab[0];
            $Position = $tab[1];

            if (($MaCleTab1 != "TOTAL" && $MaCleTab1 != "STOTAL" && $MesLignesTableau[$MaCleTab1] && in_array($MaCleTab1, $HaveValues)) || stripos($MaCleTab1, 'MARGESTOTALONFR') !== false) {
                $MaLigneTotal = &$MesLignesTableau[$MaCleTab1];

                $MaLigneTotal[$Position] = array(StringHelper::NombreFr($Valeur) => array("align" => "right"));
                $MtabTran = array_diff_key($array1, $MaLigneTotal);

                foreach ($MtabTran as $cle => $v) {
                    if (is_array($v) || is_array($array1[$cle])) {
                        $MaLigneTotal[$cle] = $v;
                    } else {
                        $MaLigneTotal[$cle] = array("" => "");
                    }
                }

                ksort($MaLigneTotal);
            } else {
                if (stripos($MaCleTab1, 'MARGESTOTALONFR') !== false) {
                    $MesLignesTableau[$MaCleTab1] = NULL;
                }
            }
        }

        if ($opt['mensuel'] || $opt['cumul']) {
            $Nb = 4;
        } else {
            $Nb = 8;
        }

        $LnBigTotal = array();
        $LnBigTotal[0] = array("Total des $Type :" => array("libelle" => "1", "align" => "right"));

        for ($i = 1; $i <= $col; $i++) {
            $LnBigTotal[$i] = array(StringHelper::NombreFr($TotalCharges[$i]) => array("align" => "right"));
        }

        $MesLignesTableau[] = "";

        $MtabTran = array_diff_key($array1, $LnBigTotal);

        foreach ($MtabTran as $cle => $v) {
            if (is_array($v) || is_array($array1[$cle])) {
                $LnBigTotal[$cle] = $v;
            } else {
                $LnBigTotal[$cle] = array("" => "");
            }
        }

        ksort($LnBigTotal);

        $MesLignesTableau["BIGTOTAL"] = $LnBigTotal;

        if ($Type == "Produits") {
            $MesLignesTableau[] = "";
            $Resultat[0] = array("R&eacute;sultat :" => array("libelle" => "1", "align" => "right"));

            foreach ($_SESSION["TotalProduit"] as $cle => $Montant) {
                if ($cle <= $Nb) {
                    $Resultat[$cle] = array(StringHelper::NombreFr($TotalCharges[$cle] - $Montant) => array("align" => "right"));
                }
            }

            if ($opt['mensuel']) {
                $array1 = array(1 => "", 2 => "", 3 => "", 4 => array("" => array("class" => "colvide")), 5 => "", 6 => "");
            } elseif ($opt['cumul']) {
                $array1 = array(1 => "", 2 => "", 3 => "", 4 => array("" => array("class" => "colvide")), 5 => "", 6 => "");
            } else {
                $array1 = array(1 => "", 2 => "", 3 => "", 4 => array("" => array("class" => "colvide")), 5 => "", 6 => "", 7 => "", 8 => array("" => array("class" => "colvide")), 9 => "", 10 => "");
            }

            $MtabTran = array_diff_key($array1, $Resultat);

            foreach ($MtabTran as $cle => $v) {
                if (is_array($v) || is_array($array1[$cle])) {
                    $Resultat[$cle] = $v;
                } else {
                    $Resultat[$cle] = array("" => "");
                }
            }

            ksort($Resultat);

            $MesLignesTableau["TOTALRESULTAT"] = $Resultat;
            $_SESSION["TotalProduit"] = array();

        } elseif ($Type == "Charges") {
            $_SESSION["TotalProduit"] = array();
            $_SESSION["TotalProduit"] = $TotalCharges;
        }

        if ($HOLD_station_DOS_PREMDATECP) {
            $_SESSION["station_DOS_PREMDATECP"] = $HOLD_station_DOS_PREMDATECP;
        }

        return $MesLignesTableau;
    }

    static function setTabMarges($MesLignesTableau, $Pourc = false, $EcartMarge = true, $Totaux = true, $MyColSpan = 7, $codeCompteStart = "TITREVENTES MARCHANDISES", $Charges = false)
    {
        $Retour = array();
        $FirstTitre = true;
        $Deb = "";

        if ($Pourc) {
            $Deb = "CPOUR";
        }

        $isVteMarchandise = false;

        if ($Pourc) {
            $LibPlus = " ";
        }//marge en &euro;";

        foreach ($MesLignesTableau as $codeCompte => $MesTds) {
            if ($codeCompte == $codeCompteStart) {
                $isVteMarchandise = true;
            }

            if ($isVteMarchandise && ($Totaux && stripos($codeCompte, "TOTAL") !== false || stripos($codeCompte, "TOTAL")=== false) && stripos($codeCompte, "CH_ONFR") === false) {
                if (stristr($codeCompte, "TITRE") && $FirstTitre) {
                    $FirstTitre = false;
                    if (!$Pourc) {
                        $Retour["TITREPOUR" . $codeCompte][0] = array("MARGES BOUTIQUE et AUTRES PRESTATIONS DE SERVICE" => array("libelle" => "1", "style" => "font-weight: bolder;text-align:left;", "colspan" => $MyColSpan, "class" => "sticky"));
                    } else {
                        $Retour["TITREMARGE" . $codeCompte][0] = array("TX DE MARGE " . self::abrev(str_replace("TITRE", "", $codeCompteStart)) => array("style" => "font-weight: bolder;text-align:left;", "colspan" => $MyColSpan, "class" => "sticky"));
                    }

                } elseif (stristr($codeCompte, "STOTAL")) {
                    $LibelleSsFamille = str_ireplace('STOTAL', '', $codeCompte);
                    $LibelleSsFamille = self::abrev($LibelleSsFamille);

                    if ($Charges) {
                        $Retour["MARGESTOTALONFR" . $Deb . $LibelleSsFamille][] = array("<i>Total Marge :</i>" => array("libelle" => "1", 'align' => 'right'));
                        $Retour["CH_ONFR" . $LibelleSsFamille][] = array("<i>Charges :</i>" => array("libelle" => "1", 'align' => 'right', 'style' => 'font-weight: italic'));
                        $Retour["STOTALRES_ONFR" . $LibelleSsFamille][] = array("R&eacute;sultat $LibelleSsFamille :" => array("libelle" => "1", 'align' => 'right', 'style' => 'font-weight: bolder'));
                    } else {
                        $Retour["MARGE" . $Deb . $codeCompte][] = array("<i> Marge $LibelleSsFamille :</i>" => array("libelle" => "1", 'align' => 'right'));
                    }
                } elseif (stristr($codeCompte, "TOTAL")) {
                    $Retour["MARGE" . $Deb . $codeCompte][0] = array("TOTAL MARGE : " => array("libelle" => "1", 'align' => 'right', 'style' => 'font-weight: bolder'));
                } elseif (stristr($codeCompte, "VIDE"))
                    $Retour["MARGE" . $codeCompte] = "";
                else {
                    if ($Pourc) {
                        $Retour["CPOUR" . $codeCompte] = $MesTds;
                    } else {
                        $Retour["CMARGE" . $codeCompte] = $MesTds;
                    }
                }
            }
        }

        foreach ($Retour as $code => &$LnSMarge) {
            foreach ($LnSMarge as $keyLN => $LnMargeCourant) {
                foreach ($LnMargeCourant as $Libelle => $Attributes) {
                    $MesLignesTableau[$code][0][str_replace("lnPoste_", "lnPosteMarge_", $Libelle)] = $Attributes;
                }
            }
        }

        return $MesLignesTableau;
    }

    static function getEcartCumul($Mois, $Type, $codePoste = false, $TypeCompte)
    {
        $Retour = array();

        if ($MesResultats = dbAcces::getResultatsPoste($Mois, true, true, true, true, $codePoste, $Type, $TypeCompte)) {

            foreach ($MesResultats as $codeCompteAvecDesc => $UnResultat) {
                $exp = explode("||#||", $codeCompteAvecDesc);
                $DescLigne = $exp[1];
                $codeCompte = $exp[0];

                if ($Type == "Produits" && $DescLigne == "UMoisRealise") {
                    $UnResultat["Montant"] = -$UnResultat["Montant"];
                }

                if ($Type == "Produits" && $DescLigne == "UMoisAnneeMoinsUn") {
                    $UnResultat["Montant"] = -$UnResultat["Montant"];
                }

                if ($DescLigne == "UMoisRealise") {
                    $Retour[$codeCompte . "||#||" . "ReaPrev"] += $UnResultat["Montant"];
                    $Retour[$codeCompte . "||#||" . "ReaAnneeMoinUn"] += $UnResultat["Montant"];
                } elseif ($DescLigne == "UMoisPrevu") {
                    $Retour[$codeCompte . "||#||" . "ReaPrev"] -= $UnResultat["Montant"];
                } elseif ($DescLigne == "UMoisAnneeMoinsUn") {
                    $Retour[$codeCompte . "||#||" . "ReaAnneeMoinUn"] -= $UnResultat["Montant"];
                }
            }
        }

        return $Retour;
    }
}
