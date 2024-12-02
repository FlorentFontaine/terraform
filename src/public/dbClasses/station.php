<?php

use Classes\DB\Database;
use Helpers\StringHelper;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../dbClasses/User.php";
require_once __DIR__ . "/../htmlClasses/html.php";

class station extends User
{
    public $Var;
    public $Infos;

    function __construct($prop)
    {
        foreach ($prop as $key => $value) {
            $this->Var[$key] = $value;
        }

        $this->Nom = $prop["STA_GERANT"];
        $this->initInfos();

        $this->NomTableUser = "station";
        $this->NomTableIdUser = "STA_NUM";
        $this->NumTableIdUser = $this->Var["STA_NUM"];
        $this->Niveau = 1;
    }

    private function initInfos()
    {
        $tab["Type"] = "station";
        $tab["Name"] = $this->Var["STA_GERANT"];

        $this->Type = $tab["Type"];
        $this->Mail = $this->Var["STA_MAIL"];
        $this->Infos = $tab;
    }

    static function Update($champ, $valeur, $STA_NUM)
    {
        $sql = "update station set $champ = '$valeur' where STA_NUM = '$STA_NUM'";

        Database::query($sql);
    }

    static function Add($Prop, &$ValEnDefaut = array(), &$ErreurUnique = false)
    {
        $TabVerif = array("STA_SARL", "LIE_NUM", "CC_NUM", "STA_CODECLIENT");
        $PropVerif = StringHelper::cleanTab(array("STA_", "LIE_", "CC_NUM"), $Prop);

        if (!$ValEnDefaut = StringHelper::donneesExists($PropVerif, $TabVerif)) {
            if (array_key_exists("STA_DATECREATION", $PropVerif) && !StringHelper::isDateValide($Prop["STA_DATECREATION"]) && !$_SESSION["agip_AG_NUM"]) {
                $ValEnDefaut = array();
                $ValEnDefaut[] = "STA_DATECREATION";

                return false;
            }

            $Prop["STA_VILLE"] = strtoupper($Prop["STA_VILLE"]);
            $TabSta = StringHelper::cleanTab(array("STA_", "LIE_"), $Prop);

            if (!$NumSta = dbAcces::AddStation($TabSta, $Prop["UpdateStation"])) {
                $ErreurUnique = true;

                return false;
            }

            if ($Prop["UpdateStation"]) {
                $NumSta = $Prop["UpdateStation"];
            }

            $TabLieSta = StringHelper::cleanTab(array("CC_"), $Prop);

            $TabLieSta["STA_NUM"] = $NumSta;

            dbAcces::LierStationCc($TabLieSta);

            return $TabLieSta["STA_NUM"];
        }

        return false;
    }

    static function NouvelleExercice($Prop, &$Erreur, &$Warning, $Confirm = false)
    {
        $DateDeb = StringHelper::DateFr2MySql($Prop["DOS_DEBEX"]);
        $DateFin = StringHelper::DateFr2MySql($Prop["DOS_FINEX"]);
        $DateFinN1 = StringHelper::DateFr2MySql($Prop["DOS_FINEXN1"]);

        $Prop["DOS_DEBEXCP"] = str_replace("-00", "-01", StringHelper::DateFr2MySql($Prop["DOS_DEBEXCP"]));
        $DateDebExCp = StringHelper::DateFr2MySql($Prop["DOS_DEBEXCP"]);

        if ($DateDebExCp == 0) {
            $DateDebExCp = "0000-00-00";
        }

        if (!StringHelper::isDateValide($Prop["DOS_DEBEX"])) {
            $Erreur["DOS_DEBEX"] = "Date de d&eacute;but d'exercice invalide";
        }

        if (!StringHelper::isDateValide($Prop["DOS_FINEX"])) {
            $Erreur["DOS_FINEX"] = "Date de fin d'exercice invalide";
        }

        if (
            $Prop["N1"]
            && !StringHelper::isDateValide($Prop["DOS_DEBEXCP"])
            || strtotime(date("Y-m-00", strtotime($DateDebExCp))) > strtotime(date("Y-m-00", strtotime($DateFinN1)))
        ) {
            $Erreur["DOS_DEBEXCP"] = "Date de comparaison N1 invalide";
        }

        $LastEx = station::GetLastEx($Prop["STA_NUM"], $Prop["DOS_NUMPREC"]);

        if (!$LastEx["DOS_NUMN1"]) {
            $LastEx["DOS_NUMN1"] = -1;
        }


        $Prop["DOS_NUMPREC"] = $LastEx["DOS_NUMN1"];

        if (!$Prop["DOS_NBMOIS"] || $Prop["DOS_NBMOIS"] < 0 || $Prop["DOS_NBMOIS"] > 23) {
            $Erreur["DOS_NBMOIS"] = "Nombre de mois invalide ";
        }

        if ($ExoEnCours = dbAcces::getDossier(null, null, $Prop["LIE_NUM"], StringHelper::DateFr2MySql($Prop["DOS_DEBEX"]), StringHelper::DateFr2MySql($Prop["DOS_FINEX"]))) {
            foreach ($ExoEnCours as $cle => $UnDossier) {
                if ($UnDossier["DOS_NUM"] != $Prop["UpdateDossier"]) {
                    $Erreur["DOSSIERENCOUR" . $cle] = "Dossier pour '" . $UnDossier["LIE_NOM"] . "' sur cette p&eacute;riode d&eacute;ja existant : <br/><br/>&nbsp;&nbsp;" . StringHelper::MySql2DateFr($UnDossier["DOS_DEBEX"]) . " - " . StringHelper::MySql2DateFr($UnDossier["DOS_FINEX"]) . " par '" . $UnDossier["STA_SARL"] . "'";
                }
            }

        }

        if ($Erreur) {
            return false;
        }

        //warning
        $MonNbMois = StringHelper::GetNbMoisEcart(date("Ym", strtotime(StringHelper::DateFr2MySql($Prop["DOS_FINEX"]))), date("Ym", strtotime(str_replace('-00', '-01', StringHelper::DateFr2MySql($Prop["DOS_DEBEX"])))));
        $MonNbMois++;

        $DifNbMois = $MonNbMois - $Prop["DOS_NBMOIS"];

        if ($DifNbMois) {
            $Warning["DOS_NBMOIS"] = "V&eacute;rifiez le nombre de mois d'exercices.";
        }

        if ($Warning && !$Confirm) {
            return false;
        }

        // verification en cas de modif que les nouvelles dates collent avec les imports effectu&eacute;s sur le dossier
        if ($Prop["UpdateDossier"] && ($MesImport = dbAcces::getBalImportM1($Prop["UpdateDossier"]))) {
            $MesDateImport = array();

            foreach ($MesImport as $UnImport) {
                $MesDateImport[] = $UnImport["BALI_MOIS"];
            }

            $MinImport = min($MesDateImport);
            $MaxImport = max($MesDateImport);

            if (!$MinImport) {
                $MinImport = $DateDeb;
            }

            if (!$MaxImport) {
                $MaxImport = $DateFin;
            }

            if (strtotime(date("Y-m-00", strtotime($DateDeb))) > strtotime($MinImport)) {
                $Erreur["DOS_DEBEX"] = "La date de d&eacute;but du nouvel exercice ne peut &ecirc;tre sup&eacute;rieur &agrave; la premi&egrave;re date d'import de balance du dossier (" . StringHelper::MySql2DateFr($MinImport) . ")";
            }

            if (strtotime(date("Y-m-00", strtotime($DateFin))) < strtotime($MaxImport)) {
                $Erreur["DOS_FINEX"] = "La date de fin du nouvel exercice ne peut &ecirc;tre inf&eacute;rieur &agrave; la derni&egrave;re date d'import de balance du dossier (" . StringHelper::MySql2DateFr($MaxImport) . ")";
            }

            if ($Erreur) {
                return false;
            }
        }
        ///////////////////////////////////////////////////////////////////////////////////////////////


        if (strtotime($DateDeb) < strtotime($DateFinN1)) {
            $Erreur["DOS_DEBEX"] = "La date de d&eacute;but du nouvel exercice ne peut &ecirc;tre inf&eacute;rieur &agrave; la date de fin de l'exercice N-1 ";
        }

        if (strtotime($DateDeb) > strtotime($DateFin)) {
            $Erreur["DOS_DEBEX"] = "La date de fin du nouvel exercice ne peut &ecirc;tre inf&eacute;rieur &agrave; la date de d&eacute;but du nouvel exercice ";
        }

        $Prop["DOS_DEBEX"] = $DateDeb;
        $Prop["DOS_FINEX"] = $DateFin;

        if ($Prop["DOS_DEBEXN1"]) {
            if (strtotime(date("Y-m-00", strtotime($DateDebExCp))) > strtotime(date("Y-m-00", strtotime($DateFinN1)))) {
                $Erreur["DOS_DEBEXCP"] = "La date de comparaison N-1 ne peut pas &ecirc;tre sup&eacute;rieur &agrave; la date de fin de l'exercice N-1";
            }
        } else {
            if (trim($Prop["DOS_DEBEXCP"]) && $Prop["DOS_DEBEXCP"] != "0000-00-00" && StringHelper::isDateValide($Prop["DOS_DEBEXCP"])) {
                $Erreur["DOS_DEBEXCP"] = "Aucunes donn&eacute;es &agrave; l'exercice N-1";
            }
        }

        if ($Erreur) {
            return false;
        }

        if (!$Prop["UpdateDossier"]) {
            $Prop["STA_DERNBAL"] = $Prop["DOS_DEBEX"];
        }

        if ($Prop["DOS_DEBEXCP"] > 0) {
            $Prop["DOS_PREMDATECP"] = date("Y-m-00", strtotime($Prop["DOS_DEBEXCP"]));
        } else {
            $Prop["DOS_PREMDATECP"] = "0000-00-00";
        }

        //trie du post
        $TabDos = StringHelper::cleanTab(array("DOS_", "STA_NUM"), $Prop, array("N1", "DEBEXCP"));

        //creation du dossier
        return dbAcces::AddDossier($TabDos, $Prop["UpdateDossier"]);
    }

    static function GetLastEx($STA_NUM, $DOS_NUM = null)
    {
        $whereR = $_SESSION["User"]->WhereRequired("dossier");
        $joinR = $_SESSION["User"]->JoinRequired("dossier");

        if ($DOS_NUM) {
            $whereR .= " and DOS_NUM = '$DOS_NUM' ";
        }

        $req = "select DOS_NUM as DOS_NUMN1,DOS_DEBEX as DOS_DEBEXN1,DOS_FINEX as DOS_FINEXN1,DOS_NBMOIS as DOS_NBMOISN1
                from dossier $joinR where dossier.STA_NUM = '$STA_NUM'
                and 1 $whereR order by DOS_FINEX DESC LIMIT 0,1";
        Database::query($req);

        return Database::fetchArray();
    }

    function WhereRequired($table)
    {
        if ($table === "station") {
            return " and station.STA_NUM = '" . $this->Var["STA_NUM"] . "' ";
        }

        return "";
    }

    function JoinRequired($table)
    {
        return "";
    }

    static function GetStation($STA_NUM = false, $option = array())
    {
        $joinR = $where = '';
        if (!isset($option["join"]) || !$option["join"]) {
            $joinR = $_SESSION["User"]->JoinRequired("station");
            $option["join"] = '';
        }

        $whereR = "";
        $whereR .= $_SESSION["User"]->WhereRequired("station");

        if (isset($option["where"]) && $option["where"]) {
            $whereR .= str_replace("WHERE 1", "", dbAcces::formatWhere($option["where"]));
        }

        if (isset($option["whereSTR"]) && $option["whereSTR"]) {
            $whereR .= $option["whereSTR"];
        }

        if ($STA_NUM) {
            $where = " and station.STA_NUM = '$STA_NUM' ";
        }

        if (!isset($option["select"]) || !$option["select"]) {
            $option["select"] = "*";
        }

        if (!isset($option["group"]) || !$option["group"]) {
            $option["group"] = "";
        }

        if (!isset($option["order"]) || !$option["order"]) {
            $option["order"] = "";
        }

        if ($joinR == '' || strpos($joinR, "lieu") === false) {
            $joinR .= " join lieu on lieu.LIE_NUM = station.LIE_NUM ";
        }

        $req = "select " . $option["select"] . " from station $joinR " . $option["join"] . "  where 1 $whereR $where " . $option["group"] . " " . $option["order"];
        
        Database::query($req);

        if ($STA_NUM) {
            return Database::fetchArray();
        } else {
            $Stations = array();

            while ($UneStation = Database::fetchArray()) {
                $Stations[$UneStation["STA_NUM"]] = $UneStation;
            }

            return $Stations;
        }
    }

    static function getCabinet($STA_NUM)
    {
        $req = "select distinct CAB_NOM
                from cabinet
                join comptable on comptable.CAB_NUM = cabinet.CAB_NUM
                join stationcc on stationcc.CC_NUM = comptable.CC_NUM
                where stationcc.STA_NUM = '$STA_NUM'";

        Database::query($req);
        $MonCab = Database::fetchArray();

        return $MonCab["CAB_NOM"];

    }

    static function GetNumCcStation($STA_NUM)
    {
        $req = "select CC_NUM from stationcc where stationcc.STA_NUM = '$STA_NUM' ";
        Database::query($req);

        $Return = array();

        while ($ln = Database::fetchArray()) {
            $Return[] = $ln["CC_NUM"];
        }

        return $Return;
    }

    static function GetLastBal($DOS_NUM)
    {
        $_SESSION["station_PREM_BAL"] = false;

        if (!$LastBal = dbAcces::getLastBalance($DOS_NUM)) {
            $_SESSION["station_PREM_BAL"] = true;
        }

        return $LastBal;
    }

    static function GetExercice($STA_NUM, $DOS_NUM = null)
    {
        $whereR = $_SESSION["User"]->WhereRequired("dossier");
        $joinR = $_SESSION["User"]->JoinRequired("dossier");

        if ($DOS_NUM) {
            $wherePlus = " and DOS_NUM = '$DOS_NUM'";
        } else {
            $wherePlus = "  and DOS_NUM in (SELECT DOS_NUM
                                            FROM dossier
                                            WHERE STA_NUM = '$STA_NUM'
                                            AND DOS_FINEX = (
                                                SELECT MAX( DOS_FINEX )
                                                FROM dossier
                                                WHERE STA_NUM = '$STA_NUM' )
                                            ) ";
        }

        $req = "select dossier.*
                from dossier $joinR
                where dossier.STA_NUM = '$STA_NUM'
                $wherePlus
                $whereR
                order by DOS_FINEX DESC LIMIT 0,1";
        Database::query($req);

        return Database::fetchArray();
    }

    static function GetAllExercice($STA_NUM, $DOS_NUM = null)
    {
        $whereR = $_SESSION["User"]->WhereRequired("dossier");
        $joinR = $_SESSION["User"]->JoinRequired("dossier");

        $WherePlus = "";

        if ($DOS_NUM) {
            $WherePlus .= " and DOS_NUM = '$DOS_NUM' ";
        }

        $req = "select DOS_NUM,DOS_DEBEX,DOS_FINEX ,DOS_NBMOIS,DOS_PREMDATECP,DOS_NUMPREC
                from dossier
                $joinR
                where dossier.STA_NUM = '$STA_NUM'
                and 1 $whereR $WherePlus
                order by DOS_NUM DESC";
        Database::query($req);

        $MesExo = array();

        while ($Ln = Database::fetchArray()) {
            $MesExo[] = $Ln;
        }

        return $MesExo;
    }
}
