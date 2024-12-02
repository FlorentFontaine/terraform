<?php

namespace Helpers;

use InvalidArgumentException;

class StringHelper
{

    public static function add_version($url)
    {
        $SCRIPT_APPELANT = "";
        if (isset($_SESSION["ISADMIN"]) && $_SESSION["ISADMIN"]) {
            $SCRIPT_APPELANT = $_SERVER["PHP_SELF"];
        }
        
        if (isset($url) && !$url) {
            throw new InvalidArgumentException("url_recode > url invalide");
        } elseif (!isset($url) || !$url) {
            throw new InvalidArgumentException("url_recode > url obligatoire");
        }
        
        if (!file_exists($url)) {
            throw new InvalidArgumentException("StringHelper::add_version > Fichier non trouvé pour l'URL '" . $url . "' << " . $SCRIPT_APPELANT);
        }

        return $url . "?dm=" . substr(hash('sha512', filemtime($url) . $url), -4);
    }

    public static function InputInError($Champ, $TabError)
    {
        if (in_array($Champ, $TabError)) {
            return " style='color:red' ";
        }
    }

    public static function NbHeuresEcart($HeureFin, $HeureDeb)
    {
        if (!$HeureFin || !$HeureDeb) {
            return false;
        }

        $HeureDebDec = self::DecimalicerHeure($HeureDeb);
        $HeureFinDec = self::DecimalicerHeure($HeureFin);

        if ($HeureFinDec < $HeureDebDec) {
            $HeureFinDec += 24;
        }

        if ($HeureFinDec == $HeureDebDec) {
            return 24;
        }

        return round($HeureFinDec - $HeureDebDec, 2);
    }

    public static function DecimalicerHeure($Heure)
    {
        $TabHeure = explode(":", $Heure);
        $DecimMinutes = 60 / $TabHeure[1];
        $DecimMinutes = 1 / $DecimMinutes;

        return (float)($TabHeure[0] + $DecimMinutes);
    }

    public static function donneesExists($TabDonnees, $TabVerif)
    {
        $ValEnDefaut = array();

        foreach ($TabVerif as $val) {
            if (!array_key_exists($val, $TabDonnees) || !$TabDonnees[$val] || $TabDonnees[$val] == "0000-00-00") {
                $ValEnDefaut[] = $val;
            }
        }

        return $ValEnDefaut;
    }

    public static function cleanTab($Key, $Tab, $KeyNot = array())
    {
        $Return = array();

        // On transforme $Key et $Tab en tableau
        if (!is_array($Key) && $Key) {
            $KeyT = $Key;
            $Key = array();
            $Key[] = $KeyT;
        }

        if (!is_array($KeyNot) && $KeyNot) {
            $KeyT = $KeyNot;
            $KeyNot = array();
            $KeyNot[] = $KeyT;
        }

        foreach ($Tab as $cle => $val) {
            $CleOk = true;

            //recherche des clés non voulue
            foreach ($KeyNot as $UneKeyNot) {
                if (strpos($cle, $UneKeyNot) !== false) {
                    $CleOk = false;
                    break;
                }
            }

            // Si la clé n'est pas écartée
            if ($CleOk) {
                //recherche des clés voulue
                foreach ($Key as $UneKey) {
                    if (stristr($cle, $UneKey)) {
                        $Return[$cle] = $val;
                        break;
                    }
                }
            }
        }

        return $Return;
    }

    public static function Signe($Nombre, $QueMoin = false, $NbDec = 2, $zero = false, $forceVirgule = false)
    {
        $retour = "";

        if (!$Nombre) {
            return $retour;
        }

        $retour = $Nombre;

        if ($Nombre > 0) {
            if ($QueMoin) {
                return self::NombreFr($Nombre, $NbDec, $zero, $forceVirgule);
            }

            $retour = "+ " . self::NombreFr($Nombre, $NbDec, $zero, $forceVirgule);
        } elseif (strpos($Nombre, "-") !== false) {
            $retour = str_replace("-", "- ", self::NombreFr($Nombre, $NbDec, $zero, $forceVirgule));
        }

        return $retour;
    }

    public static function DatePlus($Date, $options)
    {
        $Year = date('Y', strtotime(str_replace("-00", "-01", $Date)));
        $Month = date('m', strtotime(str_replace("-00", "-01", $Date)));
        $Day = date('d', strtotime(str_replace("-00", "-01", $Date)));

        if (!array_key_exists('dateformat', $options) || !$options['dateformat']) {
            $options['dateformat'] = 'Y-m-d'; //ne pas toucher !
        }

        if (!array_key_exists('anneesplus', $options)) {
            $options['anneesplus'] = 0;
        }

        if (!array_key_exists('moisplus', $options)) {
            $options['moisplus'] = 0;
        }

        if (!array_key_exists('joursplus', $options)) {
            $options['joursplus'] = 0;
        }

        $DateReturn = date("Y-m-d", mktime(0, 0, 0, $Month + $options['moisplus'], "01", $Year + $options['anneesplus']));

        //SI MOIS PLUS, ON DOIT VERIFIER QUE LE JOUR DU MOIS COURANT N'EST PAS SUPERIEUR AU DERNIER JOUR DU MOIS CALCULE
        if ($options["moisplus"]) {
            $dernierJourDateReturn = date("t", strtotime($DateReturn));

            if ($Day > $dernierJourDateReturn) {
                $Day = $dernierJourDateReturn;
            }
        }

        $DateReturn = date("Y-m-" . $Day, strtotime($DateReturn));

        //SI AJOUT DE JOURS EN PLUS, ON AJOUTE LA CLAUSE +/- X day à strtotime
        if ($options["joursplus"]) {
            if ($options["joursplus"] > 0) {
                $dayPlus = "+" . $options["joursplus"];
            } else {
                $dayPlus = $options["joursplus"];
            }

            $DateReturn = date("Y-m-d", strtotime($DateReturn . " " . $dayPlus . " day"));
        }

        if (isset($options['time']) && $options['time']) {
            return strtotime($DateReturn);
        }

        return date($options['dateformat'], strtotime($DateReturn));
    }


    public static function Date_ConvertSqlTab($date_sql)
    {
        $jour = substr($date_sql, 8, 2);
        $mois = substr($date_sql, 5, 2);
        $annee = substr($date_sql, 0, 4);
        $heure = substr($date_sql, 11, 2);
        $minute = substr($date_sql, 14, 2);
        $seconde = substr($date_sql, 17, 2);

        $key = array('annee', 'mois', 'jour', 'heure', 'minute', 'seconde');
        $value = array($annee, $mois, $jour, $heure, $minute, $seconde);

        return array_combine($key, $value);
    }

    public static function DateMoisTxt($mois_brut)
    {
        $mois = "";

        if ($mois_brut == '01') {
            $mois = 'Janvier';
        } elseif ($mois_brut == '02') {
            $mois = 'F&eacute;vrier';
        } elseif ($mois_brut == '03') {
            $mois = 'Mars';
        } elseif ($mois_brut == '04') {
            $mois = 'Avril';
        } elseif ($mois_brut == '05') {
            $mois = 'Mai';
        } elseif ($mois_brut == '06') {
            $mois = 'Juin';
        } elseif ($mois_brut == '07') {
            $mois = 'Juillet';
        } elseif ($mois_brut == '08') {
            $mois = 'Ao&ucirc;t';
        } elseif ($mois_brut == '09') {
            $mois = 'Septembre';
        } elseif ($mois_brut == '10') {
            $mois = 'Octobre';
        } elseif ($mois_brut == '11') {
            $mois = 'Novembre';
        } elseif ($mois_brut == '12') {
            $mois = 'D&eacute;cembre';
        }

        return $mois;
    }

    public static function DateMoisTxtCourt($mois_brut)
    {
        $mois = "";

        if ($mois_brut == '01') {
            $mois = 'Janv.';
        } elseif ($mois_brut == '02') {
            $mois = 'F&eacute;v.';
        } elseif ($mois_brut == '03') {
            $mois = 'Mars';
        } elseif ($mois_brut == '04') {
            $mois = 'Avril';
        } elseif ($mois_brut == '05') {
            $mois = 'Mai';
        } elseif ($mois_brut == '06') {
            $mois = 'Juin';
        } elseif ($mois_brut == '07') {
            $mois = 'Juil.';
        } elseif ($mois_brut == '08') {
            $mois = 'Ao&ucirc;t';
        } elseif ($mois_brut == '09') {
            $mois = 'Sept.';
        } elseif ($mois_brut == '10') {
            $mois = 'Oct.';
        } elseif ($mois_brut == '11') {
            $mois = 'Nov.';
        } elseif ($mois_brut == '12') {
            $mois = 'D&eacute;c.';
        }

        return $mois;
    }

    public static function DateComplete($date_sql)
    {
        $tab_date = self::Date_ConvertSqlTab($date_sql);
        $mktime_brut = mktime(
            $tab_date['heure'],
            $tab_date['minute'],
            $tab_date['seconde'],
            $tab_date['mois'],
            $tab_date['jour'],
            $tab_date['annee']
        );

        return self::DateMoisTxt(date('m', $mktime_brut)) . ' ' . $tab_date['annee'];
    }

    public static function DateSemiComplete($date_sql)
    {
        $tab_date = self::Date_ConvertSqlTab($date_sql);
        $mktime_brut = mktime(
            $tab_date['heure'],
            $tab_date['minute'],
            $tab_date['seconde'],
            $tab_date['mois'],
            $tab_date['jour'],
            $tab_date['annee']
        );

        return self::DateMoisTxtCourt(date('m', $mktime_brut)) . ' ' . $tab_date['annee'];
    }

    public static function GetNbMoisEcart($a, $b)
    {
        $date1 = intval(substr($a, 0, 4)) * 12 + intval(substr($a, 4, 2));
        $date2 = intval(substr($b, 0, 4)) * 12 + intval(substr($b, 4, 2));

        return abs($date1 - $date2);
    }


    public static function GetNbJourEcart($Date1, $Date2)
    {
        $ecart = round((strtotime($Date1) - strtotime($Date2)) / (60 * 60 * 24) + 1);

        if (strtotime($Date2) > strtotime($Date1)) {
            $ecart = (-$ecart) - 1;
        }

        return $ecart;
    }

    public static function getNbDim($DateFin, $DateDeb)
    {
        $cpt = 0;

        while ($DateDeb <= $DateFin) {
            if (date("N", strtotime($DateDeb)) == 7) {
                $cpt++;
            }

            $DateDeb = self::DatePlus($DateDeb, array("joursplus" => 1));
        }

        return $cpt;
    }

    public static function isDateValide($Date, $DateMois = false)
    {
        $Date = self::DateFr2Mysql($Date);

        list($year, $month, $day) = preg_split('/[\/.-]/', $Date);

        if (strlen($year) > 4) {
            return false;
        }

        if (strlen($year) == 2) {
            $year = "20" . $year;
        }

        if ($day == "00" && $DateMois) {
            $day = "01";
        }

        if ($year <= 1970) {
            $year = "null";
        }

        return checkdate($month, $day, $year);
    }

    public static function DateFr2MySql($date, $option = false)
    {
        if ($date == "") {
            return "";
        }

        $date = trim($date);

        if ($date == 'now') {
            return date("Y/m/d H:i:s");
        }

        $p = strpos($date, " ");

        if ($p) {
            $heure = trim(substr($date, $p + 1));
            $date = trim(substr($date, 0, $p));
        }

        // TODO Performance de check en amont les RegEx ? Double action du moteur de RegEx
        if (strlen(trim($date)) == 4) {
            $year = (int)$date;
            $month = '00';
            $day = '00';
        } elseif (strlen(trim($date)) == 5 && preg_match("/\//", $date) && $option["jourmois"]) {
            list($day, $month) = preg_split('/[\/.-]/', $date); // PREG_SPLIT_NO_EMPTY ?
            $year = '0000';
        } elseif (strlen(trim($date)) == 5 && preg_match("/\//", $date)) {
            list($month, $year) = preg_split('/[\/.-]/', $date); // PREG_SPLIT_NO_EMPTY ?
            $day = '00';
        } else {
            list($day, $month, $year) = preg_split('/[\/.-]/', $date); // PREG_SPLIT_NO_EMPTY ?
        }

        $day = trim($day);
        $month = trim($month);
        $year = trim($year);

        $retour = "";

        // test si saisi version américaine
        if ((strlen($day) == 4) && ($year == '' || strlen($year) == 2)) {
            $temp = '';
            // saisie de juste de l'année
            if ($year) {
                $temp = $year;
            }
            $year = $day;
            $day = $temp;
        } // saisie de 01/2006
        elseif ((strlen($month) == 4) && (strlen($day) == 2) && $year == '') {
            $year = $month;
            $month = $day;
            $day = '00';
        }

        if (!$option && isset($heure) && $heure) {
            list($hour, $min, $sec) = preg_split('/[:.-]/', $heure); // PREG_SPLIT_NO_EMPTY ?

            if (!$hour) {
                $hour = "00";
            }
            if (!$min) {
                $min = "00";
            }
            if (!$sec) {
                $sec = "00";
            }

            $MaDate = "$year-$month-$day $hour:$min:$sec";

            if ($day == "00") {
                $day = "01";
            }

            if (checkdate($month, $day, $year)) {
                $retour = $MaDate;
            }
        } else {
            if ($option == 'mois') {
                $retour =  "$year-$month-00";
            } elseif ($option == 'annee') {
                $retour =  "$year-00-00";
            } else {
                if (strlen($year) == 2) {
                    $year = "20" . $year;
                }

                $MaDate = "$year-$month-$day";

                if ($day == "00") {
                    $day = "01";
                }

                if ($year == "0000") {
                    $year = "2009";
                }

                if (checkdate($month, $day, $year)) {
                    $retour = $MaDate;
                }
            }
        }

        return $retour;
    }

    public static function MySql2DateFr($date, $optionheure = false)
    {
        $date = trim($date);
        $date = str_replace("0000-00-00 00:00:00", "", $date);
        $date = str_replace("0000-00-00", "", $date);

        if ($date == "") {
            return "";
        }

        $heure = '';

        $p = strpos($date, " ");

        if ($p) {
            $heure = trim(substr($date, $p + 1));
            $date = trim(substr($date, 0, $p));
        }

        list($year, $month, $day) = preg_split('/[\/.-]/', $date); // PREG_SPLIT_NO_EMPTY ?

        if ($heure !== '') {
            list($hour, $min, $sec) = preg_split('/[:.-]/', $heure); // PREG_SPLIT_NO_EMPTY ?
        }

        if ($optionheure == 'jour') {
            $retour = $day;
            $optionheure = false;
        } elseif ($optionheure == 'mois') {
            $retour = $month;
            $optionheure = false;
        } elseif ($day == '00' || $day <= 0 || $day > 31) {
            $retour = "$month/$year";
        } else {
            $retour = "$day/$month";

            if ($year > 0) {
                $retour .= "/$year";
            }
        }

        if ($optionheure || $heure) {
            if (!isset($hour) || !$hour) {
                $hour = "00";
            }

            if (!isset($min) || !$min) {
                $min = "00";
            }

            if (!isset($sec) || $sec <= 0) {
                $sec = "";
            } else {
                $sec = ":$sec";
            }

            $retour .= " $hour:$min$sec";
        }

        if ($retour <= 0) {
            $retour = "";
        }

        return $retour;
    }

    public static function Texte2Nombre($nombre)
    {
        if (is_numeric($nombre)) {
            return $nombre;
        }

        $negatif = strpos($nombre, '-') !== false;

        $nombre = trim($nombre);
        $nombre = str_replace(',', '.', $nombre);

        $tab = array(' ', 'e', '-', '_', '+', '\t', '\r', '\n', chr(160), '&nbsp;');
        $nombre = str_replace($tab, '', $nombre);

        if ($negatif) {
            $nombre = -$nombre;
        }

        return $nombre;
    }

    public static function NombreFr($nombre, $nbdec = 2, $zero = false, $forceVirgule = false, $pourcentage = false)
    {
        global $Arrondir;

        $MonArrondi = $Arrondir;

        if ($Arrondir && $forceVirgule) {
            $MonArrondi = false;
        }

        if ($MonArrondi) {
            $nbdec = 0;
        }

        if ($nombre != 0) {
            $nombre = trim(html_entity_decode(stripslashes($nombre)));
            $nombre = number_format($nombre, $nbdec, ",", " ");

            if (isset($_GET["xls"]) && $_GET["xls"]) {
                $nombre = str_replace("", "&nbsp;", $nombre);
                $nombre = str_replace("", " ", $nombre);
                $nombre = str_replace(",", ".", $nombre);
            }
        } else {
            $nombre = "";
        }

        if ($nombre == "0,00" || $nombre == "-0,00") {
            $nombre = "";
        }

        if ($nombre == "" && $zero) {
            $nombre = number_format((float)$nombre, $nbdec, ",", " ");
        }

        if ($nombre != "" && $pourcentage) {
            $nombre = $nombre . ' %';
        }

        return $nombre;
    }

    /**
     * Fonction permettant de remplacer tous les caractères accentués (non HTML)
     * par leur homologue non accentué
     */
    public static function accentToNoAccent($string, $MAJ = false)
    {
        if ($MAJ) {
            return strtr($string, 'ÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ', 'AAAAACEEEEIIIINOOOOOUUUUY');
        }

        return strtr($string, 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ', 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    }

    public static function TextFilter($in)
    {
        $search = array('@[èéêë]@i', '@[àáâãä]@i', '@[ìíîï]@i', '@[ùúûü]@i', '@[òóôõö]@i', '@ç@i');
        $replace = array('e', 'a', 'i', 'u', 'o', 'c');

        return preg_replace($search, $replace, $in);
    }

    public static function getColorSituations(?string $type = null, bool $onlyColor = false)
    {
        $situationsColor = [
            "BS" => array("color" => "green", "desc" => "Balance standard", "num" => 1),
            "BI" => array("color" => "blue", "desc" => "Situation interm&eacute;diaire", "num" => 2),
            "BP" => array("color" => "yellow", "desc" => "Pr&eacute;-Bilan", "num" => 3),
            "BD" => array("color" => "red", "desc" => "Bilan", "num" => 4),
            "" => array("color" => "", "desc" => "", "num" => 5)
        ];

        if (null !== $type && in_array(strtoupper($type), ["BS", "BI", "BP", "BD", ""])) {
            if ($onlyColor) {
                return $situationsColor[$type]['color'];
            }

            return $situationsColor[$type];
        }

        return $situationsColor;
    }
}
