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
    <title>Calcul de marges</title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
</head>

<body>
<?php
include_once __DIR__ . "/../include/entete.inc.php";
?>
<?php
}//enetepied
?>
<center>
    <?php
    $TitleTable = "CALCUL DES MARGES - " . StringHelper::DateComplete(str_replace("-00", "-01", $_SESSION["MoisHisto"])) . " - " . $_SESSION["station_BALI_TYPE_exp"];
    ?>

    <table dir="IMP_PDF;TITLETABLE:CALCUL DES MARGES;ORIENTATION:LANDSCAPE;FREEZEPLAN:B5;FITHEIGHT:1;HEIGHT:25;"
           style="width:0px" class="tabBalance" bordercolordark=#000000 bordercolorlight=#000000 id="tab_Marges">

        <?php echo EnteteTab::HTML_EnteteTab(array("Intitule" => $TitleTable, "colspanLeft" => "2", "colspanCenter" => "8", "colspanRight" => "3")); ?>

        <tr class="EnteteTab sticky">
            <td width="140" height="40" class="tdfixe">
                <div class="div300"></div>
                Marges
            </td>
            <td width="50" class="tdfixe">
                <div class="div80"></div>
                Stock<br/>initial
            </td>
            <td width="50" class="tdfixe">
                <div class="div80"></div>
                Achat
            </td>
            <td width="50" class="tdfixe">
                <div class="div80"></div>
                Vente
            </td>
            <td width="50" class="tdfixe">
                <div class="div80"></div>
                Marge <br/>th&eacute;orique
            </td>
            <td width="50" class="tdfixe">
                <div class="div80"></div>
                Tx Marge <br/>th&eacute;orique
            </td>
            <td width="50" class="tdfixe">
                <div class="div80"></div>
                Stock <br/>th&eacute;orique
            </td>
            <td width="50" class="tdfixe">
                <div class="div80"></div>
                Marge <br/>r&eacute;elle
            </td>
            <td width="50" class="tdfixe">
                <div class="div80"></div>
                Tx Marge <br/>r&eacute;elle
            </td>
            <td width="50" class="tdfixe">
                <div class="div80"></div>
                Stock <br/>r&eacute;el
            </td>

            <td width="50" class="tdfixe">
                <div class="div80"></div>
                Ecart <br/>/Marge
            </td>
            <td width="50" class="tdfixe">
                <div class="div80"></div>
                Ecart <br/>/Marge<br/> pr&eacute;c&eacute;dent
            </td>
            <td width="50" class="tdfixe">
                <div class="div80"></div>
                Stocks<br/> retenu<br/> sur bilan
            </td>
        </tr>

        <?php
        $NbCols = 11;

        foreach ($MesLignesMarge as $codeLigne => $UneLigne) {
            if (isset($cssligne) && $cssligne != 'bdligneimpaireTD') {
                $cssligne = 'bdligneimpaireTD';
            } else {
                $cssligne = 'bdlignepaireTD';
            }

            if (strpos($codeLigne, "soustotal") !== false) {
                $cssligne1 = "lnstotal";
            } elseif (strpos($codeLigne, "BIGTOTAL") !== false) {
                $cssligne1 = "EnteteTab";
            } else {
                $cssligne1 = $cssligne;
            }

            echo table::getLine($UneLigne, array("class" => $cssligne1), $NbCols);
        }
        ?>
    </table>
</center>
<?php
if (!isset($EntetePiedFalse) || !$EntetePiedFalse)
{
?>
<?php
include_once __DIR__ . "/../include/pied.inc.php";
?>
</body>
</html>
<?php
}//enetepied
