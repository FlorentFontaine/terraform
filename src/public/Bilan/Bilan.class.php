<?php

use Facades\Modules\Commentaire\Commentaire;
use Helpers\StringHelper;
use Repositories\BilanRepository;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../ctrl/ctrl.php';
require_once __DIR__ . '/../htmlClasses/table.php';
require_once __DIR__ . '/../dbClasses/AccesDonnees.php';

class Bilan
{
    static function getTab($DOS_NUM, $MoisActuel, $type = '', $checkForAno = false)
    {
        $tabCriteres = array(
            "tabCriteres" => array(
                "CPTB_TYPE" => $type,
            ),
            "triRequete" => " order by CPTB_ORDRE ASC ",
            "index" => "CPTB_NUM",
        );

        $MesPostes = dbAcces::getComptesBilan($tabCriteres);

        $MesLignesTableau = self::getStructureTableauBilan($MesPostes, $type);

        $MonActifBrut = dbAcces::getResultatsBilan($DOS_NUM, $MoisActuel, "brut");
        $MonActifProv = dbAcces::getResultatsBilan($DOS_NUM, $MoisActuel, "amort");
        $MonPassif = dbAcces::getResultatsBilan($DOS_NUM, $MoisActuel, "net");

        if (!isset($Cluster) || !$Cluster) {
            $Cluster = false;
        }

        $STOTAL = array(
            "MonActifBrut" => 0,
            "MonActifProv" => 0,
            "ActifNet" => 0,
            "MonPassif" => 0
        );

        $TOTAL = array(
            "MonActifBrut" => 0,
            "MonActifProv" => 0,
            "ActifNet" => 0,
            "MonPassif" => 0
        );

        $BIGTOT = array(
            "MonActifBrut" => 0,
            "MonActifProv" => 0,
            "ActifNet" => 0,
            "MonPassif" => 0,
        );

        $BIGTOTAL["MonPassif"] = 0;

        foreach ($MesLignesTableau as $CodeLigne => &$UneLigneDb) {
            if ($type == "actif" || $checkForAno) {
                //Type actif
                if (is_int($CodeLigne) && (($CodeLigne >= 1 && $CodeLigne <= 31) || $CodeLigne >= 59)) {
                    if (!isset($MonActifProv[$CodeLigne]["Montant"]) || !$MonActifProv[$CodeLigne]["Montant"]) {
                        $MonActifProv[$CodeLigne]["Montant"] = 0;
                    }

                    if (!isset($MonActifBrut[$CodeLigne]["Montant"]) || !$MonActifBrut[$CodeLigne]["Montant"]) {
                        $MonActifBrut[$CodeLigne]["Montant"] = 0;
                    }

                    //ligne de stock
                    if (($CodeLigne >= 6 && $CodeLigne <= 10) || $CodeLigne == 61) {
                        if (!isset($MesStocks) || !$MesStocks) {
                            $MesStocks = dbAcces::getStockRetenuBilanDetail($DOS_NUM, $MoisActuel, false, $Cluster);
                        }

                        $Brut[$CodeLigne] = $MesStocks[$CodeLigne];
                        $Brut[$CodeLigne] = round($Brut[$CodeLigne]);
                        $UneLigneDb[] = array(StringHelper::NombreFr($Brut[$CodeLigne], 0) => array("align" => "right"));
                        $MonActifProv[$CodeLigne]["Montant"] = -$MonActifProv[$CodeLigne]["Montant"];
                    } elseif ($CodeLigne == 16) {
                        //Fournisseurs et autres créances
                        $CompteBilan16 = dbAcces::getResultatsBilan($DOS_NUM, $MoisActuel, "brut", "16");
                        $Brut[$CodeLigne] = round(self::getMontantPosteBilanActif($CompteBilan16));
                        $UneLigneDb[] = array(StringHelper::NombreFr($Brut[$CodeLigne], 0) => array("align" => "right"));
                        $MonActifProv[$CodeLigne]["Montant"] = -$MonActifProv[$CodeLigne]["Montant"];
                    } elseif ($CodeLigne == 18) {
                        // Ligne Etat-TVA
                        $CompteBilan18 = dbAcces::getResultatsBilan($DOS_NUM, $MoisActuel, "brut", "18");
                        $Brut[$CodeLigne] = round(self::getMontantPosteBilanActif($CompteBilan18));
                        $UneLigneDb[] = array(StringHelper::NombreFr($Brut[$CodeLigne], 0) => array("align" => "right"));
                    } elseif ($CodeLigne == 25) {
                        // Ligne Banque
                        $CompteBilan25 = dbAcces::getResultatsBilan($DOS_NUM, $MoisActuel, "brut", "25");
                        $Brut[$CodeLigne] = round(self::getMontantPosteBilanActif($CompteBilan25));
                        $UneLigneDb[] = array(StringHelper::NombreFr($Brut[$CodeLigne], 0) => array("align" => "right"));
                    } else {
                        $MonActifProv[$CodeLigne]["Montant"] = -$MonActifProv[$CodeLigne]["Montant"];
                        $Brut[$CodeLigne] = $MonActifBrut[$CodeLigne]["Montant"];

                        if (isset($Brut["17"]) && $Brut["17"] < 0) {
                            $Brut[$CodeLigne] = 0;
                        }

                        $Brut[$CodeLigne] = round($Brut[$CodeLigne]);
                        $UneLigneDb[] = array(StringHelper::NombreFr($Brut[$CodeLigne], 0) => array("align" => "right"));
                    }


                    $UneLigneDb[] = array(StringHelper::NombreFr($MonActifProv[$CodeLigne]["Montant"], 0) => array("align" => "right"));
                    $ActifNet[$CodeLigne] = $Brut[$CodeLigne] - $MonActifProv[$CodeLigne]["Montant"];
                    $ActifNet[$CodeLigne] = round($ActifNet[$CodeLigne]);
                    $UneLigneDb[] = array(StringHelper::NombreFr($ActifNet[$CodeLigne], 0) => array("align" => "right"));

                    $STOTAL["MonActifBrut"] += $Brut[$CodeLigne];
                    $STOTAL["MonActifProv"] += $MonActifProv[$CodeLigne]["Montant"];
                    $STOTAL["ActifNet"] += $ActifNet[$CodeLigne];


                    $TOTAL["MonActifBrut"] += $Brut[$CodeLigne];
                    $TOTAL["MonActifProv"] += $MonActifProv[$CodeLigne]["Montant"];
                    $TOTAL["ActifNet"] += $ActifNet[$CodeLigne];

                    $BIGTOT["MonActifBrut"] += $Brut[$CodeLigne];
                    $BIGTOT["MonActifProv"] += $MonActifProv[$CodeLigne]["Montant"];
                    $BIGTOT["ActifNet"] += $ActifNet[$CodeLigne];
                }

                if (strpos($CodeLigne, "TOTAUX") !== false) {
                    $UneLigneDb[] = array(StringHelper::NombreFr($TOTAL["MonActifBrut"], 0) => array("align" => "right"));
                    $TOTAL["MonActifBrut"] = 0;
                    $UneLigneDb[] = array(StringHelper::NombreFr($TOTAL["MonActifProv"], 0) => array("align" => "right"));
                    $TOTAL["MonActifProv"] = 0;
                    $UneLigneDb[] = array(StringHelper::NombreFr($TOTAL["ActifNet"], 0) => array("align" => "right"));
                    $TOTAL["ActifNet"] = 0;
                }

                if (strpos($CodeLigne, "STOTAL") !== false) {
                    $UneLigneDb[] = array(StringHelper::NombreFr($STOTAL["MonActifBrut"], 0) => array("align" => "right"));
                    $STOTAL["MonActifBrut"] = 0;
                    $UneLigneDb[] = array(StringHelper::NombreFr($STOTAL["MonActifProv"], 0) => array("align" => "right"));
                    $STOTAL["MonActifProv"] = 0;
                    $UneLigneDb[] = array(StringHelper::NombreFr($STOTAL["ActifNet"], 0) => array("align" => "right"));
                    $STOTAL["ActifNet"] = 0;
                }

                if (strpos($CodeLigne, "BIG") !== false) {
                    $UneLigneDb[] = array(StringHelper::NombreFr($BIGTOT["MonActifBrut"], 0) => array("align" => "right"));
                    $UneLigneDb[] = array(StringHelper::NombreFr($BIGTOT["MonActifProv"], 0) => array("align" => "right"));
                    $UneLigneDb[] = array(StringHelper::NombreFr($BIGTOT["ActifNet"], 0) => array("align" => "right"));
                }
            }

            if ($type == "passif" || $checkForAno) {
                // Type passif
                if (is_int($CodeLigne) && $CodeLigne >= 32 && $CodeLigne <= 58) {
                    if (!isset($MonPassif[$CodeLigne]["Montant"]) || !$MonPassif[$CodeLigne]["Montant"]) {
                        $MonPassif[$CodeLigne]["Montant"] = 0;
                    }

                    // Ligne Résultat de l'exercice
                    if ($CodeLigne == "35") {
                        $ResultatComptable = dbAcces::getResultat($DOS_NUM, $MoisActuel);
                        $val[$CodeLigne] = $ResultatComptable['BALI_RES'];
                    } elseif ($CodeLigne == "46") {
                        // Ligne découvert bancaire
                        $CompteBilan46 = dbAcces::getResultatsBilan($DOS_NUM, $MoisActuel, "net", "46");
                        $val[$CodeLigne] = self::getMontantPosteBilanPassif($CompteBilan46);
                    } elseif ($CodeLigne == "51") {
                        // Dettes fournisseurs autres
                        $CompteBilan51 = dbAcces::getResultatsBilan($DOS_NUM, $MoisActuel, "net", "51");
                        $val[$CodeLigne] = self::getMontantPosteBilanPassif($CompteBilan51);
                    } elseif ($CodeLigne == "53") {
                        // Ligne personnel
                        $CompteBilan53 = dbAcces::getResultatsBilan($DOS_NUM, $MoisActuel, "net", "53");
                        $val[$CodeLigne] = self::getMontantPosteBilanPassif($CompteBilan53);
                    } elseif ($CodeLigne == "54") {
                        // Ligne organismes sociaux
                        $CompteBilan54 = dbAcces::getResultatsBilan($DOS_NUM, $MoisActuel, "net", "54");
                        $val[$CodeLigne] = self::getMontantPosteBilanPassif($CompteBilan54);
                    } elseif ($CodeLigne == "56") {
                        // Ligne Etat-TVA
                        $CompteBilan56 = dbAcces::getResultatsBilan($DOS_NUM, $MoisActuel, "net", "56");
                        $val[$CodeLigne] = self::getMontantPosteBilanPassif($CompteBilan56);
                    } elseif ($CodeLigne == "57") {
                        // Ligne Autres dettes et charges à payer
                        $CompteBilan57 = dbAcces::getResultatsBilan($DOS_NUM, $MoisActuel, "net", "57");
                        $val[$CodeLigne] = self::getMontantPosteBilanPassif($CompteBilan57);
                    } else {
                        $val[$CodeLigne] = -$MonPassif[$CodeLigne]["Montant"];
                    }

                    //Etat impôts sur bénéfices
                    if (isset($val["55"]) && $val["55"] < 0) {
                        $val["55"] = 0;
                    }

                    $val[$CodeLigne] = round($val[$CodeLigne], 2);

                    $UneLigneDb[] = array(StringHelper::NombreFr($val[$CodeLigne], 0) => array("align" => "right"));
                    $STOTAL["MonPassif"] += $val[$CodeLigne];
                    $TOTAL["MonPassif"] += $val[$CodeLigne];
                    $BIGTOTAL["MonPassif"] += $val[$CodeLigne];
                }


                if (strpos($CodeLigne, "TOTAUX") !== false) {
                    $UneLigneDb[] = array(StringHelper::NombreFr($TOTAL["MonPassif"], 0) => array("align" => "right"));
                    $TOTAL["MonPassif"] = 0;
                    $STOTAL["MonPassif"] = 0;
                }

                if (strpos($CodeLigne, "STOTAL") !== false) {
                    $UneLigneDb[] = array(StringHelper::NombreFr($STOTAL["MonPassif"], 0) => array("align" => "right"));
                    $STOTAL["MonPassif"] = 0;
                }

                if (strpos($CodeLigne, "BIG") !== false) {
                    if (abs($BIGTOTAL["MonPassif"] - $BIGTOT["ActifNet"]) <= 10) {
                        $BIGTOT["MonPassif"] = $BIGTOT["ActifNet"];
                    } else {
                        $BIGTOT["MonPassif"] = $BIGTOTAL["MonPassif"];
                    }

                    $UneLigneDb[] = array(StringHelper::NombreFr($BIGTOT["MonPassif"], 0) => array("align" => "right"));
                }
            }
        }

        if ($checkForAno) {
            return $BIGTOT;
        }

        return $MesLignesTableau;
    }

    private static function getStructureTableauBilan($MesPostes, $type = "")
    {
        $PremF = $PremSF = true;
        $FaireSToataux = false;
        $SsFamilleDef = $FamilleDef = '';

        //initialisation du tableau avec ligne total + stotal de chaque poste
        foreach ($MesPostes as $codePoste => $UneLignePoste) {
            $UneLigneTableau = array();

            //pour savoir combien il y a de sous familles
            if ($UneLignePoste["CPTB_SFAMILLE"] != $SsFamilleDef && $UneLignePoste["CPTB_SFAMILLE"] != $FamilleDef) {
                $FaireSToataux = true;
            }

            //changement de Sousfamille
            if ($UneLignePoste["CPTB_SFAMILLE"] != $SsFamilleDef) {
                if (!$PremSF && $SsFamilleDef != $FamilleDef && $FaireSToataux) {
                    //LnsTotal
                    $Nom = explode("||#||", $SsFamilleDef);

                    if (count($Nom) > 1) {
                        $Nom = "Sous total :";
                    } else {
                        $Nom = "Total " . $SsFamilleDef . " :";
                    }

                    if ($type == "actif") {
                        $UneLigneTableau[] = array($Nom => array("libelle" => "1", 'align' => 'right', 'style' => 'font-weight: bolder'));
                    } else {
                        $UneLigneTableau[] = array($Nom => array('align' => 'right', 'style' => 'font-weight: bolder'));
                    }

                    $MesLignesTableau["STOTAL" . $SsFamilleDef] = $UneLigneTableau;
                    $UneLigneTableau = array();

                    //Ln vide
                    if ($type == "actif") {
                        $UneLigneTableau[] = array("&nbsp;" => array("libelle" => "1"));
                    } else {
                        $UneLigneTableau[] = array("&nbsp;" => "");
                    }

                    $MesLignesTableau["VIDE" . $SsFamilleDef] = $UneLigneTableau;
                    $UneLigneTableau = array();
                }

                $SsFamilleDef = $UneLignePoste["CPTB_SFAMILLE"];
            }

            //changement de famille
            if ($UneLignePoste["CPTB_FAMILLE"] != $FamilleDef) {
                if (!$PremF) {
                    //LnTotal
                    if ($type == "actif") {
                        $UneLigneTableau[] = array("TOTAL " . $FamilleDef . " :" => array("libelle" => "1", 'align' => 'right', 'style' => 'font-weight: bolder'));
                    } else {
                        $UneLigneTableau[] = array("TOTAL " . $FamilleDef . " :" => array('align' => 'right', 'style' => 'font-weight: bolder'));
                    }

                    $MesLignesTableau["TOTAUX" . $FamilleDef] = $UneLigneTableau;
                    $UneLigneTableau = array();

                    //Ln vide
                    if ($type == "actif") {
                        $UneLigneTableau[] = array("&nbsp;" => array("libelle" => "1"));
                    } else {
                        $UneLigneTableau[] = array("&nbsp;" => "");
                    }

                    $MesLignesTableau["VIDE" . $FamilleDef] = $UneLigneTableau;
                    $UneLigneTableau = array();

                    if ($FamilleDef == "VENTES MARCHANDISES" && $UneLignePoste["CPTB_FAMILLE"] == "ACTIVITE LAVAGE") {
                        $MesLignesTableau = compChargesProd::setTabMarges($MesLignesTableau);
                    }
                }

                //ln nom famille
                $UneLigneTableau[] = array($UneLignePoste["CPTB_FAMILLE"] => array("style" => "font-weight: bolder"));
                $MesLignesTableau["TITRE" . $UneLignePoste["CPTB_FAMILLE"]] = $UneLigneTableau;
                $UneLigneTableau = array();

                $FamilleDef = $UneLignePoste["CPTB_FAMILLE"];
                $PremSF = true;
                $FaireSToataux = false;
            }

            $data = [
                "key" => $UneLignePoste["CPTB_NUM"],
                "value" => $UneLignePoste["CPTB_LIB"]
            ];

            if ($type == "actif") {
                if ($UneLignePoste["CPTB_NUM"]) {
                    $UneLigneTableau[] = array(
                        "<span data-type='bilan' data-id='" . $UneLignePoste["CPTB_NUM"] . "' class='detail_tooltip' >&#8505;</span>" .  Commentaire::actionOnBilan($data)
                        => array("libelle" => "1", "class" => "LibelleTab")
                    );
                } else {
                    $UneLigneTableau[] = array($UneLignePoste["CPTB_LIB"] => array("libelle" => "1", "align" => "left"));
                }
            } else {
                if ($UneLignePoste["CPTB_NUM"] && $UneLignePoste["CPTB_NUM"] != "35") {
                    $UneLigneTableau[] = array(
                        "<span data-type='bilan' data-id='" . $UneLignePoste["CPTB_NUM"] . "' class='detail_tooltip' >&#8505;</span>" . Commentaire::actionOnBilan($data)
                        => array("libelle" => "1", "class" => "LibelleTab")
                    );
                } else {
                    $UneLigneTableau[] = array(
                        "<span style='padding-right:24px;'></span>" . Commentaire::actionOnBilan($data)
                        => array("align" => "left"));
                }
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
        if ($FaireSToataux) {
            $Nom = explode("||#||", $SsFamilleDef);

            if (count($Nom) > 1) {
                $Nom = "Sous total :";
            } else {
                $Nom = "Total " . $SsFamilleDef . " :";
            }

            $UneLigneTableau = array();
            $UneLigneTableau[] = array($Nom => array('align' => 'right', 'style' => 'font-weight: bolder'));

            //Ln vide
            $UneLigneTableau = array();
            $UneLigneTableau[] = array("&nbsp;" => "");
            $MesLignesTableau["VIDE" . $SsFamilleDef] = $UneLigneTableau;

            $UneLigneTableau = array();
            $MesLignesTableau["STOTAL" . $SsFamilleDef] = $UneLigneTableau;
        }

        $UneLigneTableau = array();
        //LnTotal
        if ($type == "actif") {
            $UneLigneTableau[] = array("TOTAL " . $FamilleDef . " :" => array("libelle" => "1", 'style' => 'font-weight: bolder'));
        } else {
            $UneLigneTableau[] = array("TOTAL " . $FamilleDef . " :" => array('style' => 'font-weight: bolder'));
        }

        $MesLignesTableau["TOTAUX" . $FamilleDef] = $UneLigneTableau;

        $UneLigneTableau = array();

        if ($type == "actif") {
            $UneLigneTableau[] = array("TOTAL " . strtoupper($type) . " :" => array("libelle" => "1", 'style' => 'font-weight: bolder'));
        } else {
            //Ln vide
            if (isset($_SESSION["station_LIE_TYPO5"]) && $_SESSION["station_LIE_TYPO5"] == "oui") {
                $UneLigneTableau = array();
                $UneLigneTableau[] = array("&nbsp;" => "");
                $MesLignesTableau["VIDE" . $SsFamilleDef] = $UneLigneTableau;
                $UneLigneTableau = array();
                $UneLigneTableau[] = array("&nbsp;" => "");
                $MesLignesTableau["VIDE2" . $SsFamilleDef] = $UneLigneTableau;
                $UneLigneTableau = array();
            }

            $UneLigneTableau[] = array("TOTAL " . strtoupper($type) . " :" => array('style' => 'font-weight: bolder'));
        }

        $MesLignesTableau["BIG" . $FamilleDef] = $UneLigneTableau;

        return $MesLignesTableau;
    }

    private static function getMontantPosteBilanPassif($comptes)
    {
        $cumul = 0;

        foreach ($comptes as $MonCodeCompte => $Montant) {
            if (isset($Montant["Sens"]) && $Montant["Sens"] == "-" && $Montant["BAL_CUMUL"] < 0) {
                $comptes[$MonCodeCompte]["BAL_CUMUL"] = -round($Montant["BAL_CUMUL"]);
            } elseif (!isset($Montant["Sens"]) || !$Montant["Sens"]) {
                $comptes[$MonCodeCompte]["BAL_CUMUL"] = -round($Montant["BAL_CUMUL"]);
            } else {
                $comptes[$MonCodeCompte]["BAL_CUMUL"] = 0;
            }

            $cumul += $comptes[$MonCodeCompte]["BAL_CUMUL"];
        }

        return $cumul;
    }

    private static function getMontantPosteBilanActif($comptes)
    {
        $cumul = 0;

        foreach ($comptes as $MonCodeCompte => $Montant) {
            if (isset($Montant["Sens"]) && $Montant["Sens"] == "+" && $Montant["BAL_CUMUL"] < 0) {
                $comptes[$MonCodeCompte]["BAL_CUMUL"] = 0;
            }

            $comptes[$MonCodeCompte]["BAL_CUMUL"] = round($comptes[$MonCodeCompte]["BAL_CUMUL"]);

            $cumul += $comptes[$MonCodeCompte]["BAL_CUMUL"];
        }

        return $cumul;
    }
}
