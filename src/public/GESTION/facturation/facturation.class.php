<?php

use Classes\DB\Database;
use Helpers\StringHelper;

class facturation
{
    static function echoLines($DateDebut, $DateFin, $Imprimer)
    {
        $MesLignes = db_facturation::getFacturation($DateDebut, $DateFin);

        if (empty($MesLignes)) {
            $MesLignes = array();
        }

        $Prem = true;
        $colspanCabinet = 8;

        if (isset($_POST["display_com1"]) && $_POST["display_com1"]) {
            $colspanCabinet++;
        }

        if (isset($_POST["display_com2"]) && $_POST["display_com2"]) {
            $colspanCabinet++;
        }

        foreach ($MesLignes as $LignesStation) {
            $initCab = true;
            $nbLines = 0;
            foreach ($LignesStation as $LigneMoisTraite) {
                foreach ($LigneMoisTraite as $LnComplet) {
                    $nbLines++;

                    if ($initCab) {
                        $classTr = "";
                        if (!$Prem) {
                            echo "<div class='breakafter'></div>";
                        }

                        if ($Imprimer) {
                            echo "<b><u>Facturation My Report du " . StringHelper::Mysql2DateFr($DateDebut) . " au " . StringHelper::Mysql2DateFr($DateFin) . "</u></b><br/><br/>";
                        }

                        echo "<table align='center' style='width:0' id='tableFacturation' bordercolordark='#FFFFFF' bordercolorlight='#C4C2C2' >";
                        echo "<tr class='bdListeEnteteTD'><td colspan='" . $colspanCabinet . "'>" . $LnComplet["CAB_NOM"] . "</td></tr>";
                        echo "<tr class='bdListeEnteteTD'>
                            <td ><div class='div70'></div>Nb</td>
                            <td ><div class='div70'></div>Code<br>PDV</td>
                            <td ><div class='div200'></div>PDV</td>
                            <td ><div class='div70'></div>Code Soci&eacute;t&eacute;</td>
                            <td ><div class='div200'></div>Soci&eacute;t&eacute;</td>
                            <td ><div class='div70'></div>Mois</td>
                            <td ><div class='div70'></div>Trait&eacute; le</td>
                            <td ><div class='div140'></div>Trait&eacute; par</td>";

                        if (isset($_POST["display_com1"]) && $_POST["display_com1"]) {
                            echo "<td ><div class='div200'></div>Commentaire 1</td>";
                        }

                        if (isset($_POST["display_com2"]) && $_POST["display_com2"]) {
                            echo "<td ><div class='div200'></div>Commentaire 2</td>";
                        }

                        echo "</tr>";

                        $initCab = false;
                    }

                    if (isset($classTr) && $classTr !== "bdligneimpaireTD") {
                        $classTr = "bdligneimpaireTD";
                    } else {
                        $classTr = "bdlignepaireTD";
                    }

                    echo "<tr class='" . $classTr . "'>
                        <td align='center'>$nbLines</td>
                        <td align='center'>" . $LnComplet["LIE_CODE"] . "</td>
                        <td align='left'>" . $LnComplet["LIE_NOM"] . "</td>
                        <td align='center'>" . $LnComplet["STA_CODECLIENT"] . "</td>
                        <td align='left'>" . $LnComplet["STA_SARL"] . "</td>
                        <td align='center'>" . StringHelper::Mysql2DateFr($LnComplet["FACT_MOISDOSSIER"]) . "</td>
                        <td align='center'>" . substr(StringHelper::Mysql2DateFr($LnComplet["FACT_DATE"]), 0, 10) . "</td>
                        <td align='left'>" . $LnComplet["CC_NOM"] . "</td>";

                    if (isset($_POST["display_com1"]) && $_POST["display_com1"]) {
                        echo "<td align='left'>" . $LnComplet["STA_FACTURATION_COM1"] . "</td>";
                    }

                    if (isset($_POST["display_com2"]) && $_POST["display_com2"]) {
                        echo "<td align='left'>" . $LnComplet["STA_FACTURATION_COM2"] . "</td>";
                    }

                    echo "</tr>";
                }
            }

            echo "</table><br/><br/>";
            $Prem = false;
        }
    }

    static function AddDossier($DOS_NUM, $Mois, $CC_NUM)
    {
        db_facturation::AddDossier($DOS_NUM, $Mois, $CC_NUM);
    }

}

class db_facturation
{
    public static $db;

    static function getFacturation($DateDebut, $DateFin)
    {
        $sql = "select DISTINCT cabinet.CAB_NUM,station.STA_NUM,cabinet.CAB_NOM,lieu.LIE_CODE,lieu.LIE_NOM,station.STA_CODECLIENT,station.STA_SARL,FACT_MOISDOSSIER,FACT_DATE,facturation.CC_NOM,
            station.STA_FACTURATION_COM1, station.STA_FACTURATION_COM2
        from facturation
        join station on facturation.STA_NUM = station.STA_NUM
        join lieu on lieu.LIE_NUM = station.LIE_NUM
        join stationcc on stationcc.STA_NUM = station.STA_NUM
        join comptable on comptable.CC_NUM = stationcc.CC_NUM
        join cabinet on cabinet.CAB_NUM = comptable.CAB_NUM
        where 1 and FACT_DATE BETWEEN '$DateDebut 00:00:00' and '$DateFin 23:59:59' order by CAB_NOM,lieu.LIE_NOM, FACT_MOISDOSSIER
        ";

        Database::query($sql);

        $Return = array();

        while ($Ln = Database::fetchArray()) {
            $Return[$Ln["CAB_NUM"]][$Ln["STA_NUM"]][$Ln["FACT_MOISDOSSIER"]] = $Ln;
        }

        return $Return;
    }

    static function AddDossier($DOS_NUM, $Mois, $CC_NUM)
    {
        $sqlComptable = "select CC_NOM,CAB_NUM from comptable where CC_NUM = $CC_NUM";
        Database::query($sqlComptable);
        $LnComptable = Database::fetchArray();

        $sql = "select STA_NUM,DOS_NUM from dossier where DOS_NUM = $DOS_NUM ";
        Database::query($sql);
        $LnDossier = Database::fetchArray();

        $sql = "select BALI_TYPE from balanceimport where DOS_NUM = $DOS_NUM and BALI_MOIS = '$Mois'";
        Database::query($sql);
        $LnBal = Database::fetchArray();

        //V&eacute;rification que la ligne n'existe pas d&eacute;jà dans la table facturation
        $sql = "select FACT_NUM
                from facturation
                where 1
                and STA_NUM = '" . $LnDossier["STA_NUM"] . "'
                and DOS_NUM = '" . $LnDossier["DOS_NUM"] . "'
                and CAB_NUM = '" . $LnComptable["CAB_NUM"] . "'
                and FACT_MOISDOSSIER = '$Mois'
                and BALI_TYPE = '" . $LnBal["BALI_TYPE"] . "' ";

        Database::query($sql);
        $LnFact = Database::fetchArray();

        //Si la ligne facturation n'existe pas, elle est cr&eacute;&eacute;e
        if (!$LnFact) {
            $sqlFacturation = "insert into facturation (STA_NUM,DOS_NUM,CAB_NUM,CC_NOM,FACT_DATE,FACT_MOISDOSSIER,BALI_TYPE)
                VALUES (" . $LnDossier["STA_NUM"] . "," . $LnDossier["DOS_NUM"] . "," . $LnComptable["CAB_NUM"] . ",'" . $LnComptable["CC_NOM"] . "',NOW(),'$Mois','" . $LnBal["BALI_TYPE"] . "')";
            Database::query($sqlFacturation);
        }
    }

}
