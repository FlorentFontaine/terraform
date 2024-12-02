<?php

use Classes\DB\Database;

require_once __DIR__ . '/../dbClasses/AccesDonnees.php';
require_once __DIR__ . '/../GESTION/facturation/facturation.class.php';

class Benchmark
{
    static $TableNumber = 0;

    public static function getMinMaxPosteStation($MoisDeb, $MoisFin, $BPC_NUM, $BPC_MARGE, $optRestri = null): array
    {
        $BPC_NUM = array_filter($BPC_NUM, 'is_numeric');

        $WherePlus = Benchmark::getStationInclude($MoisDeb, $MoisFin, false, $optRestri);
        
        $sql = "SELECT BEN_NUM, SUM(BPC_REA) AS BPC_REA, BPC_MARGE, tmp_BenchLieuIn.DOS_NUM, lieu.LIE_NUM, LIE_NOM
                FROM tmp_BenchLieuIn
                    LEFT JOIN compteposte ON compteposte.codePoste = tmp_BenchLieuIn.BEN_NUM
                    JOIN dossier ON tmp_BenchLieuIn.DOS_NUM = dossier.DOS_NUM
                    JOIN station ON station.STA_NUM = dossier.STA_NUM
                    JOIN lieu ON lieu.LIE_NUM = station.LIE_NUM
                WHERE BPC_MOIS BETWEEN '$MoisDeb' AND '$MoisFin'
                    AND BEN_NUM IN (" . implode(', ', $BPC_NUM) . ")
                    AND BPC_MARGE = '$BPC_MARGE'
                $WherePlus
                GROUP BY BEN_NUM, lieu.LIE_NUM";
        
        Database::query($sql);
        
        $ReturnMin = $ReturnMax = array();

        while($ln = Database::fetchArray()) {
            if (
                !isset($ReturnMin[$ln["BEN_NUM"]."||#||".$ln["BPC_MARGE"]]['BPC_REA'])
                || ($ln['BPC_REA'] < $ReturnMin[$ln["BEN_NUM"]."||#||".$ln["BPC_MARGE"]]['BPC_REA'])
            ) {
                $ReturnMin[$ln["BEN_NUM"]."||#||".$ln["BPC_MARGE"]] = $ln;
            }

            if (
                !isset($ReturnMax[$ln["BEN_NUM"]."||#||".$ln["BPC_MARGE"]]['BPC_REA'])
                || ($ln['BPC_REA'] > $ReturnMax[$ln["BEN_NUM"]."||#||".$ln["BPC_MARGE"]]['BPC_REA'])
            ) {
                $ReturnMax[$ln["BEN_NUM"]."||#||".$ln["BPC_MARGE"]] = $ln;
            }
        }
        
        return [$ReturnMin, $ReturnMax];
    }

    public static function getMoyPosteStation($MoisDeb, $MoisFin, $BPC_NUM, $BPC_MARGE, $optRestri = null): array
    {
        $BPC_NUM = array_filter($BPC_NUM, 'is_numeric');

        $WherePlus = Benchmark::getStationInclude($MoisDeb, $MoisFin, false, $optRestri);

        $sql = "SELECT AVG(messum.BPC_REA) AS BPC_MOY, Famille, SsFamille, BEN_NUM, BPC_MARGE
                FROM (
                    select BEN_NUM, SUM(BPC_REA) AS BPC_REA, BPC_MARGE, Famille, SsFamille
                    FROM tmp_BenchLieuIn
                        LEFT JOIN compteposte ON compteposte.codePoste = tmp_BenchLieuIn.BEN_NUM
                        JOIN dossier ON tmp_BenchLieuIn.DOS_NUM = dossier.DOS_NUM
                        JOIN station ON station.STA_NUM = dossier.STA_NUM
                        JOIN lieu ON lieu.LIE_NUM = station.LIE_NUM
                    WHERE BPC_MOIS BETWEEN  '$MoisDeb' AND '$MoisFin'
                        AND BEN_NUM IN (" . implode(', ', $BPC_NUM) . ")
                        AND BPC_MARGE = '$BPC_MARGE'
                        $WherePlus
                    GROUP BY BEN_NUM, lieu.LIE_NUM
                ) messum
                GROUP BY BEN_NUM";

        Database::query($sql);

        $Return = array();

        while($ln = Database::fetchArray()) {
            $Return[$ln['BEN_NUM']."||#||".$ln['BPC_MARGE']] = $ln;
        }

        return $Return;
    }

    public static function getStationInclude($MoisDeb, $MoisFin, $formated = false, &$optRestri = null)
    {
        if(
            (isset($optRestri["rechargerBench"]) && $optRestri["rechargerBench"])
            || (!self::check_value_in_tmp_BenchLieuIn() && isset($optRestri["valuesInBench"]) && $optRestri["valuesInBench"])
        ) {
            self::init_tmp_BenchLieuIn($MoisDeb, $MoisFin, $optRestri);
            $optRestri["rechargerBench"] = false;
            $_SESSION['BenchUpdated'] = true;
        }

        if($formated) {
            $Hav = "HAVING MAX(BPC_MOIS) >= '$MoisDeb'";
            if(!isset($optRestri["NoStrict"]) || !$optRestri["NoStrict"]) {
                $Hav = " AND MIN(BPC_MOIS) <= '$MoisDeb'";
            }

            $sql = "select station.STA_NUM, STA_SARL, LIE_NOM, DOS_DEBEX, DOS_FINEX, MAX(BPC_MOIS), MIN(BPC_MOIS)
                    from tmp_BenchLieu
                    join lieu on lieu.LIE_NUM = tmp_BenchLieu.LIE_NUM
                    join station on station.LIE_NUM = lieu.LIE_NUM
                    join tmp_BenchLieuIn on tmp_BenchLieu.LIE_NUM = tmp_BenchLieuIn.LIE_NUM
                        and station.STA_NUM = tmp_BenchLieuIn.STA_NUM
                    join dossier on dossier.STA_NUM = station.STA_NUM
                    where tmp_BenchLieu.USER_TYPE = '".$_SESSION["User"]->NomTableUser."'
                    and tmp_BenchLieu.USER_ID = '".$_SESSION["User"]->NumTableIdUser."'";
            $sql .= Benchmark::formatOptRestri($optRestri,false);
            $sql .= " GROUP BY STA_NUM, DOS_FINEX ORDER BY sta_sarl ASC, dos_finex ASC ";

            Database::query($sql);

            $Returns = array();

            while ($ln = Database::fetchArray()) {
                $Returns[$ln['STA_NUM']] = $ln;
            }

            return $Returns;
        }

        return " and USER_TYPE = '".$_SESSION["User"]->NomTableUser."' and USER_ID = '".$_SESSION["User"]->NumTableIdUser."'";
    }

    public static function check_value_in_tmp_BenchLieuIn(): bool
    {
        if (isset($_SESSION['CheckValueInBench'])) {
            return $_SESSION['CheckValueInBench'];
        }

        $sql = "select DISTINCT USER_TYPE
                from tmp_BenchLieuIn
                where 1
                AND USER_TYPE = '".$_SESSION["User"]->NomTableUser."'
                AND USER_ID = '".$_SESSION["User"]->NumTableIdUser."' ";

        Database::query($sql);
        $tmp_BenchLieuIn = Database::fetchArray();

        $sql2 = "select DISTINCT USER_TYPE
                from tmp_BenchLieu
                where 1
                AND USER_TYPE = '".$_SESSION["User"]->NomTableUser."'
                AND USER_ID = '".$_SESSION["User"]->NumTableIdUser."'";

        Database::query($sql2);
        $tmp_BenchLieu = Database::fetchArray();


        if($tmp_BenchLieuIn && $tmp_BenchLieu) {
            $_SESSION['CheckValueInBench'] = true;
        }

        if(!$tmp_BenchLieuIn && !$tmp_BenchLieu) {
            $_SESSION['CheckValueInBench'] = false;
        }

        if((!$tmp_BenchLieuIn && $tmp_BenchLieu) || ($tmp_BenchLieuIn && !$tmp_BenchLieu)) {
            self::reset_tmp_BenchLieuIn();
            $_SESSION['CheckValueInBench'] = false;
        }

        $_SESSION['CheckValueInBench'] = false;

        return $_SESSION['CheckValueInBench'];
    }

    public static function reset_tmp_BenchLieuIn()
    {
        $sql = "delete from tmp_BenchLieuIn where USER_TYPE = '".$_SESSION["User"]->NomTableUser."' and USER_ID = '".$_SESSION["User"]->NumTableIdUser."'";
        Database::query($sql);

        $sqlDel = "delete from tmp_BenchLieu where USER_TYPE = '".$_SESSION["User"]->NomTableUser."' and USER_ID = '".$_SESSION["User"]->NumTableIdUser."'";
        Database::query($sqlDel);
    }

    public static function init_tmp_BenchLieuIn($MoisDeb, $MoisFin, &$optRestri = null)
    {
        $MonLieu = null;
        if (isset($optRestri["LIE_NUM"]) && $optRestri["LIE_NUM"]) {
            $MonLieu = $optRestri["LIE_NUM"];
        }

        $optRestri["LIE_NUM"] = false;
        $WherePlus = Benchmark::formatOptRestri($optRestri);

        if ($MonLieu) {
            $optRestri["LIE_NUM"] = $MonLieu;
        }

        self::reset_tmp_BenchLieuIn();

        if(isset($optRestri["NoStrict"]) && $optRestri["NoStrict"]) {
            $Hav = "HAVING  MAX(BPC_MOIS) >= '$MoisDeb'";
        } else {
            $Hav = "HAVING MAX(BPC_MOIS) >= '$MoisFin' and MIN(BPC_MOIS) <= '$MoisDeb'";
        }

        $sql = "insert into tmp_BenchLieu (LIE_NUM,USER_TYPE,USER_ID)
                SELECT DISTINCT lieu.LIE_NUM,'".$_SESSION["User"]->NomTableUser."','".$_SESSION["User"]->NumTableIdUser."'
                FROM lieu join station on station.LIE_NUM = lieu.LIE_NUM
                join dossier on station.STA_NUM = dossier.STA_NUM
                join BenchProCharge on BenchProCharge.DOS_NUM = dossier.DOS_NUM
                WHERE 1 $WherePlus
                group by lieu.LIE_NUM
                $Hav";

        Database::query($sql);

        if(!Database::countRow()) {
            $optRestri["valuesInBench"] = true;
        }

        if($MonLieu) {
            $sql = "insert into tmp_BenchLieu (LIE_NUM,USER_TYPE,USER_ID) values ('".$MonLieu."','".$_SESSION["User"]->NomTableUser."','".$_SESSION["User"]->NumTableIdUser."')";
            Database::query($sql);
        }

        if(isset($optRestri["only_tmp_BenchLieu"]) && $optRestri["only_tmp_BenchLieu"]) {
            return;
        }

        $sqlMesBench = "insert into tmp_BenchLieuIn
                            (`LIE_NUM` , `USER_TYPE` , `USER_ID` , `BEN_NUM` , `BPC_MOIS` , `DOS_NUM` , `BALI_TYPE` , `BPC_REA` ,
                            `BPC_PREV` , `BPC_PREVAGIP` , `BPC_N1` , `BPC_TYPE` , `BPC_MARGE` , `STA_NUM` , `BPC_NOTINCONSO` )

                            SELECT lieu.LIE_NUM , '".$_SESSION["User"]->NomTableUser."', '".$_SESSION["User"]->NumTableIdUser."',
                            BEN_NUM, BPC_MOIS, BenchProCharge.DOS_NUM, BenchProCharge.BALI_TYPE, BPC_REA, BPC_PREV ,
                            BPC_PREVAGIP, BPC_N1, BPC_TYPE, BPC_MARGE, BenchProCharge.STA_NUM, BPC_NOTINCONSO
                            FROM lieu
                            join station on station.LIE_NUM = lieu.LIE_NUM
                            join dossier on station.STA_NUM = dossier.STA_NUM
                            join BenchProCharge on BenchProCharge.DOS_NUM = dossier.DOS_NUM
                            join tmp_BenchLieu on lieu.LIE_NUM = tmp_BenchLieu.LIE_NUM 
                                and USER_TYPE = '".$_SESSION["User"]->NomTableUser."' and USER_ID = '".$_SESSION["User"]->NumTableIdUser."'
                            WHERE 1
                            $WherePlus
                            group by LIE_NUM,BEN_NUM,BPC_MOIS,DOS_NUM,BPC_MARGE";

        Database::query($sqlMesBench);
    }

    public static function formatOptRestri($optRestri = null,$AvecLieu = true): string
    {
        $WherePlus = " and BPC_NOTINCONSO = 0 ";

        if($optRestri) {
            if($optRestri["LIE_NUM"] && $AvecLieu) {
                $WherePlus .= " and lieu.LIE_NUM = '".$optRestri["LIE_NUM"]."' ";
            }

            if(isset($optRestri["codeChefSecteur"]) && $optRestri["codeChefSecteur"]) {
                $WherePlus .= " and lieu.codeChefSecteur = '".$optRestri["codeChefSecteur"]."' ";
            }

            for($i=1;$i<=10;$i++) {
                if(isset($optRestri["LIE_TYPO$i"]) && $optRestri["LIE_TYPO$i"]) {
                    $WherePlus .= " and LIE_TYPO$i = '".$optRestri["LIE_TYPO$i"]."' ";
                }
            }
        }

        return $WherePlus;
    }

    public static function getBench($MoisDeb, $MoisFin, $Type, $LIE_NUM = null, $SimplRea = null, $optRestri = null, $codePoste = false): array
    {
        $selectPlus = "";

        if($LIE_NUM) {
            $WherePlus = " AND lieu.LIE_NUM = '$LIE_NUM'
                            AND tmp_BenchLieuIn.USER_TYPE = '".$_SESSION["User"]->NomTableUser."'
                            AND tmp_BenchLieuIn.USER_ID = '".$_SESSION["User"]->NumTableIdUser."' ";
        } else {
            $WherePlus = Benchmark::getStationInclude($MoisDeb,$MoisFin,false,$optRestri);
        }

        if(!$SimplRea) {
//            if($optRestri["TypePrev"] != "AGIP") {
//                $FaireSum = "BPC_PREV";
//            } else {
//                $FaireSum = "BPC_PREVAGIP";
//            }
//            $selectPlus .= " ,sum($FaireSum) as BPC_PREV,sum(BPC_N1) as BPC_N1 ";

            $selectPlus .= " ,sum(BPC_PREVAGIP) as BPC_PREV,sum(BPC_N1) as BPC_N1 ";
        }

        if($Type == "Charges") {
            $TypeSql = 1;
        } else {
            $TypeSql = 2;
        }

        if($codePoste) {
            $WherePlus .= " and compteposte.codePoste = '$codePoste' ";
        }

        $sql = "select Famille,SsFamille,BEN_NUM,BPC_MARGE,sum(BPC_REA) as BPC_REA $selectPlus
                from tmp_BenchLieuIn
                left join compteposte on compteposte.codePoste = tmp_BenchLieuIn.BEN_NUM
                join dossier on tmp_BenchLieuIn.DOS_NUM = dossier.DOS_NUM
                join station on station.STA_NUM = dossier.STA_NUM
                join lieu on lieu.LIE_NUM = station.LIE_NUM
                where 1
                and  BPC_MOIS >= '$MoisDeb'
                and BPC_MOIS <= '$MoisFin'
                and BPC_TYPE='$TypeSql' $WherePlus
                group by BEN_NUM, BPC_MARGE";

        Database::query($sql);

        $Return = array();

        while($ln = Database::fetchArray()) {
            $Return[$ln["BEN_NUM"]."||#||".$ln["BPC_MARGE"]] = $ln;
        }

        return $Return;
    }

    public static function getMaxMinDate(&$Max, &$Min, $LIE_NUM = null, $STA_NUM = null, $DOS_NUM = null)
    {
        $WherePlus = "";

        if($LIE_NUM) {
            $WherePlus .= " and lieu.LIE_NUM = '".$LIE_NUM."' ";
        }

        if($STA_NUM) {
            $WherePlus .= " and station.STA_NUM = '".$STA_NUM."' ";
        }

        if($DOS_NUM) {
            $WherePlus .= " and BenchProCharge.DOS_NUM = '".$DOS_NUM."' ";
        }

        $sql = "select MAX(BPC_MOIS) as max, MIN(BPC_MOIS) as min
                from BenchProCharge
                join station on station.STA_NUM = BenchProCharge.STA_NUM
                join lieu on lieu.LIE_NUM = station.LIE_NUM
                where 1 $WherePlus";

        Database::query($sql);
        $ln = Database::fetchArray();

        $Max = $ln["max"];
        $Min = $ln["min"];
    }

    static function display_EnteteTab($TitleTable, $etude, $Intitule = null)
    {
        self::$TableNumber++;

        $FREEZPLAN = 2;
        $OptionPlus = "FITHEIGHT:1;";
        ?>

        <table dir="IMP_PDF;TITLETABLE:<?php echo $TitleTable." - ".$Intitule; ?>;FREEZEPLAN:B<?php echo $FREEZPLAN; ?>;<?php echo $OptionPlus; ?>"
            style="width:100%" class="tabBalance" bordercolordark="#000000" bordercolorlight="#000000" align="center"
            id="tab_Mensuel<?php echo self::$TableNumber ?>">
       <?php if($etude == "consolidation"){  ?>
            <thead>
                <tr class="EnteteTab">
                <td class="tdfixe" width="300" rowspan="2" style="width: 350px"><div class="div300"></div>Produits</td>
                <td colspan="3">
                Consolidation
                </td>
                <td class="colvide"></td>
                <td colspan="2" width="150">Comparatif</td>
                </tr>
                <tr class="EnteteTab sticky-up">
                <td class="tdfixe" width="60"><div class="div95"></div>R&eacute;alis&eacute;</td>
                <td class="tdfixe" width="60"><div class="div95"></div>Pr&eacute;vu</td>
                <td class="tdfixe" width="60"><div class="div95"></div>N-1</td>
                <td class="colvide tdfixe" width="2"></td>
                <td class="tdfixe" width="60"><div class="div95"></div>R&eacute;a - Pr&eacute;v</td>
                <td class="tdfixe" width="60"><div class="div95"></div>R&eacute;a - (N-1)</td>
                </tr>
            </thead>
        <?php } else {  ?>
            <thead>
                <tr class="EnteteTab">
                <td class="tdfixe"  width="300" rowspan="2" style="width: 350px"><div class="div300"></div>Produits</td>
                <td ></td>
                <td class="colvide" width="2"></td>
                <td colspan="3">
                Benchmark
                </td>
                <td class="colvide" width="2"></td>
                <td ></td>
                </tr>
                <tr class="EnteteTab sticky-up">
                <td class="tdfixe" width="60"><div class="div95"></div>Comparaison</td>
                <td class="colvide tdfixe" width="2"></td>
                <td class="tdfixe" width="60"><div class="div95"></div>-</td>
                <td class="tdfixe" width="60"><div class="div95"></div>Moyenne</td>
                <td class="tdfixe" width="60"><div class="div95"></div>+</td>
                <td class="colvide tdfixe" width="2"></td>
                <td class="tdfixe" width="60"><div class="div95"></div>Comparaison</br> - Moyenne</td>
                </tr>
            </thead>
        <?php }
    }
}
