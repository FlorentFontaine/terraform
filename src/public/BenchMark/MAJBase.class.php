<?php

use Classes\DB\Database;
use Helpers\StringHelper;

require_once __DIR__ . '/../ExportBack/export.class.php';
require_once __DIR__ . '/../dbClasses/AccesDonnees.php';
require_once __DIR__ . '/../BenchMark/benchmark.class.php';

class MAJBase
{

    public static function DoMAJBase($Mois, $DOS_NUM, &$TabCharge, &$TabProd, $STA_NUM)
    {
        $opt["MAJBASE"] = 1;

        $MonAncienMois = $_SESSION["MoisHisto"];
        $_SESSION["MoisHisto"] = $Mois;

        if (dbAcces::is_SituationInterm($DOS_NUM, $_SESSION["MoisHisto"])) {
            $BALI_TYPE = "BI";
        } elseif ($_SESSION["MoisHisto"] != date("Y-m-00", strtotime($_SESSION["station_DOS_FINEX"]))) {
            $BALI_TYPE = "BS";
        } else {
            $BALI_TYPE = "BS";
        }

        require_once __DIR__ . '/../Anomalie/Anomalie.class.php';

        $NbAno = Anomalie::CompterAnomalies($Ano);
        $NbAno--;// Pour enlever l'anomalie de la MAJ Base

        // On ne va pas plus loin, il reste des anomalies
        if ($NbAno > 0) {
            return "0";
        }

        require_once __DIR__ . '/../MargeBack/marge.class.php';

        Marge::getTab($_SESSION["MoisHisto"], array("updEcartMarge" => true, "updStockTheo" => true));

        require_once __DIR__ . '/../compChargesBack/compCharges.class.php';

        $opt["mensuel"] = 1;
        $TabCharge = compChargesProd::getTab("Charges", $Mois, false, false, false, $opt);
        $TabProd = compChargesProd::getTab("Produits", $Mois, false, false, false, $opt);

        $MAJProd = self::InsertMaj($TabProd, $Mois, $DOS_NUM, 2, $STA_NUM, false, $Resultat, $BALI_TYPE);
        $MAJCharge = self::InsertMaj($TabCharge, $Mois, $DOS_NUM, 1, $STA_NUM, false, $Resultat, $BALI_TYPE);

        if ($MAJProd && $MAJCharge) {
            $_SESSION["agip_AG_NUM"] = 1;
            $_SESSION["agip_AG_NUM"] = 0;

            if (dbAcces::is_SituationInterm($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"])) {
                $SI = "BI";
            } elseif ($_SESSION["MoisHisto"] != date("Y-m-00", strtotime($_SESSION["station_DOS_FINEX"]))) {
                $SI = "BS";
            }

            dbAcces::setMAJBaseMois($DOS_NUM, $Mois, date("Y-m-d H:i:s"), $Resultat, array("montant" => StringHelper::Texte2Nombre($MesValProj[2])), array("montant" => StringHelper::Texte2Nombre($MesValProj[6])), $Cligno["NbCligno"], $SI, array("montant" => StringHelper::Texte2Nombre($MesValProjAgip[2])));

            $_SESSION["MoisHisto"] = $MonAncienMois;
            $_SESSION["agip_AG_NUM"] = false;

            if ($_SESSION["User"]->Infos["Type"] == "comptable") {
                facturation::AddDossier($DOS_NUM, $Mois, $_SESSION["User"]->Var["CC_NUM"]);
            }

            return true;
        } else {
            $_SESSION["MoisHisto"] = $MonAncienMois;

            return false;
        }
    }

    public static function InsertMaj(&$TabCharge, $Mois, $DOS_NUM, $Type, $STA_NUM, $debug = false, &$Resultat = null, $BALI_TYPE)
    {
        $Sql = "";
        $MesMontantsPrev = null;
        $NotDel = false;

        if ($Type == 3) {
            //maj des heures, on remet 1 et on ne fera pas la suppression avant l'insertion
            $Type = 1;
            $NotDel = true;
        }

        $BPC_NOTINCONSO = self::get_NotInConso($DOS_NUM, $Mois, $BALI_TYPE);

        foreach ($TabCharge as $cleLigne => $Ligne) {
            $UneLigneBench = array();

            foreach ($Ligne as $Cel) {
                if (is_array($Cel)) {
                    foreach ($Cel as $Val => $Param) {
                        $UneLigneBench[] = htmlspecialchars_decode(strip_tags(str_replace("&nbsp;", "", Export::getVal($Val))));
                    }
                } else {
                    $UneLigneBench[] = htmlspecialchars_decode(strip_tags(str_replace("&nbsp;", "", Export::getVal($Cel))));
                }
            }

            $insert = false;
            $Marge = 0;

            if (strpos($cleLigne, 'CMARGE') !== false) {
                $codePoste = str_replace("CMARGE", "", $cleLigne);
                $Marge = 1;
                $insert = true;
            } elseif (strpos($cleLigne, 'ECARTMARGE1') !== false) {
                $codePoste = -1;
                $insert = true;
            } elseif (strpos($cleLigne, 'ECARTMARGE2') !== false) {
                $codePoste = -2;
                $insert = true;
            } elseif (strpos($cleLigne, 'NbHSalP') !== false) {
                $codePoste = -3;
                $insert = true;
            } elseif (strpos($cleLigne, 'NbHGerP') !== false) {
                $codePoste = -4;
                $insert = true;
            } elseif ($cleLigne == "TOTALRESULTAT") {
                $Resultat["reel"] = StringHelper::Texte2Nombre($UneLigneBench[1]);
                $Resultat["prevu"] = StringHelper::Texte2Nombre($UneLigneBench[2]);
                $Resultat["prevuagip"] = StringHelper::Texte2Nombre($UneLigneBench[4]);
            } elseif (is_numeric($cleLigne) && ($UneLigneBench[1] || $UneLigneBench[2] || $UneLigneBench[3])) {
                $insert = true;
                $codePoste = $cleLigne;
            }

            if ($insert) {
                if ($Type == 1) {
                    //charges
                    $UneLigneBench[1] = -StringHelper::Texte2Nombre($UneLigneBench[1]);
                    $UneLigneBench[2] = -StringHelper::Texte2Nombre($UneLigneBench[2]);
                    $UneLigneBench[3] = -StringHelper::Texte2Nombre($UneLigneBench[3]);
                    $UneLigneBench[4] = -StringHelper::Texte2Nombre($UneLigneBench[4]);
                } else {
                    //produits
                    $UneLigneBench[1] = StringHelper::Texte2Nombre($UneLigneBench[1]);
                    $UneLigneBench[2] = StringHelper::Texte2Nombre($UneLigneBench[2]);
                    $UneLigneBench[3] = StringHelper::Texte2Nombre($UneLigneBench[3]);
                    $UneLigneBench[4] = StringHelper::Texte2Nombre($UneLigneBench[4]);
                    $MesMontantsPrev[2][$codePoste] = StringHelper::Texte2Nombre($UneLigneBench[2]);
                }

                if ($UneLigneBench[1] || $UneLigneBench[2] || $UneLigneBench[3] || $UneLigneBench[4]) {
                    if ($Sql) {
                        $Sql .= ", ";
                    }

                    $Sql .= "('$codePoste','$Mois','$DOS_NUM','" . $UneLigneBench[1] . "','" . $UneLigneBench[2] . "','" . $UneLigneBench[3] . "','$Type','$Marge','$STA_NUM','" . $UneLigneBench[2] . "','$BALI_TYPE',$BPC_NOTINCONSO)";
                }
            }
        }

        //pas de valeur () import de balance vide le premier mois ...
        if (!$Sql) {
            return true;
        }

        if (!$NotDel) {
            $Sqldel = "delete from BenchProCharge where BPC_MOIS = '$Mois' and DOS_NUM = '$DOS_NUM' and BPC_TYPE = '$Type' and BALI_TYPE='$BALI_TYPE'";
            Database::query($Sqldel);
        }

        $Sql = " insert into BenchProCharge (`BEN_NUM` ,`BPC_MOIS` ,`DOS_NUM` ,`BPC_REA` ,`BPC_PREV` ,`BPC_N1`,`BPC_TYPE`,`BPC_MARGE`,`STA_NUM`,`BPC_PREVAGIP`,BALI_TYPE,BPC_NOTINCONSO ) VALUES " . $Sql . $AjoutHeure;

        // ('-3','$Mois','$DOS_NUM','','','','$Type','0','$STA_NUM'),('-4','$Mois','$DOS_NUM','','','','$Type','0','$STA_NUM')";
        // => cest mis à jour avec le deuxième lancement avec le tableau TabHeures (contient les heures travaillées payées)

        self::set_NotInConso($DOS_NUM, $Mois, $BALI_TYPE);

        return Database::query($Sql);
    }

    public static function get_NotInConso($DOS_NUM, $Mois, $BALI_TYPE)
    {
        $wherePlus = "";

        if ($BALI_TYPE == "BS") {
            $wherePlus .= " and (BALI_TYPE = 'BP' or BALI_TYPE = 'BD' or BALI_TYPE = 'BI')";
        } elseif ($BALI_TYPE == "BP") {
            $wherePlus .= " and (BALI_TYPE = 'BD' or BALI_TYPE = 'BI')";
        } else {
            return 0;
        }

        $sql = "select distinct BALI_TYPE from BenchProCharge where 1 and DOS_NUM = $DOS_NUM and BPC_MOIS = '$Mois' $wherePlus";

        Database::query($sql);

        if (Database::fetchArray()) {
            return 1;
        }

        return 0;
    }

    public static function set_NotInConso($DOS_NUM, $Mois, $BALI_TYPE)
    {
        if ($BALI_TYPE == "BD") {
            $wherePlus = " and (BALI_TYPE = 'BS' or BALI_TYPE = 'BP')";
        } elseif ($BALI_TYPE == "BP") {
            $wherePlus = " and (BALI_TYPE = 'BS')";
        } elseif ($BALI_TYPE == "BI") {
            $wherePlus = " and (BALI_TYPE = 'BS')";
        } else {
            return false;
        }

        $sql = "update BenchProCharge set BPC_NOTINCONSO = 1 where DOS_NUM = $DOS_NUM and BPC_MOIS = '$Mois' $wherePlus";
        return Database::query($sql);
    }
}
