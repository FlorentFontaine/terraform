<?php
use Helpers\StringHelper;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../ctrl/ctrl.php';
require_once __DIR__ . '/../dbClasses/AccesDonnees.php';
require_once __DIR__ . '/../dbClasses/station.php';
require_once __DIR__ . '/../htmlClasses/table.php';
require_once __DIR__ . '/../include/Filtres.php';


class Suivi
{
    static $MesSuivis;
    static $MesSuivisN1;

    /**
     * Retourne la couleur correspondant à un import
     */
    static function getColorImport($dosNum, $dosNumN1, $date)
    {
        $Color = "";

        if (!isset(self::$MesSuivis[$dosNum][$date]) || !self::$MesSuivis[$dosNum][$date]) {
            self::$MesSuivis[$dosNum][$date] = '';
        }
        if (!isset(self::$MesSuivisN1[$dosNumN1][$date]) || !self::$MesSuivisN1[$dosNumN1][$date]) {
            self::$MesSuivisN1[$dosNumN1][$date] = '';
        }

        // Si on n'a pas du tout d'import en N ou N1 pour cette date
        if (self::$MesSuivis[$dosNum][$date] === '' && self::$MesSuivisN1[$dosNumN1][$date] === '') {
            return $Color;
        }

        if (self::isBalanceThisMonth(self::$MesSuivis[$dosNum][$date], self::$MesSuivisN1[$dosNumN1][$date])) {
            if (self::isBalanceThisMonth(self::$MesSuivis[$dosNum][$date], self::$MesSuivisN1[$dosNumN1][$date], 'BS')) {
                $Color = "green";
            }

            if (
                self::isBalanceThisMonth(self::$MesSuivis[$dosNum][$date], self::$MesSuivisN1[$dosNumN1][$date], 'BI')
                || self::isBalanceThisMonth(self::$MesSuivis[$dosNum][$date], self::$MesSuivisN1[$dosNumN1][$date], 'SI')
            ) {
                $Color = "blue";
            }

            if (self::isBalanceThisMonth(self::$MesSuivis[$dosNum][$date], self::$MesSuivisN1[$dosNumN1][$date], 'BD')) {
                $Color = "red";
            }

            if (
                self::isBalanceThisMonth(self::$MesSuivis[$dosNum][$date], self::$MesSuivisN1[$dosNumN1][$date], 'BP')
                || self::isBalanceThisMonth(self::$MesSuivis[$dosNum][$date], self::$MesSuivisN1[$dosNumN1][$date], 'PB')
            ) {
                $Color = "yellow";
            }
        }

        return $Color;
    }

    /**
     * Indique si un import a eu lieu pour un type de balance ou non
     */
    static function isBalanceThisMonth($dataN, $dataN1, $type = '')
    {
        if ($type === '') {
            $return = ($dataN && $dataN["BALI_DATE_MAJBASE"] > 0)
                || ($dataN1 && $dataN1["BALI_DATE_MAJBASE"] > 0);
        } else {
            $return = ($dataN && ($dataN["BALI_TYPE"] == $type))
                || ($dataN1 && ($dataN1["BALI_TYPE"] == $type));
        }

        return $return;
    }

    /**
     * Construit le tableau du suivi d'activité
     */
    static function getSuivi($Option = array(), &$Year = '', &$YearN1 = '')
    {
        $optionGet = array(
            "select" => "cabinet.CAB_NOM, comptable.CC_NOM,comptable.CC_MAIL, station.STA_NUM, STA_SARL, lieu.LIE_CODE, lieu.LIE_NOM,
                        STA_CODECLIENT, chefSecteur.Nom, chefSecteur.Prenom, dossier.DOS_NUM, dossier.DOS_DEBEX,
                        dossier.DOS_FINEX, dossier.DOS_NUMPREC, station.STA_DERNCONNECTION_CDS",
            "join" => " left join stationcc on stationcc.STA_NUM = station.STA_NUM
                        left join comptable on comptable.CC_NUM = stationcc.CC_NUM
                        left join cabinet on cabinet.CAB_NUM=comptable.CAB_NUM
                        left join chefSecteur on chefSecteur.codeChefSecteur = lieu.codeChefSecteur
                        left join dossier on DOSSIER.STA_NUM = station.STA_NUM  "
        );

        // On élimine la valeur par défaut du filtre, car ça veut dire qu'on n'utilise pas le filtre
        if (isset($Option["codeChefSecteur"]) && $Option["codeChefSecteur"] === "-1") {
            unset($Option['codeChefSecteur']);
        }

        if (isset($Option["CAB_NUM"]) && $Option["CAB_NUM"] === "-1") {
            unset($Option['CAB_NUM']);
        }

        if (isset($Option["codeChefSecteur"]) && $Option["codeChefSecteur"] && isset($Option["CAB_NUM"]) && $Option["CAB_NUM"]) {
            $optionGet["where"] = array("and" => array("lieu.codeChefSecteur" => " = '" . $Option["codeChefSecteur"] . "' ", "cabinet.CAB_NUM" => " = '" . $Option["CAB_NUM"] . "' "));
        }

        if (isset($Option["codeChefSecteur"]) && $Option["codeChefSecteur"] && (!isset($Option["CAB_NUM"]) || !$Option["CAB_NUM"])) {
            $optionGet["where"] = array("and" => array("lieu.codeChefSecteur" => " = '" . $Option["codeChefSecteur"] . "' "));
        }

        if ((!isset($Option["codeChefSecteur"]) || !$Option["codeChefSecteur"]) && isset($Option["CAB_NUM"]) && $Option["CAB_NUM"]) {
            $optionGet["where"] = array("and" => array("cabinet.CAB_NUM" => " = '" . $Option["CAB_NUM"] . "' "));
        }

        if (isset($Option["ChampTri"]) && $Option["ChampTri"]) {
            $optionGet["order"] = " order by " . $Option["ChampTri"];
        } else {
            $optionGet["order"] = " order by lieu.LIE_NOM, DOS_NUM ASC";
        }

        $optionGet["where"]["and"]["STA_ACTIVE"] = " = 1 ";

        $MesStations = station::GetStation(false, $optionGet);

        $nb = 0;
        $MesLignesTableau = $STA_NUM = $DOS_NUMPREC = array();

        // On construit les lignes de notre tableau avec les informations des SARL
        foreach ($MesStations as $UneLignePoste) {
            $UneLigneTableau = array();

            $UneLigneTableau[] = array($UneLignePoste["LIE_CODE"] => array("align" => "center", "style" => "font-size:11px"));
            $UneLigneTableau[] = array($UneLignePoste["LIE_NOM"] => array("align" => "left", "style" => "font-size:11px"));
            $UneLigneTableau[] = array("<a href='../StationBack/open.php?STA_NUM=" . $UneLignePoste["STA_NUM"] . "' style='text-decoration:none;font-weight: bolder;'>" . $UneLignePoste["STA_SARL"] . "</a>" => array("align" => "left"));
            $UneLigneTableau[] = array(StringHelper::MySql2DateFr($UneLignePoste["DOS_DEBEX"]) => array("align" => "center", "style" => "font-size:11px"));
            $UneLigneTableau[] = array(StringHelper::MySql2DateFr($UneLignePoste["DOS_FINEX"]) => array("align" => "center", "style" => "font-size:11px"));
            $UneLigneTableau[] = array("<a href=\"mailto:" . $UneLignePoste["CC_MAIL"] . "\" style='text-decoration:none;font-weight: bolder;'>@ " . $UneLignePoste["CAB_NOM"] . " - " . $UneLignePoste["CC_NOM"] . "</a>" => array("align" => "left"));
            $UneLigneTableau[] = array($UneLignePoste["Nom"] => array("align" => "left", "style" => "font-size:11px"));

            if ($_SESSION["User"]->Niveau == 4) {
                $MaDateCds = StringHelper::MySql2DateFr($UneLignePoste["STA_DERNCONNECTION_CDS"]);
                $MaDateCds = str_replace(" ", "<br/>", $MaDateCds);
                $UneLigneTableau[] = array($MaDateCds => array("align" => "center", "style" => "font-size:9px"));
            }

            $STA_NUM[$UneLignePoste["DOS_NUM"]] = $UneLignePoste["STA_NUM"];
            $DOS_NUMPREC[$UneLignePoste["DOS_NUM"]] = $UneLignePoste["DOS_NUMPREC"];

            if (!$UneLignePoste["DOS_NUM"]) {
                $nb++;
                $cle = -$nb;
            } else {
                $cle = $UneLignePoste["DOS_NUM"];
            }

            $MesLignesTableau[$cle] = $UneLigneTableau;
        }

        $allDosNums = array_filter(array_keys($DOS_NUMPREC), function ($value) {
            return $value > 0;
        });

        $allDosNumsN1 = array_filter($DOS_NUMPREC, function ($value) {
            return $value > 0;
        });

        // On récupère les infos des imports de balance pour tous les dossiers N et N-1
        self::$MesSuivis = dbAcces::getAllBalanceImport($allDosNums, true);
        self::$MesSuivisN1 = dbAcces::getAllBalanceImport($allDosNumsN1, true);

        $maxBaliMois = null;

        // On détermine le dernier import N pour avoir une date de référence de départ
        foreach (self::$MesSuivis as $value) {
            foreach ($value as $data) {
                $baliMois = $data['BALI_MOIS'];

                if ($maxBaliMois === null || $baliMois > $maxBaliMois) {
                    $maxBaliMois = $baliMois;
                }
            }
        }

        $Year = date("Y", strtotime(str_replace('-00', '-01', $maxBaliMois)));
        $YearN1 = $Year - 1;

        $allPeriods = array();
        for ($i = 0; $i < 24; $i++) {
            $allPeriods[] = StringHelper::DatePlus($YearN1 . "-01-00", array("dateformat" => "Y-m-00", "moisplus" => $i));
        }
        
        // On peuple chaque ligne du tableau avec la couleur correspondant à l'import sur le mois
        foreach ($MesLignesTableau as $codeDossier => &$UneLigneDb) {
            $codeDossierN1 = $codeDossier > 0 ? $DOS_NUMPREC[$codeDossier] : 0;

            for ($i = 0; $i < 24; $i++) {
                $date = $allPeriods[$i];
                $Color = self::getColorImport($codeDossier, $codeDossierN1, $date);

                $UneLigneDb[] = array("<div style='width:11px;'></div>" => array("bgcolor" => $Color));
            }
        }

        return $MesLignesTableau;
    }
}

$MesLignes = Suivi::getSuivi($_POST, $Year, $YearN1);

include_once __DIR__ . '/../SuiviBack/MListe.php';
