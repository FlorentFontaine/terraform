<?php

/** @var $etude string */
/** @var $TitleTable string */
/** @var $MoisDeb string */
/** @var $MoisFin string */
/** @var $MesLignesProduits array */
/** @var $MesLignesCharges array */

use htmlClasses\TableV2;
use Helpers\StringHelper;
if(!isset($EntetePiedFalse) || !$EntetePiedFalse)
{
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
    <link rel="stylesheet" href="../style.css" type="text/css" media="screen"/>
    <link rel="stylesheet" type="text/css" href="../print.css" media="print"/>
    <script type="text/javascript" src="../BenchMark/formstation.js"></script>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title><?php echo ucfirst($etude); ?></title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
</head>

<body>
<?php
include_once __DIR__ . "/../include/entete.inc.php";
?>

<script type="text/javascript">
    function submitBenchForm() {
        document.formBench.submit();
    }
</script>

<?php
}//entetepied
?>
<center>
    <?php
    $TitleTable = '';

    if ($etude == "consolidation") {
        $TitleTable = "CONSOLIDATION";
    } elseif ($etude == "benchmark") {
        $TitleTable = "BENCHMARK";
    }

    if ($_SESSION["User"]->Type == "Secteur") {
        $TitleTable .= " - Secteur : " . $_SESSION["User"]->Nom . " " . $_SESSION["User"]->Prenom;
    } elseif ($_SESSION["User"]->Type == "Region") {
        $TitleTable .= " - R&eacute;gion : " . $_SESSION["User"]->Nom . " " . $_SESSION["User"]->Prenom;
    } else {
        $TitleTable .= " - R&eacute;seaux";
    }

    $TitleTable .= " de " . StringHelper::DateComplete(str_replace("-00", "-01", $MoisDeb)) . " &agrave; " . StringHelper::DateComplete(str_replace("-00", "-01", $MoisFin))
    ?>

    <table>
        <tr>
            <td>
                <?php
                if (!isset($xls) || !$xls) {
                    include_once __DIR__ . '/../BenchMark/Moteur.php';
                }
                ?>
            </td>
        </tr>

        <tr>
            <td>
                <table dir="IMP_PDF;TITLETABLE:<?php echo $TitleTable; ?>;FREEZEPLAN:A3;FITHEIGHT:1" style="width: 100%"
                       id="tab_1" border="0" align="center" class="tabBalance" bordercolordark=#000000
                       bordercolorlight=#000000>

                    <?php if ($etude == "consolidation") {
                        $opt["colorsigne"] = array(6, 7); ?>
                        <thead>
                        <tr class="EnteteTab">
                            <td class="tdfixe" width="300" rowspan="2" style="width: 350px">Produits</td>
                            <td colspan="3">
                                Consolidation
                            </td>
                            <td class="colvide"></td>
                            <td colspan="2" width="150">Comparatif</td>
                        </tr>
                        <tr class="EnteteTab sticky-up">
                            <td class="tdfixe" width="60">
                                <div class="div95"></div>
                                R&eacute;alis&eacute;
                            </td>
                            <td class="tdfixe" width="60">
                                <div class="div95"></div>
                                Pr&eacute;vu
                            </td>
                            <td class="tdfixe" width="60">
                                <div class="div95"></div>
                                N-1
                            </td>
                            <td class="colvide tdfixe" width="2"></td>
                            <td class="tdfixe" width="60">
                                <div class="div95"></div>
                                R&eacute;a - Pr&eacute;v
                            </td>
                            <td class="tdfixe" width="60">
                                <div class="div95"></div>
                                R&eacute;a - (N-1)
                            </td>
                        </tr>
                        </thead>
                    <?php } else {
                        $opt["colorsigne"] = array(8); ?>
                        <thead>
                        <tr class="EnteteTab">
                            <td class="tdfixe" width="300" rowspan="2" style="width: 350px">Produits</td>
                            <td></td>
                            <td class="colvide" width="2"></td>
                            <td colspan="3">
                                Benchmark
                            </td>
                            <td class="colvide" width="2"></td>
                            <td></td>
                        </tr>
                        <tr class="EnteteTab sticky-up">
                            <td class="tdfixe" width="60">
                                <div class="div95"></div>
                                Comparaison
                            </td>
                            <td class="colvide tdfixe" width="2"></td>
                            <td class="tdfixe" width="60">
                                <div class="div95"></div>
                                -
                            </td>
                            <td class="tdfixe" width="60">
                                <div class="div95"></div>
                                Moyenne
                            </td>
                            <td class="tdfixe" width="60">
                                <div class="div95"></div>
                                +
                            </td>
                            <td class="colvide tdfixe" width="2"></td>
                            <td class="tdfixe" width="60">
                                <div class="div95"></div>
                                Comparaison</br> - Moyenne
                            </td>
                        </tr>
                        </thead>
                    <?php } ?>
                    <tbody>
                    <?php
                    $NbCols = ($etude == "consolidation") ? 7 : 8;
                    $LnNumber = 0;

                    if ($etude == "consolidation") {
                        $PRINTBREAKROW1 = 72;
                        $PRINTBREAKROW2 = 0;
                    } else {
                        $PRINTBREAKROW1 = 42;
                        $PRINTBREAKROW2 = 83;
                    }

                    $Intitule1 = "MARGE BOUTIQUE ET AUTRES PRESTATIONS DE SERVICE";
                    $Intitule2 = "PRODUITS MANDAT ET HORS MANDAT";

                    foreach ($MesLignesProduits as $codecompte => $UneLigne) {
                        $LnNumber++;

                        if (isset($cssligne) && $cssligne == 'bdligneimpaireTD') {
                            $cssligne = 'bdlignepaireTD';
                        } else {
                            $cssligne = 'bdligneimpaireTD';
                        }

                        if ($PRINTBREAKROW1 > 0 && $LnNumber == $PRINTBREAKROW1) {
                            echo "</table>";
                            Benchmark::display_EnteteTab($TitleTable, $etude, $Intitule1);
                        }

                        if ($PRINTBREAKROW2 > 0 && $LnNumber == $PRINTBREAKROW2) {
                            echo "</table>";
                            Benchmark::display_EnteteTab($TitleTable, $etude, $Intitule2);
                        }

                        if (stristr($codecompte, "STOTAL")) {
                            $cssligne1 = "lnstotal";
                        } elseif (stristr($codecompte, "TOTAL")) {
                            $cssligne1 = "lntotal";
                        } elseif (stristr($codecompte, "TITRE")) {
                            $cssligne1 = "EnteteTab";
                        } else {
                            $cssligne1 = $cssligne;
                        }

                        echo table::getLine($UneLigne, array("class" => $cssligne1), $NbCols, $opt);
                    }
                    ?>

                </table>
                <table dir="IMP_PDF;FREEZEPLAN:A3;TITLETABLE:<?php echo $TitleTable; ?>;FITHEIGHT:1" style="width: 100%"
                       id="tab_2" border="0" align="center" class="tabBalance" bordercolordark=#000000
                       bordercolorlight=#000000>

                    <?php if ($etude == "consolidation") { ?>
                        <thead>
                        <tr class="EnteteTab">
                            <td class="tdfixe" style="width: 350px" width="300" rowspan="2">Charges</td>
                            <td colspan="3">
                                Consolidation
                            </td>
                            <td class="colvide"></td>
                            <td colspan="2">Comparatif</td>
                        </tr>
                        <tr class="EnteteTab sticky-up">
                            <td class="tdfixe">
                                <div class="div90"></div>
                                R&eacute;alis&eacute;
                            </td>
                            <td class="tdfixe">
                                <div class="div90"></div>
                                Pr&eacute;vu
                            </td>
                            <td class="tdfixe">
                                <div class="div90"></div>
                                N-1
                            </td>
                            <td class="tdfixe colvide" width="2"></td>
                            <td class="tdfixe">
                                <div class="div90"></div>
                                R&eacute;a - Pr&eacute;v
                            </td>
                            <td class="tdfixe">
                                <div class="div90"></div>
                                R&eacute;a - (N-1)
                            </td>
                        </tr>
                        </thead>

                    <?php } else { ?>
                        <thead>
                        <tr class="EnteteTab">
                            <td style="width: 350px" width="300" rowspan="2">Charges</td>
                            <td></td>
                            <td class="colvide" width="2"></td>
                            <td colspan="3">
                                BenchMark
                            </td>
                            <td class="colvide" width="2"></td>
                            <td></td>
                        </tr>
                        <tr class="EnteteTab">
                            <td width="60">
                                <div class="div95"></div>
                                Comparaison
                            </td>
                            <td class="colvide" width="2"></td>
                            <td width="60">
                                <div class="div95"></div>
                                -
                            </td>
                            <td width="60">
                                <div class="div95"></div>
                                Moyenne
                            </td>
                            <td width="60">
                                <div class="div95"></div>
                                +
                            </td>
                            <td class="colvide" width="2"></td>
                            <td width="60">
                                <div class="div95"></div>
                                comparaison<br/> - Moyenne
                            </td>
                        </tr>
                        </thead>
                    <?php } ?>
                    <tbody>
                    <?php
                    $NbCols = ($etude == "consolidation") ? 7 : 8;

                    $opt["colorreverse"] = true;

                    foreach ($MesLignesCharges as $codecompte => $UneLigne) {
                        if (isset($cssligne) && $cssligne == 'bdligneimpaireTD') {
                            $cssligne = 'bdlignepaireTD';
                        } else {
                            $cssligne = 'bdligneimpaireTD';
                        }

                        if (stristr($codecompte, "STOTAL")) {
                            $cssligne1 = "lnstotal";
                        } elseif (stristr($codecompte, "TOTAL")) {
                            $cssligne1 = "lntotal";
                        } elseif (stristr($codecompte, "TITRE")) {
                            $cssligne1 = "EnteteTab";
                        } else {
                            $cssligne1 = $cssligne;
                        }

                        echo table::getLine($UneLigne, array("class" => $cssligne1), $NbCols, $opt);
                    }

                    ?>
                    </tbody>
                </table>
            </td>
        </tr>
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
}
