<?php

if(!isset($EntetePiedFalse) || !$EntetePiedFalse)
{
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
    <link rel="stylesheet" href="../style.css" type="text/css" media="screen">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>CRP | MyReport</title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
</head>

<body>
<?php
include __DIR__ . "/../include/entete.inc.php";
?>
<script type="text/javascript" src="GestionCRP.function.js"></script>
<script type="text/javascript" src="GestionCRP.form.js"></script>
<?php
}//EntetPied
?>
<div id="maDIV_POPUP"></div>
<div id="maDIV_POPUP_INFO"></div>

<form method="post">
    <input type="hidden" name="CRP_NUM" id="CRP_NUM" value="<?php echo $_GET["CRP_NUM"]; ?>"/>
    <input type="hidden" name="STA_NUM" id="STA_NUM" value="<?php echo $_SESSION["station_STA_NUM"]; ?>"/>
    <input type="hidden" name="CRP_NB_MOIS" id="CRP_NB_MOIS" value="<?php echo $CRP["CRP_NB_MOIS"]; ?>"/>

    <input type="hidden" name="Enregistrement" value="1"/>
    <center>
        <table style="margin: 20px auto; width: 800px" border="0">
            <tr style="text-align: center;">
                <td style="width: 33%">
                    <a type="button" id="btn_retour" class="button-spring">
                        Retour &agrave; la liste des CRP
                    </a>
                </td>
                <td style="width: 33%">
                    <input type="submit" value="Enregistrer" name="enregistrer" class="button-spring"/>
                </td>
                <td style="width: 33%">
                    <?php if ($CRP_NUM_PREC) { ?>
                        <a type="button" id="copie_crp" class="button-spring">
                            Copier CRP pr&eacute;c.
                        </a>

                    <?php } elseif ($LAST_CRP_NUM == $CRP_NUM && !$CRP_NUM_PREC) { ?>
                        <a type="button" id="modif_date_CRP" class="button-spring">
                            Modifier les dates du CRP
                        </a>

                    <?php } ?>
                </td>
            </tr>
            <?php if ($LAST_CRP_NUM == $CRP_NUM && $CRP_NUM_PREC) { ?>
                <tr>
                    <td style="width: 33%"></td>
                    <td style="width: 33%"></td>
                    <td style="width: 33%">
                        <a type="button" id="modif_date_CRP" class="button-spring">
                            Modifier les dates
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </center>

    <center>
        <?php $Type = "Produits"; ?>
        <div style="text-align: left; margin: auto">
            <table dir="IMP_PDF;TITLETABLE:PREVISIONNEL <?php echo $Type; ?>;ORIENTATION:PORTRAIT;FONT_SIZE:9;EXTANDTABLE:1;BORDER:1;FITHEIGHT:1;FREEZEPLAN:<?php if ($Type == "Produits") {
                echo "H2";
            } else {
                echo "F2";
            } ?>;" border="0" class="tabBalance" id="tab_PrevProd">
                <thead>
                <?php echo EnteteTab::HTML_EnteteTab(array("Intitule" => $TitleTable, "colspanCenter" => 1, "colspanRight" => 4)); ?>
                <tr class="EnteteTab">
                    <td width="175" class="tdfixe" height="65">
                        <div style="width: 360px;height:0px;"></div><?php echo $Type; ?></td>
                    <td width="30" class="tdfixe" height="65">
                        <div style="width: 50px;height:0px;"></div>
                        Clef
                    </td>
                    <td width="50" class="tdfixe" height="65">
                        <div style="width: 110px;height:0px;"></div>
                        Valeur<br/>(valeurs sur <?= $CRP["CRP_NB_MOIS"] ?> mois)
                    </td>
                    <td width="50" class="tdfixe" height="65">
                        <div style="width: 75px;height:0px;"></div>
                        Valeur brute <br/>
                        mensuelle
                    </td>

                    <?php if ($Type == "Produits") { ?>
                        <td width="70" class="tdfixe" height="65">
                            <div style="width: 110px;height:0px;"></div>
                            Taux de marge <br/>et<br/>
                            Total marge annuel <br/>de la famille
                        </td>
                        <td width="50" class="tdfixe" height="65">
                            <div style="width: 75px;height:0px;"></div>
                            Marge Brute<br/>Mensuelle
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
                        if ($_SESSION["agip_AG_NUM"]) {
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
            <table dir="IMP_PDF;ORIENTATION:PORTRAIT;TITLETABLE:PREVISIONNEL <?php echo $Type; ?>;FONT_SIZE:9;EXTANDTABLE:0;BORDER:1;FITHEIGHT:1;FREEZEPLAN:F2;"
                   border="0" class="tabBalance" id="tab_PrevCharges">
                <thead>
                <tr class="EnteteTab">
                    <td width="180" class="tdfixe" height="40">
                        <div style="width: 360px;height:0px;"></div><?php echo $Type; ?></td>

                    <td width="30" class="tdfixe" height="40">
                        <div style="width: 50px;height:0px;"></div>
                        Clef
                    </td>
                    <td width="60" class="tdfixe" height="40">
                        <div style="width: 110px;height:0px;"></div>
                        Valeur<br/>(valeurs sur <?= $CRP["CRP_NB_MOIS"] ?> mois)
                    </td>

                    <td width="60" class="tdfixe" height="40">
                        <div style="width: 75px;height:0px;"></div>
                        Valeur brute<br/>
                        mensuelle
                    </td>
                    <td width="70" class="tdfixe" height="65">
                        <div style="width: 110px;height:0px;"></div>&nbsp;
                    </td>
                    <td width="50" class="tdfixe" height="65">
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
                    if ($cssligne == 'bdligneimpaireTD') {
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

        <center>
            <?php if (!$_SESSION["User"]->getAut($Section)) { ?>
                <input type="submit" value="Enregistrer" name="enregistrer" class="submit"/>&nbsp;&nbsp;&nbsp;
            <?php } ?>
        </center>
</form>


<?php
if (!isset($EntetePiedFalse) || !$EntetePiedFalse)
{
include __DIR__ . "/../include/pied.inc.php";
?>
</body>
</html>
<?php
}//EntetePied
