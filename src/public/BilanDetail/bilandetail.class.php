<?php
use Helpers\StringHelper;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Bilan/Bilan.class.php';
class BilanDetail
{
    static function getTab($MoisActuel, $CPTB_NUM = false)
    {
        global $Imprimer;

        $d = array(
            "index" => "CPTB_NUM"
        );
        if ($CPTB_NUM) {
            $d["tabCriteres"]["comptebilan.CPTB_NUM"] = $CPTB_NUM;
        }

        $MesPostes = dbAcces::getComptesBilan($d);

        $TotauxF = $TotauxSF = array();
        $PremF = $PremSF = true;

        //initialisation du tableau avec ligne total + stotal de chaque poste
        foreach ($MesPostes as $codePoste => $UneLignePoste) {
            $UneLigneTableau = array();

            $d = array(
                "join" => " join AS_comptes_comptepostebilan ON AS_comptes_comptepostebilan.CPTB_NUM = comptebilan.CPTB_NUM 
                            join comptes ON comptes.code_compte = AS_comptes_comptepostebilan.codeCompte ",
                "triRequete" => " ORDER BY comptes.numero ASC",
                "distinct * "
            );

            if ($codePoste) {
                $d["tabCriteres"]["comptebilan.CPTB_NUM"] = $codePoste;
            }

            $MesComptes = dbAcces::getComptesBilan($d);
            $MesResultatsCompte = dbAcces::getResultatsCompte($MoisActuel, false, false, false);

            $MaSsFamille = explode("||#||", $UneLignePoste["CPTB_SFAMILLE"]);
            $MaSsFamille = $MaSsFamille[0];

            if ($UneLignePoste["CPTB_SFAMILLE"] != $SsFamilleDef) {
                //ln nom sous-famille
                if ($UneLignePoste["CPTB_FAMILLE"] == $FamilleDef) {
                    $UneLigneTableau[] = array($MaSsFamille => array("style" => "padding: 5px;text-align:left; ", "class" => "tdfixe", "width" => "250"));

                    if ($MoisActuel) {
                        $UneLigneTableau[] = array(StringHelper::Mysql2DateFr($MoisActuel) => array("class" => "tdfixe", "width" => "40", "style" => "border:none"));
                    }

                    $MesLignesTableau["ENCADRE" . $UneLignePoste["CPTB_SFAMILLE"]] = $UneLigneTableau;
                    $UneLigneTableau = array();
                }

                $SsFamilleDef = array();
                $SsFamilleDef = $UneLignePoste["CPTB_SFAMILLE"];
            }

            //changement de famille
            if ($UneLignePoste["CPTB_FAMILLE"] != $FamilleDef) {
                //ln nom famille
                if ($FamilleDef) {//séparation
                    $UneLigneTableau = array();
                    $UneLigneTableau[] = "<div style='height:100px;'></div>";
                    $MesLignesTableau["SEPAR" . $UneLignePoste["CPTB_FAMILLE"]] = $UneLigneTableau;
                }

                $UneLigneTableau = array();
                $UneLignePoste["CPTB_FAMILLE"] = str_replace("ONFR", "ACTIVITES ANNEXES", $UneLignePoste["CPTB_FAMILLE"]);

                $UneLigneTableau = array();
                $UneLigneTableau[] = array($MaSsFamille . "<div class='div200' style='width:350px'></div>" => array("style" => "padding: 5px;text-align:left; ", "class" => "tdfixe", "width" => "250"));
                $UneLigneTableau[] = array(StringHelper::Mysql2DateFr($MoisActuel) . "<div class='div70'></div>" => array("class" => "tdfixe", "width" => "40"));

                $MesLignesTableau["ENCADRE" . $UneLignePoste["CPTB_SFAMILLE"]] = $UneLigneTableau;
                $UneLigneTableau = array();

                $FamilleDef = $UneLignePoste["CPTB_FAMILLE"];
                $PremSF = true;
            }

            $UneLigneTableauTitre = array();
            $UneLigneTableauTitre[] = array(" - " . strtoupper(StringHelper::TextFilter($UneLignePoste["CPTB_LIB"])) => array("align" => "left", "style" => "font-weight:bolder;"));
            $UneLigneTableau = array();

            $AuMoinUnCompte = false;

            foreach ($MesComptes as $codeCompte => $UneLigneCompte) {
                if ((!$Imprimer) || ($MesResultatsCompte[$codeCompte . "UMois"]["BAL_BALANCE"] || $MesResultatsCompte[$codeCompte . "UMois"]["BAL_CUMUL"] || $MesResultatsCompteNMoins1[$codeCompte . "UMois"]["BAL_BALANCE"] || $MesResultatsCompteNMoins1Cumul[$codeCompte . "UMois"]["BAL_CUMUL"])) {
                    if (!$AuMoinUnCompte) {
                        $MesLignesTableau["Poste" . $codePoste] = $UneLigneTableauTitre;
                    }

                    $AuMoinUnCompte = true;
                    $UneLigneTableau = array();

                    $UneLigneTableau[] = array($UneLigneCompte["numero"] . " &nbsp; " . $UneLigneCompte["libelle"] => array("align" => "left"));

                    $ComplementNom = "";

                    if ($UneLignePoste["Famille"] == "ONFR") {
                        $ComplementNom = "||#||ONFR";
                    }

                    $MesLignesTableau[$codeCompte . $ComplementNom] = $UneLigneTableau;
                }
            }

            if ($AuMoinUnCompte && $MoisActuel) {
                $UneLigneTableau = array();
                $UneLigneTableau[] = array("" => array("align" => "right", "style" => "font-weight: bolder"));
                $MesLignesTableau["Total" . $codePoste] = $UneLigneTableau;
                $UneLigneTableau = array();
            }

            if ($PremSF) {
                $PremSF = false;
            }

            if ($PremF) {
                $PremF = false;
            }
        }


        //***************************************/

        $UneLigneTableau = array();

        //mise en place des résultats comptes Période N

        if ($MoisActuel) {
            foreach ($MesLignesTableau as $codeCompte => &$UneLigneDb) {

                $codeCompte = str_replace("||#||ONFR", "", $codeCompte);

                if (strpos($codeCompte, 'ENCADRE') !== false) {
                    $Famille = str_replace("ENCADRE", "", $codeCompte);
                } elseif (strpos($codeCompte, 'Poste') !== false) {
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
        if ($MoisActuel) {
            foreach ($MesLignesTableau as $codePoste => &$UneLigneDb) {
                $codeCompte = str_replace("||#||ONFR", "", $codeCompte);

                if (strpos($codePoste, 'Total') !== false) {
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
            $TotalCharges = array();

            //TOTAUX
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
                        $MaLigneTotal[$cle] = $v;
                    }

                    ksort($MaLigneTotal);
                }
            }

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
