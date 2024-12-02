<?php

use Classes\DB\Database;
use Helpers\StringHelper;

class dbAcces
{
    public static $db;

    static function getComptesBilan($d = null)
    {

        $where = "";
        $tabRetour = [];
        $d["join"] ??= '';

        //Boucle de construction du where
        foreach ($d["tabCriteres"] as $nomChamp => $valeur) {
            if (!is_array($valeur)) {
                if (!isset($d["tabOP"][$nomChamp]) || !$d["tabOP"][$nomChamp]) {
                    $operation = " = "; //L'operation par défaut
                } else {
                    $operation = $d["tabOP"][$nomChamp];
                }

                $where .= " And " . $nomChamp . " " . $operation . " \"" . $valeur . "\" ";
            } elseif (isset($valeur["whereperso"])) {
                $where .= " AND ( ";
                $where .= $nomChamp . $valeur["whereperso"];
                $where .= " ) ";
            }
        }

        if (!isset($d["select"]) || !$d["select"]) {
            $d["select"] = " * ";
        }

        //CONSTRUCTION DE LA REQUETE SQL
        $sql = "SELECT " . $d["select"] . " FROM comptebilan
            " . $d["join"] . "
            WHERE 1
            " . $where . "
            " . $d["triRequete"] . " ";

        Database::query($sql);
        while ($ligne = Database::fetchArray()) {
            if (!$d["index"]) {
                $tabRetour[$ligne["code_compte"]] = $ligne;
            } else {
                if (!is_array($d["index"])) {
                    $tabRetour[$ligne[$d["index"]]] = $ligne;
                } else {
                    $kI = array_keys($d["index"]);
                    $k1 = $d["index"][$kI[0]];
                    $k2 = $d["index"][$kI[1]];
                    $k3 = $d["index"][$kI[2]];

                    if (count($d["index"]) == 1) {
                        $tabRetour[$ligne[$k1]] = $ligne;
                    } elseif (count($d["index"]) == 2) {
                        $tabRetour[$ligne[$k1]][$ligne[$k2]] = $ligne;
                    } elseif (count($d["index"]) == 3) {
                        $tabRetour[$ligne[$k1]][$ligne[$k2]][$ligne[$k3]] = $ligne;
                    }
                }
            }
        }

        return $tabRetour;
    }

    static function getResultatsBilan($DOS_NUM, $Mois, $Type = null, $CPB_NUM = null)
    {
        $WherePlus = "";

        if ($Type) {
            $WherePlus = " and AS_comptes_comptepostebilan.AS_TYPEVAL = '$Type' ";
        }

        if ($CPB_NUM) {
            $sql = "select Balance.codeCompte,BAL_CUMUL,Sens
                    from Balance
                    join AS_comptes_comptepostebilan on AS_comptes_comptepostebilan.codeCompte = Balance.codeCompte
                    where BAL_MOIS = '$Mois' and CPTB_NUM = '$CPB_NUM' and DOS_NUM='" . $DOS_NUM . "' $WherePlus ";
        } else {
            $sql = "select CPTB_NUM,sum(BAL_CUMUL) as Montant,Sens
                    from Balance
                    join AS_comptes_comptepostebilan on AS_comptes_comptepostebilan.codeCompte = Balance.codeCompte
                    where BAL_MOIS = '$Mois' and DOS_NUM='" . $DOS_NUM . "' $WherePlus
                    group by AS_comptes_comptepostebilan.CPTB_NUM";
        }

        Database::query($sql);

        $Return = array();

        while ($ln = Database::fetchArray()) {
            if ($CPB_NUM) {
                $Return[$ln["codeCompte"]] = $ln;
            } else {
                $Return[$ln["CPTB_NUM"]] = $ln;
            }

        }

        return $Return;
    }

    static function get_LieByCluster($STA_NUM_CLUSTER)
    {
        if (!$STA_NUM_CLUSTER) {
            return array();
        }

        $sql = "
        select lieu.* from
        station 
        join station station_fille on station_fille.STA_NUM_CLUSTER = station.STA_NUM
        join lieu on lieu.LIE_NUM = station_fille.LIE_NUM
        where 1
        and station.STA_NUM = $STA_NUM_CLUSTER
        
    ";

        $res = Database::query($sql);

        $return = array();

        while ($ln = Database::fetchArray($res)) {
            $return[$ln["LIE_NUM"]] = $ln;
        }

        return $return;
    }

    static function getMail($LIE_NUM, $DOS_NUM, $Formated = false)
    {
        $wp = "";

        if ($DOS_NUM) {
            $wp .= " and dossier.DOS_NUM = $DOS_NUM ";
        }

        $sql = "select STA_MAIL as mailsta,chefRegion.E_Mail as mailcdr,chefSecteur.E_Mail as mailcds,chefVente.E_Mail as mailcdv,CC_MAIL as mailcc,dossier.DOS_FINEX
                from station join dossier on dossier.STA_NUM = station.STA_NUM 
                join stationcc on stationcc.STA_NUM = station.STA_NUM
                join comptable on comptable.CC_NUM = stationcc.CC_NUm
                join lieu on lieu.LIE_NUM = station.LIE_NUM
                left join ChefVente on lieu.codeChefVente = chefVente.codeChefVente
                left join chefSecteur on lieu.codeChefSecteur = chefSecteur.codeChefSecteur
                left join chefRegion on lieu.codeChefRegion = chefRegion.codeChefRegion 
                where lieu.LIE_NUM = $LIE_NUM $wp
                order by DOS_FINEX DESC";
        $res = Database::query($sql);

        $return = array();

        while ($ln = Database::fetchArray($res)) {
            $return["mailsta"][0] = $ln["mailsta"];
            $return["mailcds"][0] = $ln["mailcds"];
            $return["mailcc"][] = $ln["mailcc"];
        }

        if ($Formated) {
            $flatten_mails = array_reduce($return, function ($result, $current) {
                return array_merge($result, $current);
            }, []);

            $return = implode(',', $flatten_mails);
        }

        return $return;
    }

    static function MAJBalanceMontantMois($DOS_NUM, $Mois)
    {
        $moisPrec = dbAcces::getBalImportM1($DOS_NUM, $Mois);
        $moisPrec = $moisPrec[0]["BALI_MOISM1"];

        $sql = "update `balance` balmois
                join (select codeCompte,DOS_NUM,BAL_CUMUL
                        from balance
                        where dos_num = $DOS_NUM and bal_mois = '$moisPrec'
                    ) as balmoisprec on  balmoisprec.DOS_NUM = balmois.DOS_NUM and balmois.codeCompte = balmoisprec.codeCompte
                set balmois.bal_balance = ( balmois.BAL_CUMUL - balmoisprec.BAL_CUMUL)WHERE balmois.DOS_NUM = $DOS_NUM and balmois.BAL_MOIS='$Mois'";

        Database::query($sql);
    }

    static function getBalImportM1($DOS_NUM, $BALI_MOIS = null)
    {
        $whereSql = "";

        if ($BALI_MOIS) {
            $whereSql .= " and BALI_MOIS='$BALI_MOIS' ";
        }

        $sql = "select * from balanceimport where DOS_NUM='$DOS_NUM' $whereSql order by BALI_MOIS DESC";
        Database::Query($sql);

        $mesMois = array();

        while ($ln = Database::FetchArray()) {
            $mesMois[] = $ln;
        }

        return $mesMois;
    }

    static function getAllBalanceImport($DOS_NUM = array(), $onlyValidated = false)
    {
        $whereSql = "";

        if (!empty($DOS_NUM)) {
            $whereSql .= " and DOS_NUM IN (" . implode(',', $DOS_NUM) . ")";
        }

        if ($onlyValidated) {
            $whereSql .= " and BALI_DATE_MAJBASE > 0";
        }

        $sql = "select DOS_NUM, BALI_MOIS, BALI_TYPE, BALI_DATE_MAJBASE, BALI_DATE_MAJBASEAGIP
                from balanceimport where 1 $whereSql order by BALI_MOIS DESC";
        Database::Query($sql);

        $mesMois = array();

        while ($ln = Database::FetchArray()) {
            $mesMois[$ln['DOS_NUM']][$ln['BALI_MOIS']] = $ln;
        }

        return $mesMois;
    }

    static function AddLieu($Prop, $upd = 0)
    {
        $vals = dbAcces::formatInsertUpdate($Prop);

        if (!$upd) {
            $sql = "insert into lieu set $vals";
        } else {
            $sql = "update lieu set $vals where LIE_NUM = $upd";
        }

        if (Database::query($sql)) {
            if (!$upd) {
                return Database::lastPK();
            }

            return true;
        }

        return false;
    }

    static function formatInsertUpdate($Prop)
    {
        $Return = "";
        $Prem = true;

        foreach ($Prop as $Key => $Value) {
            if ($Prem) {
                $Prem = false;
            } else {
                $Return .= ",";
            }

            $Return .= "$Key=\"" . str_replace("\"", "'", $Value) . "\" ";
        }

        return $Return;
    }

    static function getLieu($LIE_NUM = NULL, $opt = NULL)
    {
        $wherePlus = ($LIE_NUM) ? " and LIE_NUM = '$LIE_NUM' " : "";

        if (isset($opt["order"]) && $opt["order"]) {
            $order = " order by " . $opt["order"];
        } else {
            $order = " order by LIE_CODE";
        }

        if (!isset($opt["join"]) || !$opt["join"]) {
            $opt["join"] = "";
        }

        $wherePlus .= (isset($opt["where"]) && $opt["where"]) ? $opt["where"] : "";

        $sql = "select distinct lieu.* from lieu " . $opt["join"] . " where 1 $wherePlus $order";
        $res = Database::query($sql);

        $return = array();

        while ($ln = Database::fetchArray($res)) {
            $return[$ln["LIE_NUM"]] = $ln;
        }

        return $return;
    }

    static function get_ObjectifSARL($DOS_NUM, $Type, $Marge, $Mois, $OrdrePourSplit = null)
    {
        $wherePlusPoste = $wherePlus = "";

        if ($Type == "Produits") {
            $wherePlus .= " and Famille not like '%CARBURANT%' ";
            $wherePlusPoste .= " and Famille not like '%CARBURANT%' ";
        } elseif ($Type == "Carburants") {
            $wherePlus .= " and Famille like '%CARB%' ";
            $wherePlusPoste .= " and Famille like '%CARB%' ";
            $Type = "Produits";
        }

        if ($Type) {
            $wherePlus .= " and compteposte.Type = '$Type' ";
            $wherePlusPoste .= " and compteposte.Type = '$Type' ";
        }

        if ($OrdrePourSplit) {
            $wherePlus .= " and compteposte.ordre " . $OrdrePourSplit["test"] . " '" . $OrdrePourSplit["value"] . "' ";
            $wherePlusPoste .= " and compteposte.ordre " . $OrdrePourSplit["test"] . " '" . $OrdrePourSplit["value"] . "' ";
        }

        if ($Marge) {
            $wherePlus .= " and (Marge = '1' OR Famille like '%PRODUITS MANDAT%' OR Famille like '%PRODUITS HORS MANDAT%') ";
            $wherePlusPoste .= " and (Marge = '1' OR Famille like '%PRODUITS MANDAT%' OR Famille like '%PRODUITS HORS MANDAT%') ";
        }

        if ($Mois) {
            $wherePlus .= " and Periode >= '$Mois' ";
        }

        $wherePlus .= " and PrevAgip = '0' ";

        $return = array();

        $sql = "select *, SUM(comptes.CPT_VISIBLE) as visible from compteposte
                left join comptes on comptes.codePoste = compteposte.codePoste
                where 1 $wherePlusPoste
                group by compteposte.codePoste
                having visible > 0
                order by compteposte.ordre";
        
        $res = Database::query($sql);

        while ($ln = Database::fetchArray($res)) {
            $saisonDebut = date("Y-m-01", strtotime($_SESSION["station_DOS_DEBEX"]));
            $saisonFin = StringHelper::DatePlus($saisonDebut, array("moisplus" => $_SESSION["station_DOS_NBMOIS"]));//$_SESSION["station_DOS_NBMOIS"]

            while (strtotime($saisonDebut) < strtotime($saisonFin)) {
                $saisonDebutF = date("Y-m-00", strtotime($saisonDebut));

                $return[$ln["codePoste"]][$saisonDebutF] = $ln;
                $saisonDebut = StringHelper::DatePlus($saisonDebut, array("moisplus" => 1));
            }
        }

        $sql = "select Famille,SsFamille,Libelle,Marge,Periode,PrevTaux,Annuel,Montant,PrevTauxMontant,resultatposte.codePoste
                from compteposte
                left join resultatposte on compteposte.codePoste = resultatposte.codePoste
                where DOS_NUM = '$DOS_NUM' $wherePlus
                order by month(Periode),Famille,SsFamille,compteposte.ordre";

        $res = Database::query($sql);

        while ($ln = Database::fetchArray($res)) {
            $return[$ln["codePoste"]][$ln["Periode"]] = $ln;
        }

        return $return;
    }

    static function getchefSecteur($codeChefSecteur = NULL, $opt = NULL)
    {
        $WherePlus = "";

        if ($opt["order"]) {
            $order = " order by " . $opt["order"];
        } else {
            $order = " order by ChefSecteur.Nom,ChefSecteur.Prenom";
        }

        $WherePlus .= $opt["where"];

        $sql = "select * from ChefSecteur " . $opt["join"] . " where 1 $WherePlus $order";

        $res = Database::query($sql);

        $Return = NULL;

        while ($ln = Database::fetchArray($res)) {
            $Return[$ln["codeChefSecteur"]] = $ln;
        }

        return $Return;
    }

    static function getchefRegion($codeChefRegion = NULL, $opt = NULL)
    {
        $WherePlus = "";

        if ($opt["order"]) {
            $order = " order by " . $opt["order"];
        } else {
            $order = " order by ChefRegion.Nom,ChefRegion.Prenom";
        }

        $WherePlus .= $opt["where"];

        $sql = "select * from ChefRegion " . $opt["join"] . " where 1 $WherePlus $order";

        $res = Database::query($sql);

        $Return = NULL;

        while ($ln = Database::fetchArray($res)) {
            $Return[$ln["codeChefRegion"]] = $ln;
        }

        return $Return;
    }

    static function getCabinet($cabNum = null, $opt = null)
    {
        $wherePlus = "";
        $groupBy = "";
        $addSelect = "";

        if (isset($opt["order"]) && $opt["order"]) {
            $order = " order by " . $opt["order"];
        } else {
            $order = " order by cabinet.CAB_NOM ";
        }

        if (isset($opt["groupBy"]) && $opt["groupBy"]) {
            $groupBy = " group by " . $opt["groupBy"];
        }

        if (isset($opt["addSelect"]) && $opt["addSelect"]) {
            $addSelect = $opt["addSelect"];
        }

        $wherePlus .= isset($opt["where"]) && $opt["where"] ? $opt["where"] : "";

        if ($cabNum) {
            $wherePlus .= " and CAB_NUM = " . $cabNum;
        }

        $sql = "SELECT * " . $addSelect . " FROM cabinet " . ($opt["join"] ? $opt["join"] : "") . " WHERE 1 " . $wherePlus . $groupBy . $order . ";";

        Database::query($sql);

        $return = null;

        while ($ln = Database::fetchArray()) {
            $return[$ln["CAB_NUM"]] = $ln;
        }

        return $return;
    }

    static function setCabinet($data, $cabNum = null)
    {

        if (!$cabNum) {
            $sql = "INSERT INTO cabinet SET ";
            foreach ($data as $key => $value) {
                $sql .= "$key = ' " . $value . "',";
            }
            $sql = substr($sql, 0, -1) . ";";
        } else {
            $sql = "UPDATE cabinet SET ";
            foreach ($data as $key => $value) {
                $sql .= "$key = '" . $value . "',";
            }
            $sql = substr($sql, 0, -1) . " WHERE CAB_NUM = " . $cabNum . ";";
        }

        if (Database::query($sql)) {
            if (!$cabNum) {
                return Database::lastPK();
            }

            return $cabNum;
        }

        return false;
    }

    static function deleteCabinet($data)
    {
        $sql = "DELETE FROM cabinet WHERE CAB_NUM = " . $data["CAB_NUM"] . ";";

        if (Database::query($sql)) {
            header("Location: ./?delete=1");
        }

        throw new Exception("Erreur lors de la suppression du cabinet comptable");
    }

    static function get_BalanceFormat($bafNum = null)
    {
        $where = (isset($bafNum) && $bafNum) ? " and bafNum = '" . $bafNum . "'" : "";

        $sql = "SELECT balanceformat.* FROM
                balanceformat
                WHERE 1
                " . $where . "
                ORDER BY BAF_LIBELLE ASC";

        Database::query($sql);

        $return = null;

        while ($ln = Database::fetchArray()) {
            $return[$ln["BAF_NUM"]] = $ln;
        }

        return $return;
    }

    static function getStation($STA_NUM = NULL, $opt = NULL)
    {
        $WherePlus = "";
        $join = "";

        if ($STA_NUM) {
            $WherePlus .= " and STA_NUM = '$STA_NUM' ";
        }

        $WherePlus .= $opt["where"];

        $join .= $opt["join"];

        $sql = "select * from station $join where 1 $WherePlus";

        $res = Database::query($sql);

        $Return = NULL;

        while ($ln = Database::fetchArray($res)) {
            $Return[$ln["STA_NUM"]] = $ln;
        }

        return $Return;
    }

    static function AddStation($Prop, $upd = 0)
    {

        $Prop["STA_DATECREATION"] = StringHelper::DateFr2MySql($Prop["STA_DATECREATION"]);

        if (!isset($Prop["STA_MAIL"]) || !$Prop["STA_MAIL"]) {
            $Prop["STA_MAIL"] = "NULL";
        }

        $Vals = dbAcces::formatInsertUpdate($Prop);//,"station"

        $Vals = str_replace("\"NULL\"", "NULL", $Vals);

        if (!$upd) {
            $sql = "insert into station set AG_NUM=1,STA_INFOCOMPLET=1,$Vals";
        } else {
            $sql = "update station set AG_NUM=1,STA_INFOCOMPLET=1, $Vals where STA_NUM = $upd";
        }

        if (Database::query($sql)) {
            if (!$upd) {
                return Database::lastPK();
            }

            return true;
        } else {
            return false;
        }
    }

    static function LierStationCc($TabLie)
    {
        $sql = "delete from stationcc where STA_NUM = '" . $TabLie["STA_NUM"] . "'";
        Database::query($sql);

        $MesCC = $TabLie["CC_NUM"];

        foreach ($MesCC as $val) {
            $Prop["CC_NUM"] = $val;
            $Prop["STA_NUM"] = $TabLie["STA_NUM"];

            $Vals = dbAcces::formatInsertUpdate($Prop);

            $sql = "insert into stationcc set $Vals";

            Database::query($sql);
        }

        if ($Prop["CC_NUM"]) {
            return true;
        } else {
            return false;
        }
    }

    static function getEquivalenceComptes($STA_NUM = false)
    {
        $wherePlus = "";

        if ($STA_NUM) {
            $wherePlus .= " and (liaisoncompte.STA_NUM = 0 or liaisoncompte.STA_NUM = $STA_NUM )";
        } else {
            $wherePlus .= " and (liaisoncompte.STA_NUM = 0 )";
        }


        $sql = "select * from liaisoncompte join comptes on comptes.code_compte = liaisoncompte.LICO_code_compte where 1 $wherePlus and CAB_NUM = '" . $_SESSION["User"]->Var["CAB_NUM"] . "'";

        $res = Database::query($sql);

        $Return = NULL;

        while ($ln = Database::fetchArray($res)) {
            $Return[$ln["LICO_nouvcompte"] . "#||#" . $ln["STA_NUM"]] = $ln;
        }

        return $Return;
    }

    static function getCP_NUM_par_QUOTE_PART($QUOTEPART_NUM)
    {
        $WherePlus = "";

        if ($QUOTEPART_NUM) {
            $WherePlus .= " and li_quotepart_poste.QUOTEPART_NUM = $QUOTEPART_NUM";
        }

        $sql = "

        select distinct li_quotepart_poste.CP_NUM,compteposte.*,li_quotepart_poste.QUOTEPART_NUM
        from li_quotepart_poste
        join compteposte on compteposte.codePoste = li_quotepart_poste.CP_NUM
        where 1

        $WherePlus

        group by li_quotepart_poste.QUOTEPART_NUM,li_quotepart_poste.CP_NUM

        order by compteposte.ordre

        ";

        $res = Database::query($sql);

        $Return = NULL;

        while ($ln = Database::fetchArray($res)) {
            $Return[$ln["QUOTEPART_NUM"]][$ln["CP_NUM"]] = $ln;
        }

        return $Return;
    }


    static function setMAJBaseMois($DOS_NUM, $BALI_MOIS, $DateMAJ, $Resultat = NULL, $ResProjPrev = false, $ResProjN1 = false, $NbCligno = NULL, $Type = NULL, $ResProjPrevAgip = false)
    {
        $sqlType = '';

        if (!$DateMAJ) {
            $TypeUpdate = ">=";
        } else {
            $TypeUpdate = "=";
        }

        $ResMois = round(dbAcces::getResultatDossier($DOS_NUM, $BALI_MOIS));
        $sqlRes = " BALI_RES = '" . $ResMois . "'";

        if ($Resultat) {
            $sqlRes .= ",BALI_RESPREV = '" . $Resultat["prevu"] . "'";
            $sqlRes .= ",BALI_RESPREVAGIP = '" . $Resultat["prevuagip"] . "'";
        }

        if ($ResProjN1) {
            $sqlRes .= ",BALI_RESPROJN1 = '" . $ResProjN1["montant"] . "'";
        }

        if ($ResProjPrev) {
            $sqlRes .= ", BALI_RESPROJPREV = '" . $ResProjPrev["montant"] . "'";
        }

        if ($ResProjPrevAgip) {
            $sqlRes .= ",BALI_RESPROJPREVAGIP = '" . $ResProjPrevAgip["montant"] . "' ";
        }

        if ($NbCligno) {
            $sqlRes .= ", BALI_NBCLIGNO = '$NbCligno'";
        }

        if ($Type) {
            $sqlType = ", BALI_TYPE = '$Type' ";
        }

        $sql = "update balanceimport set BALI_DATE_MAJBASEAGIP = '$DateMAJ',BALI_DATE_MAJBASE = '$DateMAJ'  $sqlType where DOS_NUM = '$DOS_NUM' and BALI_MOIS $TypeUpdate '$BALI_MOIS'";
        Database::query($sql);

        $sql = "update balanceimport set  $sqlRes  where DOS_NUM = '$DOS_NUM' and BALI_MOIS = '$BALI_MOIS'";
        Database::query($sql);

        require_once '../Anomalie/Anomalie.class.php';

        Anomalie::CompterAnomalies();
    }

    static function getResultatDossier($DOS_NUM, $Mois, $cumul = false, $AnM1 = false, $RestAnM1 = false, $FinAnM1 = false)
    {
        $CondDos1 = " and balance.dos_num = $DOS_NUM ";
        $CondDos2 = "";
        $MonMois = $Mois;

        if ($RestAnM1) {
            $SigneMois = ">";
        } elseif ($cumul) {
            $SigneMois = "<=";
        } else {
            $SigneMois = "=";
        }

        $DateInf = "";

        if ($AnM1) {
            /*$NbMoisEcart = StringHelper::GetNbMoisEcart(date("Ym",strtotime($_SESSION["station_DOS_DEBEX"])),date("Ym",strtotime(str_replace('-00','-01',$Mois))));

            if($_SESSION["station_DOS_PREMDATECP"] != "0000-00-00")
                $MonMois = StringHelper::DatePlus(str_replace("-00","-01",$_SESSION["station_DOS_PREMDATECP"]),array("moisplus"=>$NbMoisEcart,"dateformat"=>"Y-m-00"));
            else
                return;
        */

            /////////////////////////

            $NbMoisEcart = StringHelper::GetNbMoisEcart(date("Ym", strtotime($_SESSION["station_DOS_DEBEX"])), date("Ym", strtotime(str_replace('-00', '-01', $Mois))));

            if ($_SESSION["station_DOS_PREMDATECP"] > 0 && $_SESSION["station_DOS_PREMDATECP"] != "0000-00-00") {
                $MonMois = StringHelper::DatePlus(str_replace("-00", "-01", $_SESSION["station_DOS_PREMDATECP"]), array("moisplus" => $NbMoisEcart, "dateformat" => "Y-m-00"));
            } elseif ($_SESSION["agip_AG_NUM"]) {
                $MonMois = StringHelper::DatePlus($Mois, array("moisplus" => -12, "dateformat" => "Y-m-00"));
            } else {
                return;
            }

            if ($cumul) {
                $DateInf = " and bal_mois >= '" . StringHelper::DatePlus($MonMois, array("moisplus" => -$NbMoisEcart, "dateformat" => "Y-m-00")) . "' ";
            }


            if (!isset($_SESSION["agip_AG_NUM"]) || !$_SESSION["agip_AG_NUM"]) {
                $CondDos1 = " and station.STA_NUM='" . $_SESSION["station_STA_NUM"] . "' ";
            } else {
                $CondDos1 = " and station.LIE_NUM='" . $_SESSION["station_LIE_NUM"] . "' ";
            }

            /////////////////////////

            //$CondDos1 = " and balance.codeStation = ".$_SESSION["station_STA_NUM"]." and balance.BAL_MOIS < '".date("Y-m-00",strtotime($_SESSION["station_DOS_DEBEX"]))."'";
            //$CondDos1 = " and station.STA_NUM = ".$_SESSION["station_STA_NUM"]." ";
        }

        if ($FinAnM1) {

            if ($_SESSION["station_DOS_PREMDATECP"] > 0 && $_SESSION["station_DOS_PREMDATECP"] != "0000-00-00") {
                $DsPrec = dbAcces::getDossier(false, $_SESSION["station_DOS_NUMPREC"]);

                $MonMois = date("Y-m-00", strtotime($DsPrec[0]["DOS_FINEX"]));
            } elseif ($_SESSION["agip_AG_NUM"]) {
                $MonMois = StringHelper::DatePlus($Mois, array("moisplus" => -$NbMoisEcart - 1, "dateformat" => "Y-m-00"));

                $DateInf = " and bal_mois >= '" . StringHelper::DatePlus($MonMois, array("moisplus" => -$_SESSION["station_DOS_NBMOIS"] + 1, "dateformat" => "Y-m-00")) . "' ";
            }

            $SigneMois = "<=";
        }

        $sql = "SELECT sum(-bal_balance) as ProdCharges FROM `balance`
                join comptes on comptes.code_compte = balance.codeCompte 
                join compteposte on comptes.codePoste = compteposte.codePoste
                join dossier on dossier.DOS_NUM = balance.DOS_NUM 
                join station on station.STA_NUM = dossier.STA_NUM
                WHERE 1 $CondDos1
                and bal_mois $SigneMois '$MonMois' $DateInf
                and compteposte.resultat = 1 
                and CAisMarge = 0
                and compteposte.marge = 0";
        //echo $sql;
        $res = Database::query($sql);
        $Montant = Database::fetchArray($res);

        //TraceSql();
        $Marge = dbAcces::GetMarge($DOS_NUM, $Mois, $cumul, $AnM1, $RestAnM1, $FinAnM1);

        return $Montant["ProdCharges"] + $Marge;
    }

    static function getDossier($STA_NUM = NULL, $DOS_NUM = NULL, $LIE_NUM = NULL, $DOS_DEBEX = NULL, $DOS_FINEX = NULL, $Limit = NULL)
    {
        $WherePlus = "";

        if ($STA_NUM) {
            $WherePlus .= " and station.STA_NUM = '$STA_NUM'";
        }

        if ($DOS_NUM) {
            $WherePlus .= " and dossier.DOS_NUM = '$DOS_NUM'";
        }

        if ($LIE_NUM) {
            $WherePlus .= " and lieu.LIE_NUM = '$LIE_NUM'";
        }

        if (!is_array($DOS_DEBEX) && $DOS_DEBEX) {
            $WherePlus .= " and dossier.DOS_DEBEX >= '$DOS_DEBEX'";
        } elseif ($DOS_DEBEX) {
            $WherePlus .= " and dossier.DOS_DEBEX " . $DOS_DEBEX["test"] . " '" . $DOS_DEBEX["DOS_DEBEX"] . "'";
        }

        if (!is_array($DOS_FINEX) && $DOS_FINEX) {
            $WherePlus .= " and dossier.DOS_FINEX <= '$DOS_FINEX'";
        } elseif ($DOS_FINEX) {
            $WherePlus .= " and dossier.DOS_FINEX " . $DOS_FINEX["test"] . " '" . $DOS_FINEX["DOS_FINEX"] . "'";
        }

        $LimitSql = "";

        if ($Limit) {
            $LimitSql = " LIMIT 0,1";
        }

        $sql = "select dossier.*,station.STA_SARL,lieu.* from dossier
        join station on station.STA_NUM = dossier.STA_NUm 
        join lieu on lieu.LIE_NUM = station.LIE_NUM where 1 $WherePlus
        order by dossier.DOS_FINEX DESC $LimitSql";

        $Return = NULL;

        $res = Database::query($sql);

        while ($ln = Database::fetchArray($res)) {
            $Return[] = $ln;
        }

        return $Return;
    }

    static function getMaxDossier($sta_num = null, $joinType = '')
    {
        $where = '';

        if (isset($sta_num) && $sta_num) {
            $where = ' WHERE STA_NUM = ' . $sta_num;
        }

        $sql = "SELECT MAX(dossier.DOS_NUM) as DOS_NUM, STA_NUM
                FROM dossier
                " . $joinType . " JOIN balanceimport ON balanceimport.DOS_NUM = dossier.DOS_NUM
                    AND balanceimport.BALI_DATE_MAJBASE > 0
                " . $where . "
                GROUP BY STA_NUM";

        $return = array();

        $res = Database::query($sql);

        while ($ln = Database::fetchArray($res)) {
            $return[$ln['STA_NUM']] = $ln['DOS_NUM'];
        }

        if (isset($sta_num) && $sta_num) {
            return $return[$sta_num];
        }

        return $return;
    }

    static function GetMarge($DOS_NUM, $Mois, $cumul = false, $AnM1 = false, $RestAnM1 = false, $FinAnM1 = false)
    {
        $CondDos1 = " and balance.dos_num = $DOS_NUM ";
        $CondDos2 = " and dossier.dos_num = $DOS_NUM ";

        if ($FinAnM1) {
            $cumul = true;
        }

        if (!$cumul) {
            if (!$AnM1) {
                $MonMois = $Mois;
            } else {
                $NbMoisEcart = StringHelper::GetNbMoisEcart(date("Ym", strtotime($_SESSION["station_DOS_DEBEX"])), date("Ym", strtotime(str_replace('-00', '-01', $Mois))));

                if ($_SESSION["station_DOS_PREMDATECP"] > 0 && $_SESSION["station_DOS_PREMDATECP"] != "0000-00-00") {
                    $MonMois = StringHelper::DatePlus(str_replace("-00", "-01", $_SESSION["station_DOS_PREMDATECP"]), array("moisplus" => $NbMoisEcart, "dateformat" => "Y-m-00"));
                } elseif ($_SESSION["agip_AG_NUM"]) {
                    $MonMois = StringHelper::DatePlus($Mois, array("moisplus" => -12, "dateformat" => "Y-m-00"));
                } else {
                    return;
                }

                if (!$_SESSION["agip_AG_NUM"]) {
                    $CondDos1 = " and balance.DOS_NUM = '" . $_SESSION["station_DOS_NUMPREC"] . "' ";
                    $CondDos2 = " and dossier.DOS_NUM = '" . $_SESSION["station_DOS_NUMPREC"] . "' ";
                } else {
                    $CondDos1 = " and station.LIE_NUM='" . $_SESSION["station_LIE_NUM"] . "' ";
                    $CondDos2 = " and station.LIE_NUM='" . $_SESSION["station_LIE_NUM"] . "' ";
                }
            }

            $sql = "select sum(ROUND(Marge,2)) as Marge from (
            
            select sum(totaux) as Marge from ( 
                    SELECT sum(bal_balance * (Taux/100)) as totaux from 
                        (SELECT  comptes.CodeCompteAttache,compteposte.codePoste,sum(bal_balance) as bal_balance ,bal_mois 
                            FROM `balance` 
                            join station on station.STA_NUM = balance.codeStation
                            join comptes on comptes.code_compte = balance.codeCompte 
                            join compteposte on comptes.codePoste = compteposte.codePoste  
                            WHERE 1 $CondDos1
                            and comptes.type='vente' 
                            and compteposte.resultat = 1 
                            and compteposte.marge = 1 
                            and bal_mois = '" . $MonMois . "'
                            group by bal_mois,compteposte.codePoste,comptes.code_compte) CA
                    join comptepostedetail on CA.CodeCompteAttache = comptepostedetail.code_compte and comptepostedetail.Mois = CA.bal_mois
                    join dossier on comptepostedetail.DOS_NUM = dossier.DOS_NUM
                    join station on dossier.STA_NUM = station.STA_NUM 
                    WHERE 1 $CondDos2
                    
                    group by comptepostedetail.Mois,CA.codePoste ) as mesmarges
                    
                    UNION ALL
                    
                    select sum(bal_balance) as bal_balance from balance
                    join station on station.STA_NUM = balance.codeStation
                    join comptes on comptes.code_compte = balance.codeCompte 
                            join compteposte on comptes.codePoste = compteposte.codePoste  
                            WHERE 1 $CondDos1
                            and comptes.type='vente' 
                            and compteposte.resultat = 1 
                            and compteposte.CaIsMarge = 1 
                            and bal_mois = '" . $MonMois . "'
                            group by bal_mois,compteposte.codePoste,comptes.code_compte
                    
                    UNION ALL

                                        select sum(bal_balance) as bal_balance from balance
                    join station on station.STA_NUM = balance.codeStation
                    join comptes on comptes.code_compte = balance.codeCompte
                            join compteposte on comptes.codePoste = compteposte.codePoste
                            WHERE 1 $CondDos1
                            and comptes.type='vente'
                            and compteposte.resultat = 1
                            and comptes.MargeDirect = 1
                            and bal_mois = '" . $MonMois . "'
                            group by bal_mois,compteposte.codePoste,comptes.code_compte

                    UNION ALL
                    
                    SELECT -(sum(EcartMarge - EcartMargePrec))  FROM `comptepostedetail` 
                    join dossier on comptepostedetail.DOS_NUM = dossier.DOS_NUM 
                    join station on dossier.STA_NUM = station.STA_NUM 
                    WHERE 1 $CondDos2
                     and Mois = '" . $MonMois . "' and ((EcartMarge is not null and EcartMarge <> 0) or StockFinal > 0 or StockFinalZero > 0) group by Mois
                    ) as marge
                    ";
        } else {

            //= Marge
            //+ Ca qui compte pour marge (Ope forfait)
            //+ ercat sur marge

            //and compteposte.codePoste != 92

            if ($RestAnM1) {
                $SigneMois = ">";
            } else {
                $SigneMois = "<=";
            }

            $SigneMoisEcartMarge = "<=";

            if ($RestAnM1) {
                $SigneMoisEcartMarge = ">";
                die("erreur 1");
            }

            if (!$AnM1) {
                $MonMois = $Mois;
            } else {

                $NbMoisEcart = StringHelper::GetNbMoisEcart(date("Ym", strtotime($_SESSION["station_DOS_DEBEX"])), date("Ym", strtotime(str_replace('-00', '-01', $Mois))));

                if ($_SESSION["station_DOS_PREMDATECP"] > 0 && $_SESSION["station_DOS_PREMDATECP"] != "0000-00-00") {
                    $MonMois = StringHelper::DatePlus(str_replace("-00", "-01", $_SESSION["station_DOS_PREMDATECP"]), array("moisplus" => $NbMoisEcart, "dateformat" => "Y-m-00"));
                } elseif ($_SESSION["agip_AG_NUM"]) {
                    $MonMois = StringHelper::DatePlus($Mois, array("moisplus" => -12, "dateformat" => "Y-m-00"));
                } else {
                    return;
                }

                $DateInf = " and bal_mois >= '" . StringHelper::DatePlus($MonMois, array("moisplus" => -$NbMoisEcart, "dateformat" => "Y-m-00")) . "' ";
                $DateInfEcartMarge = " and mois >= '" . StringHelper::DatePlus($MonMois, array("moisplus" => -$NbMoisEcart, "dateformat" => "Y-m-00")) . "' ";

                if (!isset($_SESSION["agip_AG_NUM"]) || !$_SESSION["agip_AG_NUM"]) {
                    $CondDos1 = " and balance.DOS_NUM = '" . $_SESSION["station_DOS_NUMPREC"] . "' ";
                    $CondDos2 = " and dossier.DOS_NUM = '" . $_SESSION["station_DOS_NUMPREC"] . "' ";
                } else {
                    $CondDos1 = " and station.LIE_NUM='" . $_SESSION["station_LIE_NUM"] . "' ";
                    $CondDos2 = " and station.LIE_NUM='" . $_SESSION["station_LIE_NUM"] . "' ";
                }

                //$CondDos1 = " and balance.codeStation = ".$_SESSION["station_STA_NUM"]." and balance.BAL_MOIS < '".date("Y-m-00",strtotime($_SESSION["station_DOS_DEBEX"]))."'";
                //$CondDos2 = " and dossier.STA_NUM = ".$_SESSION["station_STA_NUM"]." and comptepostedetail.Mois < '".date("Y-m-00",strtotime($_SESSION["station_DOS_DEBEX"]))."'";
            }

            if ($FinAnM1) {
                if ($_SESSION["station_DOS_PREMDATECP"] > 0 && $_SESSION["station_DOS_PREMDATECP"] != "0000-00-00") {
                    $DsPrec = dbAcces::getDossier(false, $_SESSION["station_DOS_NUMPREC"]);

                    $MonMois = date("Y-m-00", strtotime($DsPrec[0]["DOS_FINEX"]));
                } elseif ($_SESSION["agip_AG_NUM"]) {
                    $MonMois = StringHelper::DatePlus($Mois, array("moisplus" => -$NbMoisEcart - 1, "dateformat" => "Y-m-00"));

                    $DateInf = " and bal_mois >= '" . StringHelper::DatePlus($MonMois, array("moisplus" => -$_SESSION["station_DOS_NBMOIS"] + 1, "dateformat" => "Y-m-00")) . "' ";
                    $DateInfEcartMarge = " and bal_mois >= '" . StringHelper::DatePlus($MonMois, array("moisplus" => -$_SESSION["station_DOS_NBMOIS"] + 1, "dateformat" => "Y-m-00")) . "' ";
                }

                $SigneMois = "<=";
            }

            $sql = "select sum(Marge) as Marge,codePoste from (
            
                                        select sum(totaux) as Marge,codePoste from (
                    SELECT sum(ROUND(bal_balance * (Taux/100),2)) as totaux,comptes.codePoste from
                        (SELECT  comptes.CodeCompteAttache,compteposte.codePoste,sum(bal_balance) as bal_balance ,bal_mois 
                            FROM `balance` 
                            join station on station.STA_NUM = balance.codeStation
                            join comptes on comptes.code_compte = balance.codeCompte 
                            join compteposte on comptes.codePoste = compteposte.codePoste  
                            WHERE 1 $CondDos1
                            and comptes.type='vente' 
                            and compteposte.resultat = 1 
                            and compteposte.marge = 1
                                                        and comptes.MargeDirect = 0
                            and bal_mois $SigneMois '" . $MonMois . "' $DateInf
                            group by bal_mois,compteposte.codePoste,comptes.code_compte) CA
                    join comptepostedetail on CA.CodeCompteAttache = comptepostedetail.code_compte and comptepostedetail.Mois = CA.bal_mois
                                        join comptes on comptes.code_compte = comptepostedetail.code_compte
                    join dossier on comptepostedetail.DOS_NUM = dossier.DOS_NUM
                    join station on dossier.STA_NUM = station.STA_NUM
                    WHERE 1 $CondDos2
                    
                    group by comptepostedetail.Mois,CA.codePoste ) as mesmarges
                    
                    UNION ALL
                    
                    select sum(bal_balance) as bal_balance,comptes.codePoste from balance
                    join station on station.STA_NUM = balance.codeStation
                    join comptes on comptes.code_compte = balance.codeCompte 
                            join compteposte on comptes.codePoste = compteposte.codePoste  
                            WHERE 1 $CondDos1
                            and comptes.type='vente' 
                            and compteposte.resultat = 1 
                            and compteposte.CaIsMarge = 1 
                            and bal_mois $SigneMois '" . $MonMois . "' $DateInf
                            group by bal_mois,compteposte.codePoste,comptes.code_compte

                                        UNION ALL

                    select sum(bal_balance) as bal_balance,comptes.codePoste from balance
                    join station on station.STA_NUM = balance.codeStation
                    join comptes on comptes.code_compte = balance.codeCompte
                            join compteposte on comptes.codePoste = compteposte.codePoste
                            WHERE 1 $CondDos1
                            and comptes.type='vente'
                            and compteposte.resultat = 1
                            and comptes.MargeDirect = 1
                                                        and compteposte.CaIsMarge = 0

                            and bal_mois $SigneMois '" . $MonMois . "' $DateInf
                            group by bal_mois,compteposte.codePoste,comptes.code_compte

                    UNION ALL
                    
                    SELECT (-sum(EcartMarge)),comptes.codePoste  FROM `comptepostedetail`
                    join (select max(Mois) as max_mois from comptepostedetail join dossier on comptepostedetail.DOS_NUM = dossier.DOS_NUM  join station on station.STA_NUM = dossier.STA_NUM where 1 $CondDos2  and Mois $SigneMoisEcartMarge '" . $MonMois . "' and ((EcartMarge is not null and EcartMarge <> 0)  or StockFinal > 0 or StockFinalZero > 0) group by dossier.DOS_NUM) as mesmax on comptepostedetail.Mois = mesmax.max_mois
                    join dossier on comptepostedetail.DOS_NUM = dossier.DOS_NUM
                                        join comptes on comptes.code_compte = comptepostedetail.code_compte
                    join station on dossier.STA_NUM = station.STA_NUM 
                    WHERE 1 $CondDos2
                     and Mois = '" . $MonMois . "' $DateInfEcartMarge and ((EcartMarge is not null and EcartMarge <> 0) or StockFinal > 0  or StockFinalZero > 0) group by dossier.DOS_NUM
                     
                    UNION ALL
                    
                    SELECT (-sum(EcartMargePrec)),comptes.codePoste  FROM `comptepostedetail`
                    join (select max(Mois) as max_mois from comptepostedetail join dossier on comptepostedetail.DOS_NUM = dossier.DOS_NUM join station on station.STA_NUM = dossier.STA_NUM where 1 $CondDos2  and Mois $SigneMoisEcartMarge '" . $MonMois . "' and ((EcartMarge is not null and EcartMarge <> 0)  and StockFinal = 0 and StockFinalZero = 0) group by dossier.DOS_NUM) as mesmax on comptepostedetail.Mois = mesmax.max_mois
                    join dossier on comptepostedetail.DOS_NUM = dossier.DOS_NUM
                                        join comptes on comptes.code_compte = comptepostedetail.code_compte
                    join station on dossier.STA_NUM = station.STA_NUM
                    WHERE 1 $CondDos2
                     and Mois $SigneMoisEcartMarge '" . $MonMois . "' $DateInfEcartMarge and (EcartMarge is null or EcartMarge = 0) and StockFinal = 0 and StockFinalZero = 0 group by dossier.DOS_NUM
                     
                    ) as marge 
            ";
        }

        $res = Database::query($sql);
        $Montant = Database::fetchArray($res);
        return -$Montant["Marge"];
    }

    static function getDateMAJBase($DOS_NUM, $BALI_MOIS = null, $AvecDateMaj = 0, $Max = false)
    {
        $WherePlus = '';

        if ($BALI_MOIS) {
            $WherePlus .= " and BALI_MOIS = '$BALI_MOIS' ";
        }

        if ($AvecDateMaj == 1) {
            $WherePlus .= " and BALI_DATE_MAJBASE > 0 ";
        } elseif ($AvecDateMaj == 2) {
            $WherePlus .= " and BALI_DATE_MAJBASE = 0 ";
        }

        $Limit = ($Max) ? " limit 0,1" : "";
        $order = ($Max) ? "DESC" : "";

        $sql = "select BALI_MOIS,BALI_TYPE,BALI_DATE_MAJBASE,BALI_DATE_MAJBASEAGIP
                from balanceimport
                where DOS_NUM = '$DOS_NUM'
                $WherePlus
                order by BALI_MOIS $order
                $Limit";

        Database::query($sql);

        $Return = array();

        while ($ln = Database::fetchArray()) {
            $Return[$ln["BALI_MOIS"]] = $ln;
        }

        if ($BALI_MOIS) {
            if (!isset($Return[$BALI_MOIS])) {
                $Return[$BALI_MOIS] = '';
            }

            return $Return[$BALI_MOIS];
        }

        return $Return;
    }

    static function getResultat($DOS_NUM, $Periode = NULL)
    {
        $where = NULL;

        if ($Periode) {
            $where .= " and BALI_MOIS <= '$Periode'";
        }

        $sql = "select sum(BALI_RES) as BALI_RES,sum(BALI_RESPREV) as BALI_RESPREV,sum(BALI_RESPREVAGIP) as BALI_RESPREVAGIP from balanceimport where DOS_NUM = '$DOS_NUM' $where";

        $res = Database::query($sql);

        if ($ln = Database::fetchArray($res)) {
            return $ln;
        }
    }

    static function getLastBalance($DOS_NUM = null)
    {
        $Where = '';

        if ($DOS_NUM) {
            $Where .= "where DOS_NUM = '$DOS_NUM'";
        }

        $sql = "select MAX(BALI_MOIS) as max from balanceimport " . $Where;

        Database::query($sql);

        if ($ln = Database::fetchArray()) {
            return $ln["max"];
        }

        return '';
    }

    static function getAllLastBalance($DOS_NUM = array())
    {
        $Where = '';

        if (!empty($DOS_NUM)) {
            $Where .= "where DOS_NUM IN ('" . implode(',', $DOS_NUM) . "')";
        }

        $sql = "SELECT DOS_NUM, MAX(BALI_MOIS) AS max 
                FROM balanceimport " . $Where . "
                GROUP BY DOS_NUM";

        Database::query($sql);

        $return = array();

        while ($ln = Database::fetchArray()) {
            $return[$ln['DOS_NUM']] = $ln["max"];
        }

        return $return;
    }

    static function setComImport($DOS_NUM, $BALI_MOIS, $BALI_COM, $pathFileCom = NULL)
    {
        if (!$MaLigneImport = dbAcces::getBalImportM1($DOS_NUM, $BALI_MOIS)) {
            $BALI_MOISM1 = "0000-00-00";

            $sql = "insert into balanceimport set DOS_NUM='$DOS_NUM',BALI_MOIS='$BALI_MOIS',BALI_MOISM1='$BALI_MOISM1', BALI_IMPORT=1 ";

            Database::query($sql);
        }

        if (!is_null($pathFileCom)) //si $pathFileCom existe on update le lien vers le fichier de commentaire
        {
            if ($pathFileCom == "del")//si le mot del apparait il faut supprimer le chemin du fichier
            {
                $sql = "update balanceimport set BALI_FILECOM = '' where DOS_NUM = '$DOS_NUM' and BALI_MOIS='$BALI_MOIS'  ";
            } else {
                $sql = "update balanceimport set BALI_FILECOM = '$pathFileCom' where DOS_NUM = '$DOS_NUM' and BALI_MOIS='$BALI_MOIS' ";
            }
            //echo $sql;
            return Database::query($sql);
        } else {
            $sqlUpd = "update balanceimport set 
                    BALI_COM_CP_DIVERS=\"" . $BALI_COM["BALI_COM_CP_DIVERS"] . "\", 
                    BALI_COM_CP_EXCEP=\"" . $BALI_COM["BALI_COM_CP_EXCEP"] . "\", 
                    BALI_COM_AUTRES_EXCEP=\"" . $BALI_COM["BALI_COM_AUTRES_EXCEP"] . "\" 
                    where DOS_NUM='$DOS_NUM' and BALI_MOIS='$BALI_MOIS'";

            return Database::query($sqlUpd);
        }
    }

    static function InsertBalance($MesCumuls, $Mois, $Correction)
    {
        $MesCodeC = "[";

        foreach ($MesCumuls as $codeCompte => $cumul) {
            $MesCodeC .= "'" . $codeCompte . "',";
        }

        $MesCodeC .= "'0']";

        $sql = "delete from Balance where DOS_NUM='" . $_SESSION["station_DOS_NUM"] . "' and BAL_MOIS= '$Mois'";

        Database::query($sql);

        $sql = "insert into Balance (`BAL_NUM`, `codeStation`,`DOS_NUM`, `codeCompte`, `BAL_MOIS`, `BAL_CUMUL`) VALUES ";
        $prem = true;

        foreach ($MesCumuls as $codeCompte => $MonCumul) {
            if (!$prem) {
                $sql .= ",";
            } else {
                $prem = false;
            }

            $sql .= "(0,'" . $_SESSION["station_STA_NUM"] . "','" . $_SESSION["station_DOS_NUM"] . "','$codeCompte','$Mois','$MonCumul')";
        }

        Database::query($sql);

        if (!$_POST["BALI_TYPE"]) {
            $_POST["BALI_TYPE"] = "BS";
        }

        dbAcces::setBalImport($_SESSION["station_DOS_NUM"], $Mois, station::GetLastBal($_SESSION["station_DOS_NUM"]), $Correction);
        dbAcces::UpdBalanceMois($Mois);//calcul des BAL_MOIS
        dbAcces::setLastBalance($_SESSION["station_STA_NUM"], $Mois, $_SESSION["station_DOS_NUM"]);//MISE A JOUR du champ "derniere balance importŽ"
    }

    static function setBalImport($DOS_NUM, $BALI_MOIS, $BALI_MOISM1, $Correction = false)
    {
        if (!dbAcces::getBalImportM1($DOS_NUM)) {
            $BALI_MOISM1 = "0000-00-00";
        }

        //mise en place du nombre de jour concernŽ par la balance
        if ($BALI_MOIS == date("Y-m-00", strtotime($_SESSION["station_DOS_DEBEX"]))) {
            $BALI_NBJOUR = date("t", strtotime($_SESSION["station_DOS_DEBEX"])) - date("d", strtotime($_SESSION["station_DOS_DEBEX"])) + 1;//nombre de jour sur le mois
        } elseif ($BALI_MOIS == date("Y-m-00", strtotime($_SESSION["station_DOS_FINEX"]))) {
            $BALI_NBJOUR = date("d", strtotime($_SESSION["station_DOS_DEBEX"]));//nombre de jour sur le mois
        } else {
            $BALI_NBJOUR = date("t", strtotime(str_replace("-00", "-01", $BALI_MOIS)));//nombre de jour sur le mois
        }

        if (!dbAcces::getBalImportM1($DOS_NUM, $BALI_MOIS)) {
            $sql = "insert into balanceimport set DOS_NUM='$DOS_NUM',BALI_MOIS='$BALI_MOIS',BALI_MOISM1='$BALI_MOISM1',BALI_TYPE='" . $_POST["BALI_TYPE"] . "',BALI_NBJOUR='$BALI_NBJOUR' ";
        } else {
            $Imp = dbAcces::getBalImportM1($DOS_NUM, $BALI_MOIS);

            $DernNbImp = $Imp[0]["BALI_IMPORT"];
            $DernNbCorrect = $Imp[0]["BALI_CORRECT"];

            if (!$Correction) {
                $DernNbImp++;
            } else {
                $DernNbCorrect++;
            }

            $sql = "update balanceimport set BALI_IMPORT='$DernNbImp',BALI_CORRECT='$DernNbCorrect',BALI_TYPE='" . $_POST["BALI_TYPE"] . "',BALI_NBJOUR='$BALI_NBJOUR' where DOS_NUM='$DOS_NUM' and BALI_MOIS='$BALI_MOIS'";
        }

        Database::query($sql);
    }

    static function UpdBalanceMois($Mois)
    {
        $MesResultats = dbAcces::getResultatsCompte($Mois);
        $MesCumulsM = NULL;

        foreach ($MesResultats as $codeCompte => $UnResultat) {
            $MesCumulsM[$UnResultat["codeCompte"]]["BAL_CUMUL"] = $UnResultat['BAL_CUMUL'];
            $MesCumulsM[$UnResultat["codeCompte"]]["BAL_NUM"] = $UnResultat['BAL_NUM'];
        }

        $MoisM1 = dbAcces::getBalImportM1($_SESSION["station_DOS_NUM"], $Mois);

        $MesResultatsMoin1 = dbAcces::getResultatsCompte($MoisM1[0]["BALI_MOISM1"]);

        $MesCumulsMMoins1 = NULL;

        foreach ($MesResultatsMoin1 as $codeCompte => $UnResultat) {
            $MesCumulsMMoins1[$UnResultat["codeCompte"]]["BAL_CUMUL"] = $UnResultat['BAL_CUMUL'];
        }

        foreach ($MesCumulsM as $codeCompte => $UnCumul) {
            $Montant = 0;
            $Montant = $UnCumul["BAL_CUMUL"] - ((double)$MesCumulsMMoins1[$codeCompte]["BAL_CUMUL"]);

            $sql = "";
            $sql = "update Balance set BAL_BALANCE = '$Montant' where DOS_NUM='" . $_SESSION["station_DOS_NUM"] . "' and BAL_NUM = '" . $UnCumul["BAL_NUM"] . "'";

            Database::query($sql);
            /*echo $UnCumul["BAL_CUMUL"];
            echo "<br/>";
            echo $UnCumul["BAL_NUM"]."->".$Montant;
            echo "<br/>";*/
        }

        $sql = "delete from Balance where DOS_NUM='" . $_SESSION["station_DOS_NUM"] . "' and BAL_MOIS= '$Mois' and BAL_BALANCE = 0 and BAL_CUMUL = 0";

        Database::query($sql);
    }

    static function getResultatsCompte($Mois, $MoisMoins1 = false, $AnneeMoins1 = false, $codeCompte = NULL, $TypeCpt = NULL, $RRRO = NULL, $CodeCompteAttache = NULL, $VarStock = NULL, $Stock = NULL, $numero = NULL, $CumulAnm1 = false, $Cluster = false)
    {
        $WherePlus = $selectCompteAchat = "";
        $indexKey = "codeCompte";

        if ($codeCompte) {
            $WherePlus .= "and balance.codeCompte = $codeCompte";
        }

        if ($TypeCpt) {
            $WherePlus .= " and comptes.Type " . $TypeCpt["test"] . " '" . $TypeCpt["type"] . "' ";
        }

        if ($RRRO) {
            if (is_array($RRRO["RRRO"])) {
                $selectCompteAchat = ', RRRO';
                $indexKey = 'RRRO';
                $RRRO["RRRO"] = " (" . implode(',', $RRRO["RRRO"]) . ") ";
            } else {
                $RRRO["RRRO"] = " '" . $RRRO["RRRO"] . "' ";
            }

            $WherePlus .= " and comptes.RRRO " . $RRRO["test"] . $RRRO["RRRO"];
        }

        if ($VarStock) {
            $WherePlus .= " and comptes.VarStock " . $VarStock["test"] . " '" . $VarStock["VarStock"] . "' ";
        }

        if ($Stock) {
            $WherePlus .= " and comptes.Stock " . $Stock["test"] . " '" . $Stock["Stock"] . "' ";
        }

        //compte vente attaché au compte achat
        if ($CodeCompteAttache) {
            if (is_array($CodeCompteAttache)) {
                $selectCompteAchat = ', CodeCompteAttache';
                $indexKey = 'CodeCompteAttache';
                $compteAttache = " IN (" . implode(',', $CodeCompteAttache) . ") ";
            } else {
                $compteAttache = " = '" . $CodeCompteAttache . "' ";
            }

            $WherePlus .= " and comptes.CodeCompteAttache " . $compteAttache;
        }

        if ($numero) {
            $WherePlus .= " and comptes.numero " . $numero["test"] . " '" . $numero["numero"] . "' ";
        }

        $Dos = "  and codeStation='" . $_SESSION["station_STA_NUM"] . "' ";//dos_num = $_session dos_num
        $GroupBy = "group by 1";

        if ($Cluster) {
            $Dos = " and station.STA_NUM_CLUSTER = '" . $_SESSION["station_STA_NUM_CLUSTER"] . "'";
            $GroupBy = "group by station.STA_NUM_CLUSTER";
        }

        if ($AnneeMoins1) {
            if (!is_array($AnneeMoins1)) {
                $NbMoisEcart = StringHelper::GetNbMoisEcart(date("Ym", strtotime($_SESSION["station_DOS_DEBEX"])), date("Ym", strtotime(str_replace('-00', '-01', $Mois))));

                if ($_SESSION["station_DOS_PREMDATECP"] > 0 && $_SESSION["station_DOS_PREMDATECP"] != "0000-00-00") {
                    $Mois = StringHelper::DatePlus(str_replace("-00", "-01", $_SESSION["station_DOS_PREMDATECP"]), array("moisplus" => $NbMoisEcart, "dateformat" => "Y-m-00"));
                } elseif ($_SESSION["agip_AG_NUM"]) {/////Ajout
                    $Mois = StringHelper::DatePlus($Mois, array("moisplus" => -12, "dateformat" => "Y-m-00"));
                } else {
                    return array();
                }

                if ($CumulAnm1) {
                    $DateInf = StringHelper::DatePlus($Mois, array("moisplus" => -$NbMoisEcart, "dateformat" => "Y-m-00"));
                } else {
                    $DateInf = $Mois;
                }
            }

            $sql = "select balance.codeCompte,sum(balance.BAL_BALANCE) as BAL_CUMUL,req1.BAL_BALANCE as BAL_BALANCE ,'UMois',BAL_NUM from Balance
                    join station on station.STA_NUM = balance.codeSTation
                    join comptes on comptes.code_compte = Balance.codeCompte
                    join (select codeCompte,BAL_BALANCE from Balance join comptes on code_compte = Balance.codeCompte join station on station.STA_NUM = balance.codeSTation
                where BAL_MOIS = '$Mois' $Dos $WherePlus ) as req1 on req1.codeCompte = comptes.code_compte
                where BAL_MOIS <= '$Mois' and BAL_MOIS >= '" . $DateInf . "' $Dos $WherePlus $GroupBy ";
        } else {
            if (!is_array($Mois)) {
                $WherePlus2 = " and BAL_MOIS = '$Mois' and dossier.DOS_NUM='" . $_SESSION["station_DOS_NUM"] . "' ";
            } //c'est pour avoir le cumul
            else {

                $WherePlus2 = " and BAL_MOIS <= '" . $Mois["Mois"] . "' and dossier.DOS_NUM = '" . $Mois["DOS_NUM"] . "'";
            }

            $sql = "select codeCompte,sum(BAL_CUMUL) as BAL_CUMUL,sum(BAL_BALANCE) as BAL_BALANCE,'UMois',BAL_NUM $selectCompteAchat
                    from Balance
                    join comptes on comptes.code_compte = Balance.codeCompte
                    join dossier on dossier.DOS_NUM = balance.DOS_NUM
                    join station on station.STA_NUM = dossier.STA_NUM
                    where 1 $Dos $WherePlus $WherePlus2 $GroupBy";
        }

        if ($MoisMoins1) {
            $MoisMoins1 = dbAcces::getBalImportM1($_SESSION["station_DOS_NUM"], $Mois);
            $MoisMoins1 = $MoisMoins1[0]["BALI_MOISM1"];

            $sql .= " UNION ALL select codeCompte,BAL_CUMUL,BAL_BALANCE,'UMoisMoins1',BAL_NUM from Balance
            join comptes on comptes.code_compte = Balance.codeCompte
            join dossier on dossier.DOS_NUM = balance.DOS_NUM
            join station on station.STA_NUM = dossier.STA_NUM
            where BAL_MOIS = '$MoisMoins1' and dossier.DOS_NUM='" . $_SESSION["station_DOS_NUM"] . "' $WherePlus ";
        }

        Database::query($sql);

        $Return = array();

        while ($ln = Database::fetchArray()) {
            $Return[$ln[$indexKey] . $ln["UMois"]] = $ln;
        }

        return $Return;
    }

    static function setLastBalance($STA_NUM, $Date, $DOS_NUM = NULL)
    {
        $Sqlverif = "";

        if ($DOS_NUM) {
            $Sqlverif = "select max(DOS_NUM) as DOS_NUM from dossier where STA_NUM = $STA_NUM";

            $res = Database::query($Sqlverif);
            $ln = Database::fetchArray($res);

            if ($ln["DOS_NUM"] != $DOS_NUM) {
                return;
            }
        }

        $sql = "update station set STA_DERNBAL='$Date' where STA_NUM='$STA_NUM'";

        if (Database::query($sql)) {
            $_SESSION["station_STA_DERNBAL"] = $Date;
        }
    }

    static function getComptes($Where = null, $Tri = null, $WhereSpec = null, $CA = false, $Value = false)
    {
        $Join = "";
        $Where = dbAcces::formatWhere($Where);

        if (!$Where) {
            $Where = " where 1 ";
        }

        if (!$Tri) {
            $Tri = " order by comptes.numero ASC";
        } else {
            $Tri = dbAcces::formatTri($Tri);
        }

        if ($CA) {//que les comptes de CA
            $Join .= " join compteposte on comptes.codePoste = compteposte.codePoste  ";
            $Where .= " and compteposte.MARGE > 0 and compteposte.Type='Produits' and (comptes.Type='achat') and RRRO <= 0 ";
        }

        $Where .= " and CPT_VISIBLE = 1 ";//compte visible

        if ($Value && $_SESSION["station_DOS_NUM"]) {
            $Join .= " join balance on balance.codeCompte = comptes.code_compte and balance.DOS_NUM = '" . $_SESSION["station_DOS_NUM"] . "'";
        }

        $sql = "select distinct comptes.code_compte,comptes.numero,comptes.libelle,comptes.codePoste,comptes.Sens,CPT_TRANSFERT_NON_BAIE from comptes $Join $Where $Tri ";

        Database::query($sql);

        $Return = array();

        while ($ln = Database::fetchArray()) {
            $Return[$ln["code_compte"]] = $ln;
        }

        return $Return;
    }

    static public function formatWhere($Where)
    {
        if (!is_array($Where)) {
            return false;
        }

        if (count($Where) == 0) {
            return "";
        }

        $Return = " WHERE 1 and ";
        $Prem = true;

        foreach ($Where as $Operateur => $UnWhere) {
            if ($Prem) {
                $Return .= " (";
                $Prem = false;
            } else {
                $Return .= " and (";
            }

            $Prem1 = true;

            foreach ($UnWhere as $champ => $valeur) {
                if ($Prem1) {
                    $Prem1 = false;
                } else {
                    $Return .= " $Operateur ";
                }

                if (!is_array($valeur)) {
                    $Return .= " " . $champ . " " . $valeur;
                } else {
                    $Prem2 = true;

                    foreach ($valeur as $UneVal) {
                        if ($Prem2) {
                            $Prem2 = false;
                        } else {
                            $Return .= " $Operateur ";
                        }

                        $Return .= " " . $champ . " " . $UneVal;
                    }
                }
            }

            $Return .= ")";
        }

        return $Return;
    }

    static public function formatTri($Tri)
    {
        if (!is_array($Tri)) {
            return false;
        }

        if (count($Tri) == 0) {
            return "";
        }

        $Return = " order by ";

        $Prem = true;
        $Virg = "";

        foreach ($Tri as $Champ => $sens) {
            if ($Prem) {
                $Prem = false;
            } else {
                $Virg = ",";
            }

            $Return .= "$Virg $Champ $sens";
        }

        return $Return;
    }

    static function getResultatsCompteLieu($d)
    {
        $WherePlus = "";

        if ($d["BAL_MOIS_DEB"]) {
            $WherePlus .= " and BAL_MOIS >= '" . $d["BAL_MOIS_DEB"] . "'";
        }

        if ($d["BAL_MOIS_FIN"]) {
            $WherePlus .= " and BAL_MOIS <= '" . $d["BAL_MOIS_FIN"] . "'";
        }

        if ($d["LIE_NUM"]) {
            $WherePlus .= " and station.LIE_NUM = " . $d["LIE_NUM"];
        }

        if ($d["codePoste"]) {
            $WherePlus .= " and comptes.codePoste = " . $d["codePoste"];
        }

        $sql = "
        select comptes.code_compte, sum(BAL_BALANCE) as BAL_BALANCE
        from balance
        join comptes on comptes.code_compte = balance.codeCompte
        join dossier on dossier.DOS_NUM = balance.DOS_NUM
        join station on station.STA_NUM = dossier.STA_NUM
        where 1

        $WherePlus

        group by comptes.code_compte
    ";

        $res = Database::query($sql);

        $Return = array();

        while ($ln = Database::fetchArray($res)) {
            $Return[$ln["code_compte"]] = $ln;
        }

        return $Return;
    }

    static function getPosteSimple($d)
    {
        $WherePlus = "";
        $JoinPlus = "";

        if ($d["Famille"]) {
            $WherePlus .= " and compteposte.Famille = '" . $d["Famille"] . "' ";
        }

        if ($d["SsFamille"]) {
            $WherePlus .= " and compteposte.SsFamille = '" . $d["SsFamille"] . "' ";
        }

        if ($d["comptePoste_LOYER"]) {
            $WherePlus .= " and compteposte.comptePoste_LOYER = '" . $d["comptePoste_LOYER"] . "' ";
        }

//        if ($d["SCP_NUM"]) {
//            $JoinPlus .= " join LI_POSTESHELL_POSTEAVIA on LI_POSTESHELL_POSTEAVIA.CP_NUM = compteposte.codePoste ";
//            $WherePlus .= " AND LI_POSTESHELL_POSTEAVIA.SCP_NUM = '" . $d["SCP_NUM"] . "'";
//        }

        $sql = "
        select * from compteposte
            $JoinPlus
        where 1
        $WherePlus
        ";

        $res = Database::query($sql);

        $Return = NULL;

        while ($ln = Database::fetchArray($res)) {
            $Return[$ln["codePoste"]] = $ln;
        }

        return $Return;
    }

    /**
     * Récupération des postes. Permet de récupérer les postes suivant la saisie des ONFR dans le prévisionnel
     */
    static function getPoste($Where = false, $Tri = false)
    {
        $Where = dbAcces::formatWhere($Where);
        $Tri = dbAcces::formatTri($Tri);

        $sql = "select compteposte.* from compteposte $Where $Tri";

        Database::query($sql);

        $Return = array();

        while ($ln = Database::fetchArray()) {
            $Return[$ln["codePoste"]] = $ln;
        }

        return $Return;
    }

    static function getPosteVisible($Where = false, $Tri = false)
    {
        $Where = dbAcces::formatWhere($Where);
        $Tri = dbAcces::formatTri($Tri);

        $sql = "select compteposte.*, SUM(comptes.CPT_VISIBLE) as visible
                from compteposte
                LEFT JOIN comptes USING (codePoste)" . $Where
                . " GROUP BY compteposte.codePoste HAVING visible > 0"
                . $Tri . ";";

        Database::query($sql);

        $Return = array();

        while ($ln = Database::fetchArray()) {
            $Return[$ln["codePoste"]] = $ln;
        }

        return $Return;
    }

    static function getPosteSynthese($Where = NULL, $Tri = NULL, $WhereSpec = NULL, $cluster = false)
    {
        $Where = dbAcces::formatWhere($Where);

        $Tri = dbAcces::formatTri($Tri);


        $sql = "
                    select * from compteposte_synthese
                    $Where
                    and Famille != 'ONFR'
                    UNION ALL
                    select
                    distinct compteposte_synthese.*
                    from compteposte_synthese
                    #join dossier on dossier.DOS_NUM = shell_poste_transfert.DOS_NUM
                    #join station on station.STA_NUM = dossier.STA_NUM
                    $Where $Tri
        ";

        $res = Database::query($sql);

        $Return = null;

        while ($ln = Database::fetchArray()) {
            $Return[$ln["codePoste_synthese"]] = $ln;
        }

        return $Return;
    }

    static function getPosteSyntheseVisible($Where = NULL, $Tri = NULL, $WhereSpec = NULL, $cluster = false)
    {
        $Where = dbAcces::formatWhere($Where);

        $Tri = dbAcces::formatTri($Tri);

        $sql = "
                    select compteposte_synthese.*, SUM(comptes.CPT_VISIBLE) as visible
                    from compteposte_synthese
                    LEFT JOIN comptes USING (codePoste_synthese)" . $Where
                . " GROUP BY compteposte_synthese.codePoste_synthese HAVING visible > 0"
                . $Tri . ";";

        $res = Database::query($sql);

        $Return = null;

        while ($ln = Database::fetchArray()) {
            $Return[$ln["codePoste_synthese"]] = $ln;
        }

        return $Return;
    }

    static function getPosteByPosteSynthese()
    {
        $sql = "
                    select DISTINCT compteposte_synthese.codePoste_synthese,compteposte.codePoste from compteposte
                    join comptes on comptes.codePoste = compteposte.codePoste
                    join compteposte_synthese on compteposte_synthese.codeposte_synthese = comptes.codePoste_synthese
                    ";

        $res = Database::query($sql);

        $Return = NULL;

        while ($ln = Database::fetchArray($res)) {
            $Return[$ln["codePoste_synthese"]][$ln["codePoste"]] = $ln;
        }

        return $Return;
    }

    static function getFamilleSFamillePoste($codePoste = false, $Type = null, $Marge = false, $NotMarge = false)
    {
        $wherePlus = "";

        if ($codePoste) {
            $wherePlus .= " and codePoste = '$codePoste' ";
        }

        if ($Type) {
            $wherePlus .= " and Type = '$Type' ";
        }

        if ($Marge) {
            $wherePlus .= " and (Marge = '1' or CaIsMarge = '1')  ";
        }

        if ($NotMarge) {
            $wherePlus .= " and (Marge = '0') and CaIsMarge = '0'";
        }

        $sql = "
            select compteposte.*,CP_NUM_QUOTEPART from
            compteposte
            left join li_quotepart_poste on li_quotepart_poste.CP_NUM_QUOTEPART = compteposte.`codePoste`
            where 1
        $wherePlus
            order by ordre
        ";

        Database::query($sql);

        $Return = array();

        while ($ln = Database::fetchArray()) {
            $Return[$ln["codePoste"]] = $ln;
        }

        return $Return;
    }

    static function getFamilleSFamilleSynthese($codePoste_synthese = false, $Type = NULL, $Marge = false, $NotMarge = false)
    {
        $wherePlus = "";

        if ($codePoste_synthese) {
            $wherePlus .= " and codePoste_synthese = '$codePoste_synthese' ";
        }

        if ($Type) {
            $wherePlus .= " and Type = '$Type' ";
        }

        if ($Marge) {
            $wherePlus .= " and (Marge = '1' or CaIsMarge = '1')  ";
        }

        if ($NotMarge) {
            $wherePlus .= " and (Marge = '0' or codePoste = 92) and CaIsMarge = '0'";
        }

        $sql = "select * from compteposte_synthese where 1 $wherePlus order by ordre";

        $res = Database::query($sql);

        $Return = NULL;

        while ($ln = Database::fetchArray($res)) {
            $Return[$ln["codePoste_synthese"]] = $ln;
        }

        return $Return;
    }

    static function getResultatsPoste($Mois, $Realise = false, $Prevu = false, $AnneeMoins1 = false, $Cumul = false, $codePoste = NULL, $TypeCptP = NULL, $TypeCpt = NULL, $Famille = NULL, $SsFamille = NULL, $AjoutPrevuAgip = NULL, &$Contenu, $cluster)
    {
        $WherePlus = $WherePlus1 ="";

        if ($codePoste) {
            $WherePlus .= " and compteposte.codePoste = $codePoste ";
        }

        if ($TypeCptP) {
            $WherePlus .= " and compteposte.Type = '$TypeCptP' ";
        }

        if ($TypeCpt) {
            $WherePlus1 .= " and comptes.Type " . $TypeCpt["test"] . " '" . $TypeCpt["type"] . "' ";
        }

        if ($Famille) {
            $WherePlus .= " and compteposte.Famille = '" . $Famille . "' ";
        }

        if ($SsFamille) {
            $WherePlus .= " and compteposte.SsFamille = '" . $SsFamille . "' ";
        }

        $ConDos = " and dossier.DOS_NUM='" . $_SESSION["station_DOS_NUM"] . "' ";

        if ($cluster) {
            $ConDos = " and station.STA_NUM_CLUSTER ='" . $_SESSION["station_STA_NUM_CLUSTER"] . "' ";
        }

        $DebEx = date("Y-m-00", strtotime($_SESSION["station_DOS_DEBEX"]));

        $WherePlusPrev = " and PrevAgip = 0 ";
        $sql = $UNION = "";

        $MoisIsArray = false;

        if ($Realise) {
            if (is_array($Mois)) {
                $MaDateDeb = $Mois["debut"];
                $Mois = $Mois["fin"];
                $MoisIsArray = true;
            } elseif ($Cumul) {
                $MaDateDeb = $DebEx;
            } else {
                $MaDateDeb = $Mois;
            }

            $sql .= "select compteposte.codePoste,sum(BAL_BALANCE) as Montant,Famille,SsFamille,BAL_MOIS,'UMoisRealise'
                    from Balance
                    join dossier on dossier.DOS_NUM = balance.DOS_NUM
                    join station on station.STA_NUM = dossier.STA_NUM
                    join comptes on comptes.code_compte = Balance.codeCompte
                    join compteposte on compteposte.codePoste = comptes.codePoste
                    where 1
                    and BAL_MOIS <= '$Mois' and BAL_MOIS >= '$MaDateDeb'
                    $ConDos $WherePlus $WherePlus1
                    group by  dossier.DOS_NUM, balance.BAL_MOIS, compteposte.codePoste";
        }

        if ($Prevu) {
            if ($Realise) {
                $UNION = " UNION ALL ";
            }

            if (!$Cumul) {
                $MaPeriodePrev = " and Periode = '$Mois' ";
            } else {
                $MaPeriodePrev = " and Periode <= '$Mois' and Periode >= '$DebEx' ";
            }

            $sql .= " $UNION
                select resultatposte.codePoste,sum(Montant) as Montant,Famille,SsFamille,Periode,'UMoisPrevu' from resultatposte
                join compteposte on compteposte.codePoste = resultatposte.codePoste
                join dossier on dossier.DOS_NUM = resultatposte.DOS_NUM
                join station on station.STA_NUM = dossier.STA_NUM
                where 1 $MaPeriodePrev $ConDos $WherePlus $WherePlusPrev group by dossier.DOS_NUM,resultatposte.codePoste";
        }

        if ($AnneeMoins1) {
            if ($Realise || $Prevu) {
                $UNION = " UNION ALL ";
            }

            $NbMoisEcart = StringHelper::GetNbMoisEcart(
                date("Ym", strtotime($_SESSION["station_DOS_DEBEX"])),
                date("Ym", strtotime(str_replace('-00', '-01', $Mois)))
            );

            if ($_SESSION["station_DOS_PREMDATECP"] > 0 && $_SESSION["station_DOS_PREMDATECP"] != "0000-00-00") {
                $AnneeMoins1 = StringHelper::DatePlus(str_replace("-00", "-01", $_SESSION["station_DOS_PREMDATECP"]), array("moisplus" => $NbMoisEcart, "dateformat" => "Y-m-00"));
            } elseif ($_SESSION["agip_AG_NUM"]) {
                $AnneeMoins1 = StringHelper::DatePlus($Mois, array("moisplus" => -12, "dateformat" => "Y-m-00"));
            }

            if ($Cumul) {
                $DateInf = StringHelper::DatePlus($AnneeMoins1, array("moisplus" => -$NbMoisEcart, "dateformat" => "Y-m-00"));
            } else {
                $DateInf = $AnneeMoins1;
            }

            ////////////////////////

            if (!isset($_SESSION["agip_AG_NUM"]) || !$_SESSION["agip_AG_NUM"]) {
                $condStation = " and station.STA_NUM='" . $_SESSION["station_STA_NUM"] . "' ";
            } else {
                $condStation = " and station.LIE_NUM='" . $_SESSION["station_LIE_NUM"] . "' ";
            }

            if ($cluster) {
                $condStation = $ConDos;
            }

            $sql .= "$UNION
                select compteposte.codePoste,sum(BAL_BALANCE) as Montant, Famille,SsFamille,BAL_MOIS,'UMoisAnneeMoinsUn'
                from Balance
                join dossier on dossier.DOS_NUM = balance.DOS_NUM
                join station on station.STA_NUM = dossier.STA_NUM
                join comptes on comptes.code_compte = Balance.codeCompte
                join compteposte on compteposte.codePoste = comptes.codePoste
                where 1
                and BAL_MOIS <= '$AnneeMoins1' and BAL_MOIS >= '" . $DateInf . "'
                $condStation $WherePlus $WherePlus1 group by dossier.DOS_NUM,compteposte.codePoste";
        }

        Database::query($sql);

        $Return = array();

        if ($Realise) {
            $text = "UMoisRealise";
        } elseif ($Prevu) {
            $text = "UMoisPrevu";
        } elseif ($AjoutPrevuAgip) {
            $text = "UMoisPrevuAgip";
        } elseif ($AnneeMoins1) {
            $text = "UMoisAnneeMoinsUn";
        }

        $MyReturnPlageMois = array();

        while ($ln = Database::fetchArray()) {

            $Contenu[$ln[$text]] = true;

            $ln["Montant"] = round($ln["Montant"]);

            if (!$MoisIsArray) {
                if (isset($Return[$ln["codePoste"] . "||#||" . $ln[$text]]) && $Return[$ln["codePoste"] . "||#||" . $ln[$text]]) {
                    $Return[$ln["codePoste"] . "||#||" . $ln[$text]]["Montant"] += $ln["Montant"];
                } else {
                    $Return[$ln["codePoste"] . "||#||" . $ln[$text]] = $ln;
                }
            } else {
                if ($MyReturnPlageMois[$ln["BAL_MOIS"]][$ln["codePoste"] . "||#||" . $ln[$text]]) {
                    $MyReturnPlageMois[$ln["BAL_MOIS"]][$ln["codePoste"] . "||#||" . $ln[$text]]["Montant"] += $ln["Montant"];
                } else {
                    $MyReturnPlageMois[$ln["BAL_MOIS"]][$ln["codePoste"] . "||#||" . $ln[$text]] = $ln;
                }
            }
        }

        if ($MyReturnPlageMois) {
            return $MyReturnPlageMois;
        }

        return $Return;
    }

    static function getResultatsPoste_synthese($Mois, $Realise = false, $Prevu = false, $AnneeMoins1 = false, $Cumul = false, $codePoste_synthese = NULL, $TypeCptP = NULL, $TypeCpt = NULL, $Famille = NULL, $SsFamille = NULL, $AjoutPrevuAgip = NULL, &$Contenu, $cluster)
    {
        $WherePlus = "";
        $WherePlus1 = "  ";

        if ($codePoste_synthese) {
            $WherePlus .= " and compteposte_synthese.codePoste_synthese = $codePoste_synthese ";
        }

        if ($TypeCptP) {
            $WherePlus .= " and compteposte_synthese.Type = '$TypeCptP' ";
        }

        if ($TypeCpt) {
            $WherePlus1 .= " and comptes.Type " . $TypeCpt["test"] . " '" . $TypeCpt["type"] . "' ";
        }

        if ($Famille) {
            $WherePlus = " and compteposte_synthese.Famille = '" . $Famille . "' ";
        }

        if ($SsFamille) {
            $WherePlus = " and compteposte_synthese.SsFamille = '" . $SsFamille . "' ";
        }

        $ConDos = " and dossier.DOS_NUM='" . $_SESSION["station_DOS_NUM"] . "' ";

        if ($cluster) {
            $ConDos = " and station.STA_NUM_CLUSTER ='" . $_SESSION["station_STA_NUM_CLUSTER"] . "' ";
        }

        $WherePlus1 .= " and comptes.MargeDirect = 0 ";

        $DebEx = date("Y-m-00", strtotime($_SESSION["station_DOS_DEBEX"]));
        $WherePlusPrev = " and PrevAgip = 0 ";
        $sql = $UNION = "";


        if ($Realise) {
            $DateIfRg = $Mois;

            if ($Cumul) {
                $DateIfRg = $DebEx;
            }

            $sql .= "select compteposte_synthese.codePoste_synthese,sum(BAL_BALANCE) as Montant,compteposte_synthese.Famille,compteposte_synthese.SsFamille,'UMoisRealise'
                    from Balance
                    join dossier on dossier.DOS_NUM = balance.DOS_NUM
                    join station on station.STA_NUM = dossier.STA_NUM
                    join comptes on comptes.code_compte = Balance.codeCompte
                    join compteposte_synthese on compteposte_synthese.codePoste_synthese = comptes.codePoste_synthese
                    join compteposte on compteposte.codePoste = comptes.codePoste
                    where 1
                    and comptes.CPT_VISIBLE = 1
                    and BAL_MOIS <= '$Mois' and BAL_MOIS >= '$DateIfRg'
                    $ConDos $WherePlus $WherePlus1
                    group by dossier.DOS_NUM, compteposte_synthese.codePoste_synthese";
        }

        if ($Prevu) {
            if ($Realise) {
                $UNION = " UNION ALL ";
            }

            if ($Cumul) {
                $MaPeriodePrev = " AND Periode <= '$Mois' AND Periode >= '$DebEx' ";
            } else {
                $MaPeriodePrev = " AND Periode = '$Mois' ";
            }

            $sql .= "$UNION
                    SELECT compteposte_synthese.codePoste_synthese, SUM( Montant ) , compteposte_synthese.Famille, compteposte_synthese.SsFamille,  'UMoisPrevu'
                    FROM resultatposte
                    join dossier on dossier.DOS_NUM = resultatposte.DOS_NUM
                    join station on station.STA_NUM = dossier.STA_NUM
                    JOIN compteposte on compteposte.codePoste = resultatposte.codePoste
                    join (SELECT DISTINCT comptes.codePoste, comptes.CodePoste_synthese FROM comptes) comptes on comptes.codePoste = compteposte.codePoste 
                    JOIN compteposte_synthese ON compteposte_synthese.codePoste_synthese = comptes.codePoste_synthese
                    where 1 $MaPeriodePrev $ConDos $WherePlus $WherePlusPrev
                    GROUP BY compteposte_synthese.codePoste_synthese";
        }

        if ($AnneeMoins1) {
            if ($Realise || $Prevu) {
                $UNION = " UNION ALL ";
            }

            $NbMoisEcart = StringHelper::GetNbMoisEcart(
                date("Ym", strtotime($_SESSION["station_DOS_DEBEX"])),
                date("Ym", strtotime(str_replace('-00', '-01', $Mois)))
            );

            if ($_SESSION["station_DOS_PREMDATECP"] > 0 && $_SESSION["station_DOS_PREMDATECP"] != "0000-00-00") {
                $AnneeMoins1 = StringHelper::DatePlus(str_replace("-00", "-01", $_SESSION["station_DOS_PREMDATECP"]), array("moisplus" => $NbMoisEcart, "dateformat" => "Y-m-00"));
            } elseif ($_SESSION["agip_AG_NUM"]) {
                $AnneeMoins1 = StringHelper::DatePlus($Mois, array("moisplus" => -12, "dateformat" => "Y-m-00"));
            }

            if ($Cumul) {
                $DateInf = StringHelper::DatePlus($AnneeMoins1, array("moisplus" => -$NbMoisEcart, "dateformat" => "Y-m-00"));
            } else {
                $DateInf = $AnneeMoins1;
            }

            ////////////////////////

            if (!isset($_SESSION["agip_AG_NUM"]) || !$_SESSION["agip_AG_NUM"]) {
                $condStation = " and station.STA_NUM='" . $_SESSION["station_STA_NUM"] . "' ";
            } else {
                $condStation = " and station.LIE_NUM='" . $_SESSION["station_LIE_NUM"] . "' ";
            }

            if ($cluster) {
                $condStation = $ConDos;
            }

            $sql .= " $UNION
                        select compteposte_synthese.codePoste_synthese,sum(BAL_BALANCE) as Montant,compteposte_synthese.Famille,compteposte_synthese.SsFamille,'UMoisAnneeMoinsUn'
                        from Balance
                        join dossier on dossier.DOS_NUM = balance.DOS_NUM
                        join station on station.STA_NUM = dossier.STA_NUM
                        join comptes on comptes.code_compte = Balance.codeCompte
                        join compteposte_synthese on compteposte_synthese.codePoste_synthese = comptes.codePoste_synthese
                        join compteposte on compteposte.codePoste = comptes.codePoste
                        where 1 and CPT_VISIBLE = 1 and BAL_MOIS <= '$AnneeMoins1' and BAL_MOIS >= '" . $DateInf . "'
                        $condStation $WherePlus $WherePlus1
                        group by dossier.DOS_NUM,compteposte_synthese.codePoste_synthese";
        }

        Database::query($sql);

        $Return = array();

        if ($Realise) {
            $text = "UMoisRealise";
        } elseif ($Prevu) {
            $text = "UMoisPrevu";
        } elseif ($AjoutPrevuAgip) {
            $text = "UMoisPrevuAgip";
        } elseif ($AnneeMoins1) {
            $text = "UMoisAnneeMoinsUn";
        }

        while ($ln = Database::fetchArray()) {
            $Contenu[$ln[$text]] = true;

            $ln["Montant"] = round($ln["Montant"]);

            if (isset($Return[$ln["codePoste_synthese"] . "||#||" . $ln[$text]]) && $Return[$ln["codePoste_synthese"] . "||#||" . $ln[$text]]) {//!!!IMPORTANT
                $Return[$ln["codePoste_synthese"] . "||#||" . $ln[$text]]["Montant"] += $ln["Montant"];
            } else {
                $Return[$ln["codePoste_synthese"] . "||#||" . $ln[$text]] = $ln;
            }
        }

        return $Return;
    }

    static function getLiaisonBalPoste($codePoste, $Type = null, $RRRO = false, $codePoste_synthese = null)
    {
        $sqlPlus = "";
        $JoinPlus = "";

        if ($codePoste) {
            $JoinPlus .= " join compteposte on comptes.codePoste = compteposte.codePoste  ";
            $sqlPlus .= " and compteposte.codePoste = $codePoste ";
        } elseif ($codePoste_synthese) {
            $JoinPlus .= " join compteposte_synthese on comptes.codePoste_synthese = compteposte_synthese.codePoste_synthese  ";
            $sqlPlus .= " and compteposte_synthese.codePoste_synthese = $codePoste_synthese ";
        } else {
            return false;
        }

        if ($Type) {
            $sqlPlus .= " and comptes.type='$Type'";
        }

        if ($RRRO) {
            $sqlPlus .= " and comptes.RRRO >='1'";
        } else {
            $sqlPlus .= " and comptes.RRRO <='0'";
        }

        $sql = "select comptes.* from comptes
                $JoinPlus
                where 1 $sqlPlus";

        Database::query($sql);

        $Return = array();

        while ($ln = Database::fetchArray()) {
            $Return[$ln["code_compte"]] = $ln;
        }

        return $Return;
    }

    static function setPrev($DOS_NUM, $Periode, $Values, $Type = null, $CodePostes = null)
    {
        $WherePlus = "";
        $PrevAgip = 0;

        if ($Type == 'Produits') {
            $Type = " and codePoste in (select codePoste from compteposte where Type='Produits')";
        } elseif ($Type == 'Charges') {
            $Type = " and codePoste in (select codePoste from compteposte where Type='Charges' )";
        }

        if ($CodePostes) {
            $WherePlus .= " and codePoste in (" . implode(",", $CodePostes) . ")";
        }

        $sql = "delete from resultatposte where 1  $Type and  DOS_NUM = '" . $DOS_NUM . "' and Periode= '$Periode' and PrevAgip = $PrevAgip $WherePlus";

        Database::query($sql);

        if ($Values) {
            $sql = "insert into resultatposte ( `DOS_NUM`,`PrevAgip`, `codePoste`, `SAI_CLE`,`N1`,`NSURN1`, `Annuel`, `Correction`, `Montant`, `Periode`, `PrevTaux`,`PrevTauxMontant`,`QuotePart_Tx`) VALUES ";
            $prem = true;

            foreach ($Values as $codePoste => $UnPoste) {
                $UnPoste["N1"] ??= 0;
                $UnPoste["NSURN1"] ??= 0;
                $UnPoste["QuotePart_Tx"] ??= 1;

                if (!$prem) {
                    $sql .= ",";
                } else {
                    $prem = false;
                }

                if ($UnPoste["cor"] == "0" || $UnPoste["cor"] > 0 || $UnPoste["cor"] < 0) {
                    $MaCor = $UnPoste["cor"];
                } else {
                    $MaCor = "NULL";
                }

                if (!isset($UnPoste["carburant"]) || !$UnPoste["carburant"]) {
                    $Marge = round($UnPoste["montant"] * ($UnPoste["prevtaux"] / 100));
                } else {
                    $Marge = round(($UnPoste["montant"] / 1000) * $UnPoste["prevtaux"]);
                }

                $sql .= "('" . $DOS_NUM . "','" . $PrevAgip . "','$codePoste','" . $UnPoste["cle"] . "','" . $UnPoste["N1"] . "','" . $UnPoste["NSURN1"] . "','" . $UnPoste["annuel"] . "',$MaCor,'" . $UnPoste["montant"] . "','$Periode','" . $UnPoste["prevtaux"] . "','" . $Marge . "','" . $UnPoste["QuotePart_Tx"] . "')";
            }

            Database::query($sql);
        }

        // Mise à jour des quote part taux
        //
        // On initialise tous le monde à "1"
        $sql = "UPDATE resultatposte,compteposte SET QuotePart_Tx = '1'
                WHERE resultatposte.codePoste = compteposte.codePoste
                    AND resultatposte.Periode = '$Periode'
                    AND resultatposte.DOS_NUM = '$DOS_NUM'
                    AND compteposte.QUOTEPART_NUM <= 0";
        Database::query($sql);

        //on recherche les quote part saisie alors qu'il n'y a pas de montant dans la famille à emputer
        $sql = "SELECT compteposte.QUOTEPART_NUM,sum(resultatposte_rba.Montant) AS MontantRBA
                FROM  `resultatposte`
                JOIN compteposte ON compteposte.codePoste = resultatposte.codePoste
                    AND compteposte.QUOTEPART_NUM >0
                    AND resultatposte.DOS_NUM = $DOS_NUM
                    AND resultatposte.Periode = '$Periode'
                JOIN compteposte compteposte_rba ON compteposte_rba.QUOTEPART_NUM_EMPUTE = compteposte.QUOTEPART_NUM
                LEFT JOIN resultatposte resultatposte_rba ON resultatposte_rba.codePoste = compteposte_rba.codePoste
                    AND resultatposte_rba.Periode = resultatposte.Periode
                    AND resultatposte_rba.DOS_NUM = resultatposte.DOS_NUM
                WHERE 1
                GROUP BY compteposte.QUOTEPART_NUM
                HAVING MontantRBA IS NULL";
        Database::query($sql);

        $QUOTEPART_ERROR = array();

        while ($ln = Database::fetchArray()) {
            $QUOTEPART_ERROR[$ln["QUOTEPART_NUM"]] = $ln;
        }

        foreach ($QUOTEPART_ERROR as $QUOTEPART_NUM => $ln) {
            // On supprime la quotepart saisie, car il n'y a pas de montant dans la famille à emputer
            $sql = "DELETE FROM resultatposte USING resultatposte,compteposte
                    WHERE resultatposte.codePoste = compteposte.codePoste
                        AND compteposte.QUOTEPART_NUM = $QUOTEPART_NUM
                        AND resultatposte.Periode = '$Periode'
                        AND resultatposte.DOS_NUM = '$DOS_NUM'";
            Database::query($sql);
        }

        //On met à jour les montants annuel et mensuel de chaque quote part de chaque ONFR
        $sql = "SELECT QUOTEPART_NUM_EMPUTE, SUM(Annuel) AS Annuel, SUM(Montant) AS Montant
                FROM resultatposte
                JOIN compteposte ON compteposte.codePoste = resultatposte.codePoste
                WHERE QUOTEPART_NUM_EMPUTE > 0
                    AND resultatposte.Periode = '$Periode'
                    AND resultatposte.DOS_NUM = '$DOS_NUM'
                    GROUP BY QUOTEPART_NUM_EMPUTE";
        Database::query($sql);

        $MontantsRBA = array();

        while ($ln = Database::fetchArray()) {
            $MontantsRBA[$ln["QUOTEPART_NUM_EMPUTE"]] = $ln;
        }

        foreach ($MontantsRBA as $QUOTEPART_NUM_EMPUTE => $Values) {
            $sql = "UPDATE resultatposte,compteposte
                    SET QuotePart_Tx = (annuel / " . $Values["Annuel"] . ") , Montant = (annuel / " . $Values["Annuel"] . ") * " . $Values["Montant"] . "
                    WHERE resultatposte.codePoste = compteposte.codePoste
                        AND QUOTEPART_NUM = $QUOTEPART_NUM_EMPUTE
                        AND resultatposte.Periode = '$Periode'
                        AND resultatposte.DOS_NUM = '$DOS_NUM'
                        AND QuotePart_Tx = 0
                        AND annuel > 0";
            Database::query($sql);

            $sql = "UPDATE resultatposte,compteposte
                    SET Annuel = QuotePart_Tx * " . $Values["Annuel"] . ", Montant = QuotePart_Tx * " . $Values["Montant"] . "
                    WHERE resultatposte.codePoste = compteposte.codePoste
                        AND QUOTEPART_NUM = $QUOTEPART_NUM_EMPUTE
                        AND resultatposte.Periode = '$Periode'
                        AND resultatposte.DOS_NUM = '$DOS_NUM'
                        AND QuotePart_Tx > 0
                        AND annuel = 0";
            Database::query($sql);
        }

        // On empute les RBA
        $sql = "SELECT QUOTEPART_NUM, (1 - SUM(QuotePart_Tx)) AS QuotePart_Tx
                FROM resultatposte
                JOIN compteposte ON compteposte.codePoste = resultatposte.codePoste
                WHERE compteposte.QUOTEPART_NUM > 0
                    AND resultatposte.Periode = '$Periode'
                    AND resultatposte.DOS_NUM = '$DOS_NUM'
                GROUP BY compteposte.QUOTEPART_NUM";
        Database::query($sql);

        $MesTaux = array();

        while ($ln = Database::fetchArray()) {
            $MesTaux[$ln["QUOTEPART_NUM"]] = $ln;
        }

        //pour chaque poste à emputer, on met à jour le tx, le montant annuel et le montant mensuel
        foreach ($MesTaux as $QUOTEPART_NUM => $Values) {
            $sql = "UPDATE resultatposte,compteposte
                    SET QuotePart_Tx = '" . $Values["QuotePart_Tx"] . "', Annuel = Annuel * " . $Values["QuotePart_Tx"] . ", Montant = Montant * " . $Values["QuotePart_Tx"] . "
                    WHERE resultatposte.codePoste = compteposte.codePoste
                    AND QUOTEPART_NUM_EMPUTE = $QUOTEPART_NUM
                    AND resultatposte.Periode = '$Periode'
                    AND resultatposte.DOS_NUM = '$DOS_NUM'";
            Database::query($sql);
        }

        //on met à jour les quotepart_tx pour que tous les postes soient pris en comptes
        $sqlDel = "DELETE FROM quotepart_empute
                    WHERE quotepart_empute.QE_PERIODE = '$Periode'
                        AND quotepart_empute.QE_DOS_NUM = '$DOS_NUM'";
        Database::query($sqlDel);

        $sql = "INSERT INTO quotepart_empute
                    SELECT DISTINCT
                        `resultatposte`.`DOS_NUM` AS `QE_DOS_NUM`,
                        `resultatposte`.`Periode` AS `QE_PERIODE`,
                        `compteposte`.`QUOTEPART_NUM_EMPUTE` AS `QE_CP_NUM`,
                        '0',
                        `resultatposte`.`QuotePart_Tx` AS `QE_QUOTE_PART_TX`
                    FROM `resultatposte`
                    JOIN `compteposte` ON `compteposte`.`codePoste` = `resultatposte`.`codePoste`
                    WHERE resultatposte.Periode = '$Periode'
                        AND resultatposte.DOS_NUM = '$DOS_NUM'
                        AND compteposte.QUOTEPART_NUM_EMPUTE > 0";
        Database::query($sql);

        $sql = "INSERT INTO quotepart_empute
                    SELECT DISTINCT
                        `resultatposte`.`DOS_NUM` AS `QE_DOS_NUM`,
                        `resultatposte`.`Periode` AS `QE_PERIODE`,
                        `compteposte`.`QUOTEPART_NUM_EMPUTE` AS `QE_CP_NUM`,
                        `cp_2`.`ONFR_NUM` AS `ONFR_NUM`,
                        `rs_2`.`QuotePart_Tx` AS `QE_QUOTE_PART_TX`
                    FROM `resultatposte`
                    JOIN `compteposte` ON `compteposte`.`codePoste` = `resultatposte`.`codePoste`
                    JOIN compteposte cp_2 ON cp_2.QUOTEPART_NUM = compteposte.QUOTEPART_NUM_EMPUTE
                    JOIN resultatposte rs_2 ON rs_2.codePoste = cp_2.codePoste
                        AND resultatposte.Periode = rs_2.Periode
                        AND resultatposte.DOS_NUM = rs_2.DOS_NUM
                    WHERE resultatposte.Periode = '$Periode'
                        AND resultatposte.DOS_NUM = '$DOS_NUM'
                        AND compteposte.QUOTEPART_NUM_EMPUTE > 0";
        Database::query($sql);
    }

    static function getPrev($DOS_NUM, $Periode, $Type = NULL, $Sum = false, $CodePoste = false, $Famille = false, $SsFamille = NULL, $Marge = false, $TotalProd = false, $TotalCharges = false, $CodePosteExclu = false, $NotMarge = false, $cumul = false, $cluster = false)
    {
        $Group = "";
        $DebEx = date("Y-m-00", strtotime($_SESSION["station_DOS_DEBEX"]));
        $PrevAgip = " AND PrevAgip = '0' ";

        $PeriodeSql = $WherePlus = $TypeSql = "";

        if ($Type == 'Produits') {
            $TypeSql = " AND compteposte.codePoste not in (select codePoste from compteposte where Type='Charges')";
        } elseif ($Type == 'Charges') {
            $TypeSql = " AND compteposte.codePoste not in (select codePoste from compteposte where Type='Produits')";
        }

        if ($Periode && !$cumul) {
            $PeriodeSql .= " AND Periode='$Periode' ";
        }

        if ($cumul) {
            $PeriodeSql .= " AND Periode<='$Periode' AND Periode >= '$DebEx' ";
        }

        if ($CodePoste) {
            $WherePlus .= " AND compteposte.codePoste='$CodePoste'  ";
        }

        if ($Famille) {
            $WherePlus .= " AND compteposte.Famille = \"" . $Famille . "\" ";
        }

        if ($SsFamille) {
            $WherePlus .= " AND compteposte.SsFamille = \"" . $SsFamille . "\" ";
        }

        if ($TotalProd) {
            $WherePlus .= " AND comptePoste.Resultat='1'  AND comptePoste.Type = 'Produits'";
        }

        if ($TotalCharges) {
            $WherePlus .= " AND comptePoste.Resultat='1'  AND comptePoste.Type = 'Charges'";
        }

        if ($CodePosteExclu) {
            $WherePlus .= " AND comptePoste.codePoste != '$CodePosteExclu'";
        }

        if ($Marge) {
            $WherePlus .= " AND compteposte.Marge = 1 ";
            $cumul = true;
        } elseif ($NotMarge) {
            $WherePlus .= " AND compteposte.Marge = 0 ";
        }

        if ($Sum) {
            $Select = " resultatposte.codePoste, compteposte.Type, SUM(Annuel) AS Annuel, SUM(Correction) AS Correction, SUM(Montant) AS Montant, PrevTaux, SUM(PrevTauxMontant) AS PrevTauxMontant ";
        } else {
            $Select = " compteposte.Type, resultatposte.* ";
        }

        if ($cumul && !$TotalProd && !$TotalCharges) {
            $Group .= " GROUP BY resultatposte.codePoste ";
        }

        $CondDos = " AND dossier.DOS_NUM = '$DOS_NUM' ";

        if ($cluster) {
            $CondDos = " AND station.STA_NUM_CLUSTER = '" . $_SESSION["station_STA_NUM_CLUSTER"] . "' ";
        }

        $sql = "SELECT $Select, compteposte.ONFR_NUM
                FROM resultatposte
                JOIN compteposte ON compteposte.codePoste = resultatposte.codePoste
                JOIN dossier ON dossier.DOS_NUM = resultatposte.DOS_NUM
                JOIN station ON station.STA_NUM = dossier.STA_NUM
                WHERE 1
                $CondDos
                $PrevAgip
                $TypeSql
                $PeriodeSql
                $WherePlus
                $Group";

        Database::query($sql);

        $Return = array();

        while ($ln = Database::fetchArray()) {
            // Pour les sum
            if (!$ln["codePoste"]) {
                $ln["codePoste"] = 0;
            }

            if (!$TotalProd && !$TotalCharges) {
                $Return[$ln["codePoste"]] = $ln;
            } else {
                $Return = $ln;
            }
        }

        return $Return;
    }

    static function getRgDivers($Mois, $DOS_NUM = false, $Champ, $Cumul = false, $MaxDate = false, $STA_NUM = false, $cluster = false)
    {
        $WherePlus = "";
        $CondDos = "";

        if ($cluster) {
            $CondDos = " and station.STA_NUM_CLUSTER = '" . $_SESSION["station_STA_NUM_CLUSTER"] . "' ";
        } elseif ($STA_NUM) {
            $CondDos = " and dossier.STA_NUM = '$STA_NUM' ";
        } elseif ($DOS_NUM) {
            $CondDos = " and dossier.DOS_NUM = '$DOS_NUM' ";
        }

        if ($Cumul) {
            $select = "rgdivers.*,sum(RGD_MONTANT) as RGD_MONTANT,count(RGD_MONTANT) as NbMontant";
            $WherePlus .= " and RGD_MOIS <= '$Mois' ";
        } elseif ($MaxDate) {
            $select = "rgdivers.*,max(RGD_DATE) as RGD_DATE";
            $WherePlus .= " and dossier.STA_NUM = '$STA_NUM' ";
        } else {
            $select = "rgdivers.*";
            $WherePlus .= " and RGD_MOIS = '$Mois' ";
        }

        $WherePlus .= "  and RGD_CHAMP = '$Champ' ";

        $sql = "select $select from rgdivers
                join dossier on dossier.DOS_NUM = rgdivers.DOS_NUM
                join station on station.STA_NUM = dossier.STA_NUM
                where 1 $CondDos $WherePlus";

        Database::query($sql);

        $Return = array();

        while ($ln = Database::fetchArray()) {
            $Return[$ln["RGD_CHAMP"]] = $ln;
        }

        return $Return;
    }

    static function setRgDivers($Mois, $DOS_NUM, $TabMontant)
    {
        $sql = "delete from rgdivers where RGD_MOIS='$Mois' and  DOS_NUM = '" . $DOS_NUM . "' ";

        Database::query($sql);

        $sql = "insert into rgdivers (`DOS_NUM`,`RGD_MOIS`, `RGD_CHAMP`, `RGD_MONTANT`,RGD_DATE) VALUES ";
        $prem = true;

        foreach ($TabMontant as $champ => $date) {
            if (!$prem) {
                $sql .= ",";
            } else {
                $prem = false;
            }

            $sql .= "('" . $DOS_NUM . "', '" . $Mois . "', '$champ', '0' ,'$date')";
        }

        Database::query($sql);
    }

    static function getMargeTheorique($CodeCptAchat, $Mois): array
    {
        $ConDos = '';
        if ($_SESSION["agip_AG_NUM"]) {
            $ConDos = " and station.LIE_NUM = " . $_SESSION["station_LIE_NUM"];
        }


        $NbMoisEcart = StringHelper::GetNbMoisEcart(date("Ym", strtotime($_SESSION["station_DOS_DEBEX"])), date("Ym", strtotime(str_replace('-00', '-01', $Mois))));
        $DateMin = StringHelper::DatePlus($Mois, array("moisplus" => -$NbMoisEcart, "dateformat" => "Y-m-00"));


        $retour = array();

        $sql = "select sum(BAL_BALANCE * (Taux/100)) as Montant, CodeCompteAttache as Compte
                from balance
                join station on station.STA_NUM = balance.codeStation
                JOIN comptes on comptes.code_compte = balance.codeCompte
                join comptepostedetail on  comptepostedetail.code_compte = comptes.CodeCompteAttache  and comptepostedetail.DOS_NUM = balance.DOS_NUM and balance.BAL_MOIS = comptepostedetail.Mois
                WHERE comptes.Type = 'vente'
                and CodeCompteAttache IN (" . implode(',', $CodeCptAchat) . ")
                and balance.BAL_MOIS <= '$Mois' and balance.BAL_MOIS >= '$DateMin' and comptepostedetail.Mois <= '$Mois'  and comptepostedetail.Mois >= '$DateMin'
                and balance.DOS_NUM = " . $_SESSION["station_DOS_NUM"] . "
                $ConDos
                GROUP BY CodeCompteAttache";

        Database::query($sql);

        while ($ln = Database::fetchArray()) {
            $retour[$ln['Compte']] = -round($ln['Montant'], 2);
        }

        return $retour;
    }

    static function getMargeTheoriqueGroupe($Mois, $DOS_NUM = NULL, $Cumul = false, $annem1 = false, $GroupBy_codePoste_synthese = false, $cluster = false)
    {
        $NbMoisEcart = StringHelper::GetNbMoisEcart(date("Ym", strtotime($_SESSION["station_DOS_DEBEX"])), date("Ym", strtotime(str_replace('-00', '-01', $Mois))));

        if ($annem1) {
            if ($_SESSION["station_DOS_PREMDATECP"] > 0 && $_SESSION["station_DOS_PREMDATECP"] != "0000-00-00") {
                $Mois = StringHelper::DatePlus($_SESSION["station_DOS_PREMDATECP"], array("moisplus" => $NbMoisEcart, "dateformat" => "Y-m-00"));
            } else {
                $Mois = StringHelper::DatePlus($Mois, array("moisplus" => -12, "dateformat" => "Y-m-00"));
            }
        }

        if ($Cumul) {
            $DateMin = StringHelper::DatePlus($Mois, array("moisplus" => -$NbMoisEcart, "dateformat" => "Y-m-00"));
        }

        if ($_SESSION["agip_AG_NUM"] && $annem1) {
            $ConDos = " and station.LIE_NUM = " . $_SESSION["station_LIE_NUM"];
        } else {
            if ($cluster) {
                $ConDos = " and station.STA_NUM_CLUSTER = '" . $_SESSION["station_STA_NUM_CLUSTER"] . "' ";
            } else {
                if ($DOS_NUM) {
                    $ConDos = " and balance.DOS_NUM = $DOS_NUM ";
                }
                $ConDos .= " and station.STA_NUM = '" . $_SESSION["station_STA_NUM"] . "' ";
            }
        }

        $MontantTotal = 0;

        if ($Cumul) {
            $WherePlus = " and balance.BAL_MOIS <= '$Mois' and balance.BAL_MOIS >= '$DateMin'";
        } else {
            $WherePlus = " and balance.BAL_MOIS = '$Mois' ";
        }

        $MyKey = " compteposte.codePoste ";
        $MyKey2 = "codePoste";
        $MyJoin = " JOIN compteposte on compteposte.`codePoste` = comptes.`codePoste` ";
        $MyGroup = " compteposte.codePoste ";

        if ($GroupBy_codePoste_synthese) {
            $MyKey = " compteposte_synthese.codePoste_synthese ";
            $MyKey2 = "codePoste_synthese";
            $MyJoin = " JOIN compteposte_synthese on compteposte_synthese.`codePoste_synthese` = comptes.`codePoste_synthese` ";
            $MyGroup = "comptes.codePoste_synthese,MargeDirect ";
        }

        $sql = "

                select $MyKey,CodeCompteAttache,sum(-BAL_BALANCE) as montantbalance,CAISMARGE,MargeDirect,(-sum(BAL_BALANCE)) as Montant,'100' from balance
                join dossier on dossier.DOS_NUM = balance.DOS_NUM
                                join station on station.STA_NUM = dossier.STA_NUM
                JOIN comptes on comptes.code_compte = balance.codeCompte
        $MyJoin
                WHERE comptes.Type = 'vente' and PasAchat = 1   $ConDos $WherePlus group by  $MyGroup
        
                UNION ALL
                select $MyKey,CodeCompteAttache,sum(-BAL_BALANCE) as montantbalance,CAISMARGE,MargeDirect,(-sum(BAL_BALANCE *(Taux/100))) as Montant,Taux from balance
                join dossier on dossier.DOS_NUM = balance.DOS_NUM
                                join station on station.STA_NUM = dossier.STA_NUM
                JOIN comptes on comptes.code_compte = balance.codeCompte
                                
        $MyJoin
                left join comptepostedetail on  comptepostedetail.code_compte = comptes.CodeCompteAttache  and comptepostedetail.DOS_NUM = balance.DOS_NUM and balance.BAL_MOIS = comptepostedetail.Mois
                WHERE comptes.Type = 'vente' and PasAchat = 0  $ConDos $WherePlus  group by  $MyGroup
                
                
        ";
        //echo $sql;

        $res = Database::query($sql);

        $Return = NULL;

        while ($Ln = Database::fetchArray($res)) {
            if ($Ln["CAISMARGE"] || $Ln["MargeDirect"]) {
                $Ln["Montant"] = $Ln["montantbalance"];
                $Ln["Taux"] = 100;
            }

            $ln["Montant"] = round($ln["Montant"]);

            if ($Return[$Ln["$MyKey2"]]) {
                $Return[$Ln["$MyKey2"]]["Montant"] += $Ln["Montant"];
            } else {
                $Return[$Ln["$MyKey2"]] = $Ln;
            }
        }

        return $Return;
    }

    //    static function getcodePosteInFamille($Famille, $SsFamille, $Marge = false, $Type = false, $CDC = false)
    //    {
    //        $WherePlus = "";
    //        if ($Famille) {
    //            $WherePlus .= " and Famille = \"$Famille\"";
    //        }
    //
    //        if ($SsFamille) {
    //            $WherePlus .= " and SsFamille = \"$SsFamille\"";
    //        }
    //
    //        if ($Marge) {
    //            $WherePlus .= " and compteposte.Marge = 1 ";
    //        }
    //
    //        if ($Type) {
    //            $WherePlus .= " and compteposte.Type = '$Type' ";
    //        }
    //
    //        if ($CDC) {
    //            $WherePlus .= " and compteposte.codePoste != 14 and Famille != 'CARBURANTS' and compteposte.Marge = 0 ";
    //        }
    //
    //        $sql = "SELECT comptePoste.codePoste from compteposte
    //                WHERE compteposte.resultat = 1 $WherePlus";
    //
    //        $res = Database::query($sql);
    //
    //        $Return = NULL;
    //
    //        while ($UnCompte = Database::fetchArray($res)) {
    //            $Return[$UnCompte["codePoste"]] = NULL;
    //        }
    //        return $Return;
    //    }

    static function getResultatFamille($DOS_NUM, $Mois, $Famille, $SsFamille = NULL, $AnM1 = NULL, $RestAnM1 = NULL, $CodePosteExlut = NULL, $ByMois = false, $FinAnM1 = false, $Type = false, $Saisonalisable = false)
    {
        $WherePlus = "";
        if ($Famille) {
            $WherePlus .= " and Famille = \"$Famille\"";
        }

        if ($Type) {
            $WherePlus .= " and compteposte.Type = \"$Type\"";
        }

        if ($SsFamille) {
            $WherePlus .= " and SsFamille = \"$SsFamille\"";
        }

        if ($CodePosteExlut) {
            $WherePlus .= " and comptePoste.codePoste != $CodePosteExlut ";
        }

        if ($Saisonalisable) {
            $WherePlus .= " and comptePoste.Saisonalisable = 1 ";
        }

        $CondDos = " and balance.dos_num = $DOS_NUM ";
        $DateInf = "";

        $NbMoisEcart = StringHelper::GetNbMoisEcart(date("Ym", strtotime($_SESSION["station_DOS_DEBEX"])), date("Ym", strtotime(str_replace('-00', '-01', $Mois))));
        $NbMoisEcart++;

        if ($FinAnM1) {
            if ($_SESSION["agip_AG_NUM"] && $_SESSION["station_DOS_PREMDATECP"] == 0) {
                $Mois = StringHelper::DatePlus($Mois, array("moisplus" => -$NbMoisEcart, "dateformat" => "Y-m-00"));
                $CondDos = " and station.LIE_NUM = '" . $_SESSION["station_LIE_NUM"] . "' ";
                $DateInf = " and bal_mois >= '" . StringHelper::DatePlus($Mois, array("moisplus" => -$_SESSION["station_DOS_NBMOIS"] + $NbMoisEcart - 1, "dateformat" => "Y-m-00")) . "' and bal_mois < '" . $_SESSION["station_DOS_DEBEX"] . "'";
            } else {
                $CondDos = " and balance.dos_num = '" . $_SESSION["station_DOS_NUMPREC"] . "' ";
            }

            if ($RestAnM1) {
                if ($_SESSION["station_DOS_PREMDATECP"] != "0000-00-00") {
                    $Mois = StringHelper::DatePlus(str_replace("-00", "-01", $_SESSION["station_DOS_PREMDATECP"]), array("moisplus" => $NbMoisEcart, "dateformat" => "Y-m-00"));
                } elseif ($_SESSION["agip_AG_NUM"]) {
                    $DateInf = " and bal_mois > '" . StringHelper::DatePlus($Mois, array("moisplus" => -$_SESSION["station_DOS_NBMOIS"] + $NbMoisEcart, "dateformat" => "Y-m-00")) . "' ";
                }
            }
        }

        if ($AnM1) {
            $NbMoisEcart = StringHelper::GetNbMoisEcart(date("Ym", strtotime($_SESSION["station_DOS_DEBEX"])), date("Ym", strtotime(str_replace('-00', '-01', $Mois))));

            if ($_SESSION["station_DOS_PREMDATECP"] != "0000-00-00") {
                $Mois = StringHelper::DatePlus(str_replace("-00", "-01", $_SESSION["station_DOS_PREMDATECP"]), array("moisplus" => $NbMoisEcart, "dateformat" => "Y-m-00"));
            } else {
                return;
            }

            $CondDos = " and codeStation='" . $_SESSION["station_STA_NUM"] . "' and balance.BAL_MOIS < '" . $_SESSION["DOS_DEBEX"] . "'";
        }

        if ($RestAnM1) {
            $SigneMois = ">=";
        } elseif ($ByMois) {
            $SigneMois = "=";
        } else {
            $SigneMois = "<=";
        }

        $sql = "SELECT sum(BAL_BALANCE) as Montant FROM `balance`
                join station on station.STA_NUM = balance.codeStation 
                join comptes on comptes.code_compte = balance.codeCompte 
                join compteposte on comptes.codePoste = compteposte.codePoste 
                WHERE 1 $CondDos 
                and bal_mois $SigneMois '$Mois' $DateInf
                and compteposte.resultat = 1 and comptes.MargeDirect = 0 and comptes.Type != 'achat'
                #and compteposte.codePoste != 66
                $WherePlus";

        $res = Database::query($sql);
        $Montant = Database::fetchArray($res);

        $Montant2["Montant"] = 0;
        /*if($Famille == "VENTES MARCHANDISES")
    {
        $sql2 = "Select sum(BAL_BALANCE) as Montant from balance
                join station on station.STA_NUM = balance.codeStation
                join comptes on comptes.code_compte = balance.codeCompte
                join compteposte on comptes.codePoste = compteposte.codePoste
                WHERE 1 $CondDos
                and bal_mois $SigneMois '$Mois' $DateInf
                and compteposte.codePoste = 66
            ";

        $res = Database::query($sql2);
        $Montant2 = Database::fetchArray($res);
        $Montant2["Montant"] = $Montant2["Montant"] / 0.47;
    }*/

        $Montant["Montant"] = -$Montant["Montant"];
        //$Montant2["Montant"] = -$Montant2["Montant"];

        return $Montant["Montant"] + $Montant2["Montant"];
    }

    /**
     * Récupération du résultat enregistré dans "balanceimport" après calcul dans la fonction "dbAccess::setMAJBase"
     */
    static function getResultatDossier_sauvegarde($d)
    {
        $WherePlus = "";

        if (isset($d["cumul"]) && $d["cumul"]) {
            $WherePlus .= " and BALI_MOIS <= '" . $d["BALI_MOIS"] . "'";
        } else {
            $WherePlus .= " and BALI_MOIS = '" . $d["BALI_MOIS"] . "'";
        }

        if (isset($d["cluster"]) && $d["cluster"]) {
            $WherePlus .= " and station.STA_NUM_CLUSTER = '" . $_SESSION["station_STA_NUM_CLUSTER"] . "' and BALI_MOIS >= '" . date("Y-m-00", strtotime($_SESSION["station_DOS_DEBEX"])) . "'";
        } else {
            $WherePlus .= " and dossier.DOS_NUM = '" . $_SESSION["station_DOS_NUM"] . "'";
        }

        $sql = "select sum(BALI_RES) as BALI_RES,sum(BALI_RESPREV) as BALI_RESPREV
                from balanceimport
                join dossier on dossier.DOS_NUM = balanceimport.DOS_NUM
                join station on station.STA_NUM = dossier.STA_NUM
                where 1 $WherePlus";

        Database::query($sql);

        return Database::fetchArray();
    }

    static function getResultatDossierPrev($DOS_NUM, $Periode, $GroupPeriode)
    {
        if (!$DOS_NUM) {
            return false;
        }

        $WherePlus = "";
        $GroupPlus = "";

        if ($Periode) {
            $WherePlus .= " and Periode='$Periode'";
        }

        if ($GroupPeriode) {
            $GroupPlus = " group by Periode";
        }

        $sql = "
            select  Periode,sum(MesMontants) as resultat from (

                SELECT DISTINCT Periode,sum(PrevTauxMontant) as MesMontants FROM `resultatposte`
                join compteposte on compteposte.codePoste = resultatposte.codePoste
                WHERE 1
                and type='Produits'
                and DOS_NUM = $DOS_NUM
                and resultat = 1
                and Marge = 1
        $WherePlus
        $GroupPlus

                UNION ALL
        
                SELECT DISTINCT Periode,sum(Montant) as MesMontants FROM `resultatposte`
                join compteposte on compteposte.codePoste = resultatposte.codePoste
                WHERE 1
                and type='Produits'
                and DOS_NUM = $DOS_NUM
                and resultat = 1
                and Marge = 0
        $WherePlus
        $GroupPlus

                UNION ALL

                SELECT DISTINCT Periode,sum(-Montant) as MesMontants FROM `resultatposte`
                join compteposte on compteposte.codePoste = resultatposte.codePoste
                WHERE 1
                and type='Charges'
                and DOS_NUM = $DOS_NUM
                and resultat = 1
        $WherePlus
        $GroupPlus

            ) MonUnion where 1

        $GroupPlus

        ";

        Database::query($sql);

        $Return = array();

        while ($ln = Database::fetchArray()) {
            if (!$GroupPeriode) {
                return $ln["resultat"];
            } else {
                $Return[$ln["Periode"]] = $ln;
            }
        }

        return $Return;
    }

    static function getTauxMarge($DOS_NUM, $Mois, $codePoste = false, $codeCompte = false, $all = false, $N1 = false, $sum = false)
    {
        $WherePlus = "";
        $GroupPlus = "";

        if ($codePoste) {
            $WherePlus = "and comptepostedetail.codePoste = $codeCompte";
        }

        if ($codePoste) {
            $WherePlus = "and comptes.code_compte = $codePoste";
        }

        if (!$N1) {
            $WherePlus .= " and DOS_NUM='$DOS_NUM' ";
        } else {
            $NbMoisEcart = StringHelper::GetNbMoisEcart(date("Ym", strtotime($_SESSION["station_DOS_DEBEX"])), date("Ym", strtotime(str_replace('-00', '-01', $Mois))));

            if ($_SESSION["station_DOS_PREMDATECP"] != "0000-00-00") {
                $Mois = StringHelper::DatePlus(str_replace("-00", "-01", $_SESSION["station_DOS_PREMDATECP"]), array("moisplus" => $NbMoisEcart, "dateformat" => "Y-m-00"));
            } else {
                return;
            }

            $WherePlus .= " and DOS_NUM='" . $_SESSION["station_DOS_NUMPREC"] . "' ";
        }

        if (!$all) {
            $WherePlus .= " and Mois = '$Mois' ";
        } else {
            $WherePlus .= " and Mois <= '$Mois' ";
        }

        $selectStock = " ,StockInit,StockFinal,StockTheorique ";

        if ($sum) {
            $selectStock = " ,sum(StockInit) as StockInit,sum(StockFinal) as StockFinal,sum(StockTheorique) as StockTheorique ";
            $GroupPlus .= " group by DOS_NUM";

        }

        $sql = "select codePosteDetail,comptes.code_compte,Taux,TauxReel,StockFinalZero,TauxZero,EcartMarge,EcartMargePrec,Mois $selectStock from comptepostedetail
        join comptes on comptes.code_compte = comptepostedetail.code_compte
         
        where 1   $WherePlus $GroupPlus";

        //echo $sql;
        $res = Database::query($sql);

        $Return = NULL;

        while ($ln = Database::fetchArray($res)) {
            if (!$all) {
                $cle = $ln["code_compte"];
            } else {
                $cle = $ln["code_compte"] . "||#||" . $ln["Mois"];
            }

            $Return[$cle] = $ln;
        }

        return $Return;
    }

    static function is_SituationInterm($DOS_NUM, $Mois)
    {
        $sql = "select dossier.DOS_NUM from comptepostedetail
        join dossier on dossier.DOS_NUM = comptepostedetail.DOS_NUM 
        where 1 and dossier.DOS_NUM = $DOS_NUM and DATE_FORMAT(dossier.DOS_FINEX,'%Y-%m-00') <> '$Mois' and Mois = '$Mois' 
        and (StockFinal > 0 or StockFinalZero > 0)";

        $res = Database::query($sql);

        if ($ln = Database::fetchArray($res)) {
            return true;
        } else {
            return false;
        }
    }

    static function setTaux($MesTaux, $DOS_NUM, $Mois, $codeCompteAjout)
    {
        $WherePlus = "";
        if ($codeCompteAjout) {
            $WherePlus .= " and code_compte = '$codeCompteAjout' ";
        }

        $sql = "delete from comptepostedetail where 1 and DOS_NUM = '$DOS_NUM' and Mois='$Mois' $WherePlus";

        Database::query($sql);

        $sql = "insert into comptepostedetail (codePosteDetail,`DOS_NUM`, `code_compte`,`Mois`,`Taux`, `StockInit`, `StockFinal`,`StockFinalZero`,`TauxZero`,`EcartMarge`,`EcartMargePrec`) VALUES ";
        $prem = true;
        foreach ($MesTaux as $codeCompte => $ligne) {

            if (!$prem) {
                $sql .= ",";
            } else {
                $prem = false;
            }

            if (($ligne["StockFinal"] == "0" && $ligne["StockFinal"] != "") && $ligne["StockFinalZero"] != "0") {
                $StockFinalZero = "1";
            } else {
                $StockFinalZero = "0";
            }

            if ($ligne["Taux"] == "0") {
                $TauxZero = "1";
            } else {
                $TauxZero = "0";
            }

            $sql .= "(0,'" . $DOS_NUM . "','" . $codeCompte . "','" . $Mois . "','" . $ligne["Taux"] . "','" . $ligne["StockInit"] . "','" . $ligne["StockFinal"] . "','$StockFinalZero','$TauxZero','" . $ligne["EcartMarge"] . "','" . $ligne["EcartMargePrec"] . "')";
        }

        Database::query($sql);
    }

    static function setTxMarge($DOS_NUM, $Mois, $Tx)
    {
        foreach ($Tx as $code_compte => $MonTx) {
            $sql = "
        select * from
        comptepostedetail
        where 1
        and DOS_NUM = $DOS_NUM
        and Mois = '$Mois'
        and code_compte = $code_compte
        ";

            Database::query($sql);

            $sql = "";

            if (!Database::fetchArray()) {
                $sql = "
            insert into comptepostedetail set
            DOS_NUM = $DOS_NUM,
            Mois = '$Mois',
            code_compte = $code_compte,
            Taux = '$MonTx'

        ";
            } else {
                $sql = "
            update comptepostedetail set
            Taux = '$MonTx'
            where 1
            and DOS_NUM = $DOS_NUM
            and Mois = '$Mois'
            and code_compte = $code_compte

        ";
            }

            Database::query($sql);
        }
    }

    static function setEcartMarge($DOS_NUM, $Mois, $CodeCpt, $EcartMarge)
    {
        $sql = "update comptepostedetail set EcartMarge = '$EcartMarge' where DOS_NUM = '$DOS_NUM' and Mois = '$Mois' and code_compte = '$CodeCpt' ";
        Database::query($sql);
    }

    static function getEcartMarge($DOS_NUM, $Mois, $codePoste = false, $codeCompte = false, $cumul = false, $codePoste_synthese = false, $cluster = false, $annem1 = false)
    {
        $wherePlus = "";

        $NbMoisEcart = StringHelper::GetNbMoisEcart(date("Ym", strtotime($_SESSION["station_DOS_DEBEX"])), date("Ym", strtotime(str_replace('-00', '-01', $Mois))));
        if ($annem1) {
            if ($_SESSION["station_DOS_PREMDATECP"] > 0 && $_SESSION["station_DOS_PREMDATECP"] != "0000-00-00") {
                $Mois = StringHelper::DatePlus($_SESSION["station_DOS_PREMDATECP"], array("moisplus" => $NbMoisEcart, "dateformat" => "Y-m-00"));
            } else {
                $Mois = StringHelper::DatePlus($Mois, array("moisplus" => -12, "dateformat" => "Y-m-00"));
            }
        }

        if ($codePoste) {
            $wherePlus .= " and comptes.codePoste = '$codePoste' ";
        }

        if ($codePoste_synthese) {
            $wherePlus .= " and comptes.codePoste_synthese = '$codePoste_synthese' ";
        }

        if ($codeCompte) {
            $wherePlus .= " and comptes.code_compte = '$codeCompte' ";
        }

        $CondDos = " and dossier.DOS_NUM='$DOS_NUM'  ";

        if ($annem1) {
            $CondDos = " and station.STA_NUM = '" . $_SESSION["station_STA_NUM"] . "'  ";
        } elseif ($cluster) {
            $CondDos = " station.STA_NUM_CLUSTER = '" . $_SESSION["station_STA_NUM_CLUSTER"] . "'  ";
        }

        $sql = "select comptes.codePoste,comptes.codePoste_synthese,comptes.code_compte,EcartMarge,EcartMargePrec, StockFinal, StockFinalZero from comptepostedetail
        join comptes on comptes.code_compte = comptepostedetail.code_compte
                join dossier on dossier.DOS_NUM = comptepostedetail.DOS_NUM
                join station on station.STA_NUM = dossier.STA_NUM
        where 1
        $CondDos
                and Mois = '$Mois' $wherePlus";
        //echo $sql."<br/><br/>";
        $res = Database::query($sql);

        $Return = NULL;

        $Montant = 0;

        while ($ln = Database::fetchArray($res)) {
            if ($cumul) {
                if ($ln['EcartMarge'] || ($ln['StockFinalZero'] || $ln['StockFinal'])) {
                    $Montant += $ln['EcartMarge'];
                } else {
                    $Montant += $ln['EcartMargePrec'];
                }
            } else {
                if ($ln['EcartMarge'] || $ln['StockFinalZero']) {
                    $Montant += $ln['EcartMarge'] - $ln['EcartMargePrec'];
                }
            }
        }

        return $Montant;
    }

    /**
     * Set the theoretical stock for a given dossier, month, and account code.
     * If an array of account codes is provided, the stock will be set for multiple accounts.
     * If a specific stock value is provided, it will be set for the given dossier, month, and account code.
     *
     * @param string $DOS_NUM The dossier number
     * @param string $Mois The month
     * @param string|array $CodeCpt The account code(s)
     * @param float|null $StockTheo The theoretical stock value (optional)
     *
     * @return void
     */
    static function setStockTheorique(string $DOS_NUM, string $Mois, $CodeCpt, ?float $StockTheo = null)
    {
        $sql = "update comptepostedetail set StockTheorique = '$StockTheo' where DOS_NUM = '$DOS_NUM' and Mois = '$Mois' and code_compte = '$CodeCpt' ";
        Database::query($sql);
    }

    static function setStockRetenuBilan($DOS_NUM, $Mois, $CodeCpt, $StockRetenu)
    {
        $sql = "update comptepostedetail set StockRetenuBilan = '$StockRetenu' where DOS_NUM = '$DOS_NUM' and Mois = '$Mois' and code_compte = '$CodeCpt' ";
        Database::query($sql);
    }

    static function getStockRetenuBilan($DOS_NUM, $Mois, $anm1 = false, $Cluster = false)
    {
        $Dos = " and dossier.DOS_NUM = '$DOS_NUM' ";

        if ($Cluster) {
            $Dos = " and station.STA_NUM_CLUSTER ='" . $_SESSION["station_STA_NUM_CLUSTER"] . "' ";
        }

        if ($anm1) {
            $NbMoisEcart = StringHelper::GetNbMoisEcart(date("Ym", strtotime($_SESSION["station_DOS_DEBEX"])), date("Ym", strtotime(str_replace('-00', '-01', $Mois))));

            if ($_SESSION["station_DOS_PREMDATECP"] > 0 && $_SESSION["station_DOS_PREMDATECP"] != "0000-00-00") {
                $Mois = StringHelper::DatePlus(str_replace("-00", "-01", $_SESSION["station_DOS_PREMDATECP"]), array("moisplus" => $NbMoisEcart, "dateformat" => "Y-m-00"));
            } elseif ($_SESSION["agip_AG_NUM"]) {
                $Mois = StringHelper::DatePlus($Mois, array("moisplus" => -12, "dateformat" => "Y-m-00"));
            } else {
                return 0;
            }

            if (!$_SESSION["agip_AG_NUM"] || $_SESSION["station_DOS_PREMDATECP"] > 0) {
                $Dos = " and station.STA_NUM='" . $_SESSION["station_STA_NUM"] . "' ";
            } else {
                $Dos = " and station.LIE_NUM='" . $_SESSION["station_LIE_NUM"] . "' ";
            }
        }

        $sql = "select sum(StockRetenuBilan) as somme from comptepostedetail
                join dossier on dossier.DOS_NUM = comptepostedetail.DOS_NUM
                join station on station.STA_NUM = dossier.STA_NUM
                where 1 $Dos and comptepostedetail.Mois = '$Mois' ";

        Database::query($sql);
        $ln = Database::fetchArray();

        return $ln["somme"];
    }

    static function getStockRetenuBilanDetail($DOS_NUM, $Mois, $anm1 = false, $Cluster)
    {
        $Return = NULL;

        $Dos = " and dossier.DOS_NUM = '$DOS_NUM' ";

        if ($Cluster) {
            $Dos = " and station.STA_NUM_CLUSTER ='" . $_SESSION["station_STA_NUM_CLUSTER"] . "' ";
        }

        if ($anm1) {
            if ($_SESSION["station_DOS_PREMDATECP"] > 0 && $_SESSION["station_DOS_PREMDATECP"] != "0000-00-00") {
                $Mois = StringHelper::DatePlus(str_replace("-00", "-01", $_SESSION["station_DOS_PREMDATECP"]), array("moisplus" => $NbMoisEcart, "dateformat" => "Y-m-00"));
            } elseif ($_SESSION["agip_AG_NUM"]) {
                $Mois = StringHelper::DatePlus($Mois, array("moisplus" => -12, "dateformat" => "Y-m-00"));
            } else {
                return 0;
            }

            if (!$_SESSION["agip_AG_NUM"] || $_SESSION["station_DOS_PREMDATECP"] > 0) {
                $Dos = " and station.STA_NUM='" . $_SESSION["station_STA_NUM"] . "' ";
            } else {
                $Dos = " and station.LIE_NUM='" . $_SESSION["station_LIE_NUM"] . "' ";
            }

        }

        $sql = "select CPTB_NUM, sum(StockRetenuBilan) as somme from comptepostedetail
        join dossier on dossier.DOS_NUM = comptepostedetail.DOS_NUM 
        join station on station.STA_NUM = dossier.STA_NUM 
                join AS_comptes_comptepostebilan on AS_comptes_comptepostebilan.codeCompte = comptepostedetail.code_compte
        where 1  $Dos and comptepostedetail.Mois = '$Mois' 
                GROUP BY AS_comptes_comptepostebilan.CPTB_NUM";
        //echo "<br/>$sql";

        $res = Database::query($sql);

        while ($ln = Database::fetchArray($res)) {
            $Return[$ln["CPTB_NUM"]] = $ln["somme"];
        }

        return $Return;
    }

    static function setEcartMargePrec($DOS_NUM, $Mois, $CodeCpt, $EcartMargePrec)
    {
        $sql = "update comptepostedetail set EcartMargePrec = '$EcartMargePrec' where DOS_NUM = '$DOS_NUM' and Mois = '$Mois' and code_compte = '$CodeCpt' ";
        Database::query($sql);
    }

    static function setTauxReelCompte($DOS_NUM, $Mois, $CodeCpt, $TauxReel)
    {
        $sql = "update comptepostedetail set TauxReel = '$TauxReel' where DOS_NUM = '$DOS_NUM' and Mois = '$Mois' and code_compte = '$CodeCpt' ";
        Database::query($sql);
    }

    static function AddDossier($Prop, $upd = 0)
    {
        $Vals = dbAcces::formatInsertUpdate($Prop);

        if (!$upd) {
            $sql = "insert into dossier set $Vals";
        } else {
            $sql = "update dossier set $Vals where DOS_NUM = $upd";
        }

        if (Database::query($sql)) {
            if (!$upd) {
                $num = Database::lastPK();
                dbAcces::setLastBalance($Prop["STA_NUM"], date("Y-m-00", strtotime($Prop["DOS_DEBEX"])));
            }

            if ($num) {
                return $num;
            }

            return $upd;
        } else {
            return false;
        }
    }

    static function getCarb()
    {
        $sql = "select carburant.*,compteposte.`codePoste` from carburant join compteposte on compteposte.`CARB_NUM` = carburant.`CARB_NUM` order by CARB_TRI";

        Database::query($sql);

        $Return = [];

        while ($ln = Database::fetchArray()) {
            $Return[$ln["CARB_NUM"]] = $ln;
        }

        return $Return;
    }

    static function getLitrageCarb($DOS_NUM, $Periode, $sum = false, $SumMois = false, $AnM1 = false, $ByMois = false, $cluster = false, $sumCarb = false)
    {
        $WherePlus = "";
        $GroupPlus = "";

        if ($sum) {
            $select = " carburant.CARB_NUM,sum(CARV_VOLUME) as CARV_VOLUME ";
            if (!$sumCarb) {
                $GroupPlus = " group by CARB_NUM";

                if ($ByMois) {
                    $GroupPlus .= ",Periode";
                }
            }
        } else {
            $select = "*";
        }

        $CondDos = " and dossier.DOS_NUM = '$DOS_NUM' ";

        if ($cluster) {
            $CondDos = "  and station.STA_NUM_CLUSTER = '" . $_SESSION["station_STA_NUM_CLUSTER"] . "'  ";
        }

        if ($AnM1) {
            $NbMoisEcart = StringHelper::GetNbMoisEcart(date("Ym", strtotime($_SESSION["station_DOS_DEBEX"])), date("Ym", strtotime(str_replace('-00', '-01', $Periode))));

            if ($_SESSION["station_DOS_PREMDATECP"] != "0000-00-00") {
                $Periode = StringHelper::DatePlus(str_replace("-00", "-01", $_SESSION["station_DOS_PREMDATECP"]), array("moisplus" => $NbMoisEcart, "dateformat" => "Y-m-00"));
            } else {
                return;
            }

            if (!$cluster && $_SESSION["agip_AG_NUM"]) {
                $CondDos = " and station.LIE_NUM='" . $_SESSION["station_LIE_NUM"] . "' ";
            }
        }

        if (!$sum || $SumMois) {
            $WherePlus .= " and CARV_PERIODE= '$Periode' ";
        } else {
            $WherePlus .= " and CARV_PERIODE <= '$Periode' and CARV_PERIODE >= '" . $_SESSION["station_DOS_PREMDATECP"] . "' ";
        }

        $sql = "select $select from carburantvolumes
                join carburant on carburant.CARB_NUM = carburantvolumes.CARB_NUM
                join dossier on dossier.DOS_NUM = carburantvolumes.DOS_NUM
                join station on station.STA_NUM = dossier.STA_NUM
                where 1
                $CondDos $WherePlus $GroupPlus";

        Database::query($sql);

        $Return = array();

        while ($ln = Database::fetchArray()) {
            if ($sumCarb) {
                $Return = $ln["CARV_VOLUME"];
            } else {
                $Return[$ln["CARB_NUM"]] = $ln;
            }
        }

        return $Return;
    }

    static function getLitrageCarbPrev($DOS_NUM, $Periode, $sum = false, $SumMois = false, $AnM1 = false, $ByMois = false, $cluster = false, $sumCarb = false)
    {
        $WherePlus = "";
        $GroupPlus = "";

        if ($sum) {
            $select = " carburant.CARB_NUM, sum(Montant) as CARV_VOLUME ";
            if (!$sumCarb) {
                $GroupPlus = " group by CARB_NUM";

                if ($ByMois) {
                    $GroupPlus .= ",Periode";
                }
            }
        } else {
            $select = "*";
        }

        $CondDos = " and dossier.DOS_NUM = '$DOS_NUM' ";

        if ($cluster) {
            $CondDos = "  and station.STA_NUM_CLUSTER = '" . $_SESSION["station_STA_NUM_CLUSTER"] . "'  ";
        }

        if ($AnM1) {
            $NbMoisEcart = StringHelper::GetNbMoisEcart(date("Ym", strtotime($_SESSION["station_DOS_DEBEX"])), date("Ym", strtotime(str_replace('-00', '-01', $Periode))));

            if ($_SESSION["station_DOS_PREMDATECP"] != "0000-00-00") {
                $Periode = StringHelper::DatePlus(str_replace("-00", "-01", $_SESSION["station_DOS_PREMDATECP"]), array("moisplus" => $NbMoisEcart, "dateformat" => "Y-m-00"));
            } else {
                return;
            }

            //$CondDos1 = " and balance.codeStation = ".$_SESSION["station_STA_NUM"]." and balance.BAL_MOIS < '".date("Y-m-00",strtotime($_SESSION["station_DOS_DEBEX"]))."'";
            //$DOS_NUM = $_SESSION["station_DOS_NUMPREC"];

            if (!$cluster && $_SESSION["agip_AG_NUM"]) {
                $CondDos = " and station.LIE_NUM='" . $_SESSION["station_LIE_NUM"] . "' ";
            }
        }

        if (!$sum || $SumMois) {
            $WherePlus .= " and resultatposte.Periode = '$Periode' ";
        } else {
            $WherePlus .= " and resultatposte.Periode <= '$Periode' and resultatposte.Periode >= '" . $_SESSION["station_DOS_PREMDATECP"] . "' ";
        }

        $sql = "
                select $select from resultatposte
                join compteposte on resultatposte.codePoste = compteposte.codePoste
                join carburant on carburant.CARB_NUM = compteposte.CARB_NUM
                join dossier on dossier.DOS_NUM = resultatposte.DOS_NUM
                join station on station.STA_NUM = dossier.STA_NUM
                where 1
        $CondDos
        $WherePlus $GroupPlus
        ";

        $res = Database::query($sql);
        $Return = NULL;

        while ($ln = Database::fetchArray($res)) {
            if ($sumCarb) {
                $Return = $ln["CARV_VOLUME"];
            } else {
                $Return[$ln["CARB_NUM"]] = $ln;
            }
        }

        return $Return;
    }

    static function setLitrageCarb($DOS_NUM, $Periode, $MesCarb)
    {
        $sql = "delete from carburantvolumes where DOS_NUM = '$DOS_NUM' and CARV_PERIODE='$Periode'";

        Database::query($sql);

        $sql = "insert into carburantvolumes (`DOS_NUM`, `CARV_PERIODE`,`CARV_VOLUME`, `CARB_NUM`) VALUES ";
        $prem = true;
        foreach ($MesCarb as $CARB_NUM => $Volume) {

            if ($Volume) {
                if (!$prem) {
                    $sql .= ",";
                } else {
                    $prem = false;
                }

                $sql .= "('" . $DOS_NUM . "','" . $Periode . "','" . $Volume . "','" . $CARB_NUM . "')";
            }

        }

        Database::query($sql);
    }


    static function getSaison($SAI_DATE = null, $DOS_NUM = null, &$MesSum = null, $SAI_NUMSAISON = null, &$PlageBase = null, $STA_NUM = null): array
    {
        $join = $SAI_NUMSAISONsql = '';
        $AG_NUMsql = " and AG_NUM = '0' ";
        $SAI_DATEsql = ($SAI_DATE) ? " and SAI_DATE = '$SAI_DATE' " : "";

        if ($DOS_NUM) {
            $DOS_NUM = " and DOS_NUM = '$DOS_NUM' ";
        } elseif ($STA_NUM) {
            $join = " join dossier on dossier.DOS_NUM = saison.DOS_NUM ";
            $STA_NUM = " and STA_NUM = '$STA_NUM' ";
        } else {
            $DOS_NUM = " and DOS_NUM = '" . $_SESSION["station_DOS_NUM"] . "'";
        }

        if ($SAI_DATE > 0 && !$SAI_NUMSAISON) {
            $SAI_NUMSAISONsql = " and SAI_NUMSAISON = (select SAI_NUMSAISON
                                                    from saison $join
                                                    where 1 $DOS_NUM $STA_NUM and SAI_DATE = '$SAI_DATE'
                                                    and SAI_DATE <= '$SAI_DATE' $AG_NUMsql
                                                    order by SAI_NUMSAISON DESC LIMIT 0,1) ";
        }

        if ($SAI_NUMSAISON) {
            $SAI_NUMSAISONsql = " and SAI_NUMSAISON = $SAI_NUMSAISON";
        }

        $sql = "select * from saison $join where 1 $STA_NUM $SAI_NUMSAISONsql $SAI_DATEsql $DOS_NUM $AG_NUMsql order by SAI_DATE";

        Database::query($sql);

        $Return = [];
        $PremDate = true;
        $MaPremiereDate = $MaDerniereDate = null;

        while ($ln = Database::fetchArray()) {
            $DateCourante = date("Y-m-00", strtotime(str_replace("-00", "-01", $ln["SAI_DATE"])));

            if ($PremDate) {
                $MaPremiereDate = $DateCourante;
                $PremDate = false;
            }

            $ln["SAI_CLE0"] = 1;
            $Return[$ln["SAI_DATE"]] = $ln;
            $MaDerniereDate = $DateCourante;
        }

        if (!$PlageBase) {
            if ($MaDerniereDate > StringHelper::DatePlus($MaPremiereDate, array("dateformat" => "Y-m-00", "moisplus" => 11))) {
                $MaDerniereDate = StringHelper::DatePlus($MaPremiereDate, array("moisplus" => 11));
            }

            $PlageBase["DateDeb"] = $MaPremiereDate;
            $PlageBase["DateFin"] = $MaDerniereDate;
        }

        $SAI_NUMSAISONsql .= " and SAI_DATE BETWEEN '" . $PlageBase["DateDeb"] . "' and  '" . $PlageBase["DateFin"] . "' ";

        // Mise à jour du paramètre passé par référence
        //SAI_NUMSAISON sert à sélectionner le bon tableau de clef suivant la période recherchée
        $sql = "select count(*) as SAI_CLE0, sum(SAI_CLE1) as SAI_CLE1, sum(SAI_CLE2) as SAI_CLE2, sum(SAI_CLE3) as SAI_CLE3
                from saison $join
                where 1 $STA_NUM $SAI_NUMSAISONsql $DOS_NUM $AG_NUMsql";

        Database::query($sql);
        $MesSum = Database::fetchArray();

        return $Return;
    }

    static function setSaison($MesSaison, $DOS_NUM, $SAI_NUMSAISON = false)
    {
        $SAI_NUMSAISONDef = $SAI_NUMSAISONInsert = '';

        if ($SAI_NUMSAISON) {
            $SAI_NUMSAISONDef = ", `SAI_NUMSAISON`";
            $SAI_NUMSAISONInsert = ", '" . $SAI_NUMSAISON . "'";
        }

        $sql = "DELETE FROM saison WHERE DOS_NUM = '$DOS_NUM' AND AG_NUM = 0 AND SAI_NUMSAISON = '$SAI_NUMSAISON'";
        Database::query($sql);

        $sql = "insert into saison (`DOS_NUM`, `SAI_DATE`,`SAI_CLE1`, `SAI_CLE2`, `SAI_CLE3`,`AG_NUM`$SAI_NUMSAISONDef) VALUES ";

        $values = [];
        foreach ($MesSaison as $Periode => $ligne) {
            $values[] = "('" . $DOS_NUM . "', '" . $Periode . "', '" . $ligne["SAI_CLE1"] . "', '" . $ligne["SAI_CLE2"] . "', '" . $ligne["SAI_CLE3"] . "', '0'$SAI_NUMSAISONInsert)";
        }

        Database::query($sql . implode(',', $values));
    }

    static function getTotalCharges($DOS_NUM, $Periode, $N1 = false, $MoisFin = null): array
    {
        if ($N1) {
            if ($MoisFin) {
                $WherePeriode = "and `BAL_MOIS` >= '$Periode' and `BAL_MOIS` <= '$MoisFin'";
            } else {
                $WherePeriode = "and `BAL_MOIS` = '$Periode'";
            }
            
            $sql = "select sum(BAL_BALANCE) as Montant
                    from balance
                        join comptes on comptes.code_compte = balance.codeCompte
                        join comptePoste on comptePoste.codePoste = comptes.codePoste
                    where balance.`DOS_NUM` = '$DOS_NUM'  $WherePeriode
                        and comptePoste.`Type` = 'charges' and comptePoste.`codePoste` <> '661' ";
        } else {
            $sql = "select sum(BAL_CUMUL) as Montant
                    from balance
                        join comptes on comptes.code_compte = balance.codeCompte
                        join comptePoste on comptePoste.codePoste = comptes.codePoste
                    where balance.`DOS_NUM` = '$DOS_NUM'  and `BAL_MOIS` = '$Periode'
                        and comptePoste.`Type` = 'charges' and comptePoste.`codePoste` <> '661' ";
        }

        Database::query($sql);
        $Return = [];
        
        while ($ln = Database::fetchArray()) {
            $Return = $ln;
        }
        
        return $Return;
    }

    static function getTotalProduits($DOS_NUM, $codeStation, $Periode, $N1 = false, $MoisFin = null)
    {
        if ($N1) {
            if ($MoisFin) {
                $WherePeriode = "and `BAL_MOIS` >= '$Periode' and `BAL_MOIS` <= '$MoisFin'";
            } else {
                $WherePeriode = "and `BAL_MOIS` = '$Periode'";
            }
            
            $sql = "select sum(BAL_BALANCE) as Montant
                    from balance
                    join comptes on comptes.code_compte = balance.codeCompte
                    join comptePoste on comptePoste.codePoste = comptes.codePoste
                    where balance.`DOS_NUM` = '$DOS_NUM' and balance.`codeStation` = '$codeStation'  $WherePeriode
                        and comptePoste.`Type` = 'produits' and comptes.`Type` != 'achat'";
        } else {
            $sql = "select sum(BAL_CUMUL) as Montant
                    from balance
                    join comptes on comptes.code_compte = balance.codeCompte
                    join comptePoste on comptePoste.codePoste = comptes.codePoste
                    where balance.`DOS_NUM` = '$DOS_NUM' and balance.`codeStation` = '$codeStation' and `BAL_MOIS` = '$Periode'
                        and comptePoste.`Type` = 'produits' and comptes.`Type` != 'achat'";
        }

        Database::query($sql);
        return Database::fetchArray();
    }

    static function SuppBalance($STA_NUM, $DOS_NUM)
    {
        $sql = "select MAX(BALI_MOIS) as MaxMois from balanceimport where DOS_NUM = $DOS_NUM";
        Database::query($sql);
        $ligne = Database::fetchArray();

        $Mois = $ligne["MaxMois"];

        if ($Mois == 0) {
            return;
        }

        $sql = "delete from balance where DOS_NUM = '$DOS_NUM' and codeStation = '$STA_NUM' and BAL_MOIS = '$Mois' ";
        Database::query($sql);

        $sql = "delete from balanceimport where DOS_NUM = '$DOS_NUM' and BALI_MOIS= '$Mois'";
        Database::query($sql);

        $sql = "delete from BenchProCharge where DOS_NUM = '$DOS_NUM' and STA_NUM = '$STA_NUM' and BPC_MOIS = '$Mois'";
        Database::query($sql);

        $sql = "delete from comptepostedetail where DOS_NUM = '$DOS_NUM' and Mois = '$Mois' ";
        Database::query($sql);

        $sql = "delete from resultatposte where DOS_NUM = '$DOS_NUM' and Periode = '$Mois'";
        Database::query($sql);

        $NouvMois = StringHelper::DatePlus(str_replace('-00', '-01', $Mois), array('dateformat' => 'Y-m-00', 'moisplus' => -1));

        if (strtotime(str_replace('-00', '-01', $NouvMois)) >= strtotime(date('Y-m-01', strtotime($_SESSION['station_DOS_DEBEX'])))) {
            $sql = "update station set STA_DERNBAL = '$NouvMois' where STA_NUM = '$STA_NUM'";
            Database::query($sql);
            $_SESSION["station_STA_DERNBAL"] = $NouvMois;
            $_SESSION['MoisVoulu'] = $NouvMois;
        }
    }
}
