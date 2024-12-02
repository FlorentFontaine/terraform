<?php

use Helpers\StringHelper;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../ctrl/ctrl.php';
require_once __DIR__ . '/../Anomalie/Anomalie.class.php';
require_once __DIR__ . '/../dbClasses/AccesDonnees.php';

$Section = "Anomalies";

if(!isset($EntetePiedFalse) || !$EntetePiedFalse) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>Remarques et anomalies</title>
    <link rel="stylesheet" href="../style.css" type="text/css" media="screen"/>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
</head>

<body>
<?php
include_once __DIR__ . "/../include/entete.inc.php";

}

$TitleTable = "REMARQUES ET ANOMALIES";
$TitleTable .= " - " . StringHelper::DateComplete(str_replace("-00", "-01", $_SESSION["MoisHisto"])) . " - " . $_SESSION["station_BALI_TYPE_exp"];

Anomalie::Rem_Objectif($Rem);
Anomalie::Rem_TxMarge($Rem);

Anomalie::CompterAnomalies($Anomalie);
?>

<center>
    <table dir="IMP_PDF;TITLETABLE:ANOMALIES ET REMARQUES;FITHEIGHT:1;HEIGHT:30;FREEZEPLAN:A5;" border="0"
           style="margin: 20px;">

        <?php echo EnteteTab::HTML_EnteteTab(array("Intitule" => $TitleTable, "colspanLeft" => "2", "colspanCenter" => "0", "colspanRight" => "1")); ?>
        <tr>
            <td colspan="3" align="center" class="EnteteTab">REMARQUES</td>
        </tr>

        <tr>
            <td width="100">&nbsp;</td>
            <td width="300">&nbsp;</td>
            <td width="100"></td>
        </tr>
        <tr>
            <td colspan="3" align="left" class="bolder">Pr&eacute;visionnel</td>
        </tr>

        <tr>
            <td></td>
            <td colspan="2">
                <?php
                if (isset($Rem["PrevCc"]) && $Rem["PrevCc"]) {
                    echo "<a style='color:blue'>- Aucune valeur pour le pr&eacute;visionnel du mois.</a>";
                } else {
                    echo "- Le pr&eacute;visionnel du mois est pr&eacute;sent.";
                }
                ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <?php
                if (isset($Rem["PrevTxModifie"]) && $Rem["PrevTxModifie"]) {
                    echo "<a style='color:blue'>- Les taux de marge des renseignements ne sont pas ceux du pr&eacute;visionnel.</a>";
                } else {
                    echo "- Les taux de marge des renseignements sont ceux du pr&eacute;visionnel.";
                }
                ?>
            </td>
        </tr>

        <tr>
            <td height="50" colspan="2"></td>
        </tr>

        <tr>
            <td colspan="3" align="center" class="EnteteTab">ANOMALIES</td>
        </tr>
        <tr>
            <td width="100">&nbsp;</td>
            <td width="300">&nbsp;</td>
            <td width="100"></td>
        </tr>
        <tr>
            <td colspan="2" align="left" class="bolder">% de marge et Stocks dans les renseignements du mois</td>
        </tr>
        <tr>
            <td class=""></td>
            <td>
                <?php
                if (isset($Anomalie['oublimarge']) && $Anomalie["oublimarge"]) {
                    $NbOubli = explode("||#||", $Anomalie["oublimarge"]);
                    echo "<a style='color:red'>- Il manque " . (count($NbOubli) - 1) . " pourcentage(s) de marge dans les renseignements.</a>";
                } else {
                    echo "- R.A.S. sur les pourcentages de marge des renseignements.";
                }
                ?>
            </td>
        </tr>

        <tr>
            <td></td>
            <td>
                <?php
                if (isset($Anomalie['tauxsup100']) && $Anomalie["tauxsup100"]) {
                    $NbTauxSup = explode("||#||", $Anomalie["tauxsup100"]);
                    echo "<a style='color:red'>- " . (count($NbTauxSup) - 1) . " taux de marge sup&eacute;rieur(s) &agrave; 100 % dans les renseignements.</a>";
                } else {
                    echo "- R.A.S. aucun taux de marge sup&eacute;rieur &agrave; 100 % dans les renseignements.";
                }
                ?>
            </td>
        </tr>

        <tr>
            <td></td>
            <td>
                <?php
                if (isset($Anomalie['StockInit']) && $Anomalie["StockInit"]) {
                    $Nb = explode("||#||", $Anomalie["StockInit"]);
                    echo "<a style='color:red'>- Stocks initiaux des renseignements ne sont pas &eacute;gaux &agrave; ceux de la balance (" . (count($Nb) - 1) . ").</a>";
                } else {
                    echo "- R.A.S sur les stocks initiaux des renseignements.";
                }
                ?>
            </td>
        </tr>

        <tr>
            <td></td>
            <td>
                <?php
                if (isset($Anomalie['StockFinal']) && $Anomalie["StockFinal"]) {
                    $Nb = explode("||#||", $Anomalie["StockFinal"]);
                    echo "<a style='color:red'>- Stocks finaux des renseignements ne sont pas &eacute;gaux &agrave; ceux de la balance (" . (count($Nb) - 1) . ").</a>";
                } else {
                    echo "- R.A.S sur les stocks finaux des renseignements.";
                }
                ?>
            </td>
        </tr>

        <tr>
            <td></td>
            <td>
                <?php
                if (isset($Anomalie['VariationStock']) && $Anomalie["VariationStock"]) {
                    $Nb = explode("||#||", $Anomalie["VariationStock"]);
                    echo "<a style='color:red'>- " . (count($Nb) - 1) . " erreur(s) sur la variation des stocks.</a>";
                } else {
                    echo "- R.A.S. sur les variations des stocks.";
                }
                ?>
            </td>
        </tr>

        <tr>
            <td></td>
            <td>
                <?php
                if (isset($Anomalie['CARB']) && $Anomalie["CARB"]) {
                    echo "<a style='color:red'>- Les litrages des carburants ne sont pas saisis.</a>";
                } else {
                    echo "- R.A.S. sur les litrages des carburants.";
                }
                ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <?php
                if (isset($Anomalie['AnoStockFinalZero']) && $Anomalie["AnoStockFinalZero"]) {
                    $Nb = explode("||#||", $Anomalie["AnoStockFinalZero"]);
                    echo "<a style='color:red'>- Veuillez completer les stocks finaux pour " . (count($Nb) - 1) . " poste(s).</a>";
                } else {
                    echo "- R.A.S. sur la saisie des stocks finaux des renseignements.";
                }
                ?>
            </td>
        </tr>
<!--        <tr>-->
<!--            <td></td>-->
<!--            <td>-->
<!--                --><?php
//                if (isset($Anomalie['Inventaire']) && $Anomalie["Inventaire"]) {
//                    echo "<a style='color:red'>- La date de dernier inventaire n'est pas saisie dans les renseignements.</a>";
//                } else {
//                    echo "- R.A.S. sur la date de dernier inventaire.";
//                }
//                ?>
<!--            </td>-->
<!--        </tr>-->

        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2" align="left" class="bolder">Balance</td>
        </tr>
        <tr>
            <td></td>
            <td>
                <?php
                if (isset($Anomalie['AnoSens']) && $Anomalie["AnoSens"]) {
                    $Nb = explode("||#||", $Anomalie["AnoSens"]);
                    echo "<a style='color:red'>- Vous utilisez " . (count($Nb) - 1) . " compte(s) avec un sens (D&eacute;bit/Cr&eacute;dit) erron&eacute;</a>";
                } else {
                    echo "- R.A.S. sur le sens (D&eacute;bit/Cr&eacute;dit) des comptes.";
                }
                ?>
        </tr>

        <tr>
            <td>&nbsp;</td>
        </tr>

        <tr>
            <td colspan="2" align="left" class="bolder">Renseignements compl&eacute;mentaires</td>
        </tr>
        <tr>
            <td></td>
            <td>
                <?php
                if (isset($Anomalie['StockFinalB']) && $Anomalie["StockFinalB"]) {
                    $Nb = explode("||#||", $Anomalie["StockFinalB"]);
                    echo "<a style='color:red'>- Vous avez import&eacute; une balance PB ou BI, les stocks finaux ne sont pas compl&eacute;tement saisis (il manque " . (count($Nb) - 1) . " valeur(s)).</a>";
                } else {
                    echo "- RAS sur Stocks finaux des renseignements.";
                }
                ?>
            </td>
        </tr>

        <tr>
            <td>&nbsp;</td>
        </tr>

        <tr>
            <td colspan="2" align="left" class="bolder">Contr&ocirc;le du r&eacute;sultat</td>
        </tr>
        <tr>
            <td></td>
            <td>
                <?php
                if (isset($Anomalie["Bilan"]) && $Anomalie["Bilan"]) {
                    echo "<a style='color:red'>- Le bilan n'est pas &eacute;quilibr&eacute;. (" . StringHelper::Signe($Anomalie["Bilan"]) . ")</a>";
                } else {
                    echo "- Le bilan est &eacute;quilibr&eacute;.";
                }
                ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <?php
                if (isset($Anomalie["Resultat"]) && $Anomalie["Resultat"]) {
                    echo "<a style='color:red'>- Erreur sur le r&eacute;sultat. </a>";
                } else {
                    echo "- RAS sur le r&eacute;sultat.";
                }
                ?>
            </td>
        </tr>

        <tr>
            <td>&nbsp;</td>
        </tr>

        <tr>
            <td colspan="2" align="left" class="bolder">Base de donn&eacute;e</td>
        </tr>
        <tr>
            <td></td>
            <td>
                <?php
                if (isset($Anomalie["BALI_DATE_MAJBASE"]) && $Anomalie["BALI_DATE_MAJBASE"]) {
                    echo "<a style='color:red'>- Mise &agrave; jour de la base de donn&eacute;e non effectu&eacute;e.</a>";
                } else {
                    echo "- R.A.S. sur la mise &agrave; jour de la base de donn&eacute;e.";
                }
                ?>
            </td>
        </tr>
    </table>
</center>

<?php
if (!isset($EntetePiedFalse) || !$EntetePiedFalse) {
?>

<?php
include_once __DIR__ . "/../include/pied.inc.php";
?>
</body>
</html>
<?php
}
