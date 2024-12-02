<?php

use Helpers\StringHelper;
use htmlClasses\TableV2;

if(!isset($EntetePiedFalse) || !$EntetePiedFalse)
{
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
    <link rel="stylesheet" href="../style.css" type="text/css" media="screen">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>Pr&eacute;visionnel</title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
</head>

<body>
<?php
include "../include/entete.inc.php";
}//EntetPied
?>

<center>
    <?php
    if (isset($_SESSION["agip_AG_NUM"]) && $_SESSION["agip_AG_NUM"]) {
        if (!isset($Actif) || !$Actif) {
            $TitleTable = "CRP";
        } else {
            $TitleTable = "CRP actif";
        }
    } else {
        $TitleTable = "CRP DU MOIS";
    }

    if (isset($_SESSION["inLIE_NUM_station_STA_SARL"]) && $_SESSION["inLIE_NUM_station_STA_SARL"]) {
        $TitleTable .= " ( Soci&eacute;t&eacute; : " . $_SESSION["inLIE_NUM_station_STA_SARL"] . " - " . StringHelper::MySql2DateFr($_SESSION["MoisHisto"]) . " ) ";
    }
    ?>
</center>

<center>
    <?php $Type = "Produits"; ?>
    <div style="text-align: left;width: 800px;">

        <table dir="IMP_PDF;TITLETABLE:PREVISIONNEL <?php echo $Type; ?>;ORIENTATION:LANDSCAPE;FONT_SIZE:8;BORDER:1;FITHEIGHT:1;FREEZEPLAN:B5;"
               border="0" class="tabBalance" id="tab_PrevProd">
            <thead>
            <?php echo EnteteTab::HTML_EnteteTab(array("Intitule" => "CRP PRODUITS DU MOIS", "colspanCenter" => 1, "colspanRight" => 4)); ?>
            <tr class="EnteteTab sticky">
                <td width="300" class="tdfixe" height="65">
                    <div style="width: 360px;height:0px;"></div><?php echo $Type; ?></td>
                <td width="70" class="tdfixe" height="65">
                    <div style="width: 50px;height:0px;"></div>
                    Clef
                </td>
                <td width="70" class="tdfixe" height="65">
                    <div style="width: 110px;height:0px;"></div>
                    Annuel
                </td>
                <td width="70" class="tdfixe" height="65">
                    <div style="width: 75px;height:0px;"></div>
                    Montant <br/>
                    du mois
                </td>

                <?php if ($Type == "Produits") { ?>
                    <td width="70" class="tdfixe" height="65">
                        <div style="width: 110px;height:0px;"></div>
                        Taux de marge <br/>et<br/>
                        Total marge annuel <br/>de la famille
                    </td>
                    <td width="70" class="tdfixe" height="65">
                        <div style="width: 75px;height:0px;"></div>
                        Marge Mois
                    </td>
                <?php } ?>
            </tr>
            </thead>
            <tbody>
            <?php
            $NbCols = 5;

            if ($Type == "Produits") {
                $NbCols++;
            }

            foreach ($MesLignesProduits as $codecompte => $UneLigne) {
                if (isset($cssligne) && $cssligne == 'bdligneimpaireTD') {
                    $cssligne = 'bdlignepaireTD';
                } else {
                    $cssligne = 'bdligneimpaireTD';
                }

                if (stristr($codecompte, "TOTAL")) {
                    $cssligne1 = "lntotal";
                } elseif (stristr($codecompte, "TITRE")) {
                    $cssligne1 = "EnteteTab";
                } else {
                    $cssligne1 = $cssligne;
                }

                echo table::getLine($UneLigne, array("class" => $cssligne1), $NbCols);
            }
            ?>
            </tbody>
        </table>

        <br/>
        <div class="breakafter">&nbsp;</div>
        <?php
        if (isset($Imprimer) && $Imprimer) {
            ?>
            <center>
                <div class="titresection">
                    <?php
                    if (isset($_SESSION["agip_AG_NUM"]) && $_SESSION["agip_AG_NUM"]) {
                        if (!isset($Actif) || !$Actif) {
                            echo "CRP";
                        } else {
                            echo "CRP actif sur la soci&eacute;t&eacute; : ";
                        }
                    } else {
                        echo "PREVISIONNEL";
                    }
                    ?>
                </div>
            </center>
            <?php
        }
        ?>
        <?php $Type = "Charges"; ?>
        <table dir="IMP_PDF;ORIENTATION:LANDSCAPE;TITLETABLE:PREVISIONNEL <?php echo $Type; ?>;FONT_SIZE:9;EXTANDTABLE:0;BORDER:1;FITHEIGHT:1;FREEZEPLAN:B5;"
               border="0" class="tabBalance" id="tab_PrevCharges">
            <thead>
            <?php echo EnteteTab::HTML_EnteteTab(array("Intitule" => "CRP CHARGES DU MOIS", "colspanCenter" => 1, "colspanRight" => 4)); ?>
            <tr class="EnteteTab sticky">
                <td width="300" class="tdfixe" height="40">
                    <div style="width: 360px;height:0px;"></div><?php echo $Type; ?></td>

                <td width="70" class="tdfixe" height="40">
                    <div style="width: 50px;height:0px;"></div>
                    Clef
                </td>
                <td width="70" class="tdfixe" height="40">
                    <div style="width: 110px;height:0px;"></div>
                    Annuel
                </td>

                <td width="70" class="tdfixe" height="40">
                    <div style="width: 75px;height:0px;"></div>
                    Montant <br/>
                    du mois
                </td>
                <td width="70" class="tdfixe" height="65">
                    <div style="width: 110px;height:0px;"></div>&nbsp;
                </td>
                <td width="70" class="tdfixe" height="65">
                    <div style="width: 75px;height:0px;"></div>&nbsp;
                </td>

            </tr>
            </thead>
            <tbody>
            <?php
            $NbCols = 6;

            if ($Type == "Produits") {
                $NbCols++;
            }

            foreach ($MesLignesCharges as $codecompte => $UneLigne) {
                if (isset($cssligne) && $cssligne == 'bdligneimpaireTD') {
                    $cssligne = 'bdlignepaireTD';
                } else {
                    $cssligne = 'bdligneimpaireTD';
                }

                if (stristr($codecompte, "TOTAL")) {
                    $cssligne1 = "lntotal";
                } elseif (stristr($codecompte, "TITRE")) {
                    $cssligne1 = "EnteteTab";
                } else {
                    $cssligne1 = $cssligne;
                }

                echo table::getLine($UneLigne, array("class" => $cssligne1), $NbCols);
            }
            ?>
            </tbody>
        </table>
    </div>
    <br/>


    <?php
    if (!isset($EntetePiedFalse) || !$EntetePiedFalse)
    {
    ?>

    <?php
    include("../include/pied.inc.php");
    ?>
</body>
</html>
<?php
}//EntetePied
