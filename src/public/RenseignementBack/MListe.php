<?php
use htmlClasses\TableV2;
use Helpers\StringHelper;

if(!isset($EntetePiedFalse) || !$EntetePiedFalse)
{
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <link rel="stylesheet" href="../style.css" type="text/css" media="screen"/>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>Renseignement</title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
</head>

<body>
<?php
include("../include/entete.inc.php");

?>
<?php
}//enetetepied

$TitleTable = "RENSEIGNEMENTS";


if (!$_SESSION["User"]->getAut($Section) && $_SESSION["ModifOK"]) {

$action = "?" . $_SERVER['QUERY_STRING'];

if (!isset($Retour["oublimarge"]) || !$Retour["oublimarge"]) {
    $action = str_replace("OubliMarge=1", "", $action);
}

if (!isset($Retour["AnoTauxSup100"]) || !$Retour["AnoTauxSup100"]) {
    $action = str_replace("AnoTauxSup100=1", "", $action);
}

if (!isset($Retour["AnoStockInit"]) || !$Retour["AnoStockInit"]) {
    $action = str_replace("AnoStockInit=1", "", $action);
}

if (!isset($Retour["AnoStockFinal"]) || !$Retour["AnoStockFinal"]) {
    $action = str_replace("AnoStockFinal=1", "", $action);
}

if (!isset($Retour["AnoStockFinalZero"]) || !$Retour["AnoStockFinalZero"]) {
    $action = str_replace("AnoStockFinalZero=1", "", $action);
}

if ($action === "?") {
    $action = "";
}

?>
<form method="post" action="<?php echo $action; ?>">
    <input type="hidden" name="Enregistrement" value="1"/>
    <center style="margin-top: 20px">
        <?php if ($Retour) { ?>
            <input type="button"
                   onclick="customConfirm('Les informations non enregistr&eacute;es seront perdus',function(){
                       window.location.href='Liste.php'
                   })"
                   class="button-spring" style="width: 220px"
                   value="Annuler recherche anomalie"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <?php } ?>

        <input type="submit" <?php if ($Retour) {
            echo "tabindex='800'";
        } ?> name="valid" value="Enregistrer" class="button-spring"/>
    </center>
    <br/>

    <?php }
    if (isset($Retour["oublimarge"]) && $Retour["oublimarge"]) { ?>
        <input type="hidden" name="CorrectOubliMarge" value="1"/>
    <?php }
    if (isset($Retour["AnoTauxSup100"]) && $Retour["AnoTauxSup100"]) { ?>
        <input type="hidden" name="CorrectAnoTauxSup100" value="1"/>
    <?php }
    if (isset($Retour["AnoStockInit"]) && $Retour["AnoStockInit"]) { ?>
        <input type="hidden" name="CorrectAnoStockInit" value="1"/>
    <?php }
    if (isset($Retour["AnoStockFinal"]) && $Retour["AnoStockFinal"]) { ?>
        <input type="hidden" name="CorrectAnoStockFinal" value="1"/>
    <?php }
    if (isset($Retour["AnoStockFinalZero"]) && $Retour["AnoStockFinalZero"]) { ?>
        <input type="hidden" name="CorrectAnoStockFinalZero" value="1"/>
    <?php }
    ?>
    <center>
        <table style="width:0px" border="0" cellspacing="15" cellpadding="0">
            <tr>
                <td style="text-align:center;font-weight:bold;border:none" colspan="2" class="EnteteTab TitreTable">
                    RENSEIGNEMENTS
                </td>
            </tr>

            <tr>
                <td rowspan="2" valign="top" align="center">

                    <table id="tabTx"
                           dir="IMP_PDF;ORIENTATION:PORTRAIT;TITLETABLE:RENSEIGNEMENTS;FONT_SIZE:10;FITHEIGHT:1;HEIGHT:30;FREEZEPLAN:A5;"
                           style="width:500px;border:0px solid #000000;" border="1" align="center" class="tabForm"
                           bordercolordark=#000000 bordercolorlight=#000000>
                        <?php
                        if ($Imprimer) {
                            echo EnteteTab::HTML_EnteteTab(array("Intitule" => $TitleTable, "colspanCenter" => 1));
                        } ?>
                        <tr>
                            <td colspan="4" style="font-weight:bold;text-align:center;border: 0px;padding:0px;">
                                <center>
                                    <div class="titresectionreverse"> Stocks</div>
                                </center>
                            </td>
                        </tr>

                        <tr class="EnteteTab">
                            <td width="180" class="tdfixe">
                                <div class="div140"></div>
                                Libell&eacute;
                            </td>
                            <td width="60" class="tdfixe">
                                <div class="div90"></div>
                                Taux marge
                            </td>
                            <td width="60" class="tdfixe">
                                <div class="div90"></div>
                                Stock initial
                            </td>
                            <td width="60" class="tdfixe">
                                <div class="div90"></div>
                                Stock Final
                            </td>
                        </tr>
                        <?php

                        $NbCols = 4;

                        foreach ($MesLignesProd as $codeLigne => $UneLigne) {
                            if (isset($cssligne) && $cssligne == 'bdligneimpaireTD') {
                                $cssligne = 'bdlignepaireTD';
                            } else {
                                $cssligne = 'bdligneimpaireTD';
                            }

                            if (stripos($codeLigne, 'soustotal') !== false) {
                                $cssligne1 = "lnstotal";
                            } elseif (stripos($codeLigne, 'BIGTOTAL') !== false) {
                                $cssligne1 = "EnteteTab";
                            } else {
                                $cssligne1 = $cssligne;
                            }


                            echo table::getLine($UneLigne, array("class" => $cssligne1), $NbCols);

                        } ?>

                    </table>
                </td>
                <td align="center" valign="top">
                    <table id="tabCarb"
                           dir="IMP_PDF;ORIENTATION:PORTRAIT;TITLETABLE:RENSEIGNEMENTS;FONT_SIZE:12;HEIGHT:25;"
                           style="border:0px solid #000000;" align="center" border="1" class="tabForm"
                           bordercolordark=#000000 bordercolorlight=#000000>

                        <tr>
                            <td colspan="4" style="font-weight:bold;text-align:center;border: 0;padding:0; ">
                                <center>
                                    <div class="titresectionreverse"> Carburants</div>
                                </center>
                            </td>
                        </tr>

                        <tr class="EnteteTab">
                            <td colspan="2" class="tdfixe">
                                <div class="div140"></div>
                                Carburant
                            </td>
                            <td colspan="2" class="tdfixe">
                                <div class="div90"></div>
                                En Litre
                            </td>
                        </tr>

                        <?php
                        $NbCols = 2;

                        foreach ($MesLigneCarb as $codeLigne => $UneLigne) {
                            if (isset($cssligne) && $cssligne == 'bdligneimpaireTD') {
                                $cssligne = 'bdlignepaireTD';
                            } else {
                                $cssligne = 'bdligneimpaireTD';
                            }

                            if (stripos($codeLigne, 'soustotal') !== false) {
                                $cssligne1 = "lnstotal";
                            } elseif (stripos($codeLigne, 'BIGTOTAL') !== false) {
                                $cssligne1 = "EnteteTab";
                            } else {
                                $cssligne1 = $cssligne;
                            }


                            echo table::getLine($UneLigne, array("class" => $cssligne1), $NbCols);

                        } ?>

                        <tr class="EnteteTab">
                            <td colspan="2">Total :</td>
                            <td colspan="2" align="right"><?php echo StringHelper::NombreFr($TotalCarb); ?></td>
                        </tr>

                        <tr>
                            <td colspan="4" style="font-weight:bold;text-align:center;border: 0px "><br/></td>
                        </tr>

                        <tr>
                            <td colspan="4" style="font-weight:bold;text-align:center;border: 0;padding:0;">
                                <center>
                                    <div class="titresectionreverse"> Divers</div>
                                </center>
                            </td>
                        </tr>

                        <tr class="EnteteTab">
                            <td colspan="2" class="tdfixe">
                                <div class="div140"></div>
                                Libell&eacute;
                            </td>
                            <td colspan="2" class="tdfixe">
                                <div class="div90"></div>
                            </td>
                        </tr>

                        <?php foreach ($MesLigneDivers as $Champ => $UneLigne) {
                            if (isset($cssligne) && $cssligne == 'bdligneimpaireTD') {
                                $cssligne = 'bdlignepaireTD';
                            } else {
                                $cssligne = 'bdligneimpaireTD';
                            }

                            echo table::getLine($UneLigne, array("class" => $cssligne));
                        } ?>

                        <tr>
                            <td colspan="4" style="font-weight:bold;text-align:center;border: 0px "><br/></td>
                        </tr>

                        <tr>
                            <td colspan="4" style="font-weight:bold;text-align:center;border: 0px;padding:0px;">
                                <center>
                                    <div class="titresectionreverse"> Saisonnalit&eacute;</div>
                                </center>
                            </td>
                        </tr>

                        <tr class="EnteteTab">
                            <td width="110" class="tdfixe">
                                <div class="div140"></div>
                            </td>
                            <td width="70" class="tdfixe">
                                <div class="div70"></div>
                                Cl&eacute; 1
                            </td>
                            <td width="70" class="tdfixe">
                                <div class="div70"></div>
                                Cl&eacute; 2
                            </td>
                            <td width="70" class="tdfixe">
                                <div class="div70"></div>
                                Cl&eacute; 3
                            </td>
                        </tr>
                        <?php
                        foreach ($MesLigneSaison as $Date => $UneLigne) {
                            if (isset($cssligne) && $cssligne == 'bdligneimpaireTD') {
                                $cssligne = 'bdlignepaireTD';
                            } else {
                                $cssligne = 'bdligneimpaireTD';
                            }

                            echo table::getLine($UneLigne, array("class" => $cssligne));
                        } ?>
                        <tr class="EnteteTab">
                            <td align="right" style="text-align:right">Total :</td>
                            <td align="right"
                                style="text-align:right"><?php echo StringHelper::NombreFr($MesSum["SAI_CLE1"], 4, true, true); ?></td>
                            <td align="right"
                                style="text-align:right"><?php echo StringHelper::NombreFr($MesSum["SAI_CLE2"], 4, true, true); ?></td>
                            <td align="right"
                                style="text-align:right"><?php echo StringHelper::NombreFr($MesSum["SAI_CLE3"], 4, true, true); ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </center>
    <?php if (!$_SESSION["User"]->getAut($Section) && $_SESSION["ModifOK"]) { ?>
    <br/>
    <center><input type="submit" name="valid" value="Enregistrer" class="button-spring"/></center>
    <br/>

</form>
<?php }
if (!isset($EntetePiedFalse) || !$EntetePiedFalse)
{
?>
<?php
include("../include/pied.inc.php");
?>
</body>
</html>
<?php
}//entetepied
