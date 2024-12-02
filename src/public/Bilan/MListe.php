<?php

use Helpers\StringHelper;
use htmlClasses\TableV2;

require_once __DIR__ . '/../ctrl/ctrl.php';

if (!isset($EntetePiedFalse) || !$EntetePiedFalse) { ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
    <head>
        <link rel="stylesheet" type="text/css" href="../style.css" media="screen">
        <link rel="stylesheet" type="text/css" href="../print.css" media="print">
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
        <title>Bilan</title>
        <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
    </head>
    <body>
    <?php
    include_once __DIR__ . "/../include/entete.inc.php";
}

if ((isset($print) && $print) || (isset($_GET['print']) && $_GET['print'])) {
    $css = 'class="tabBilan"';
} else {
    $css = 'border="0" class="tabBalance"';
}
?>

    <center>
        <?php
        $TitleTable = "BILAN - " . StringHelper::DateComplete(str_replace("-00", "-01", $_SESSION["MoisHisto"])) . " - " . $_SESSION["station_BALI_TYPE_exp"];
        ?>

        <table
            dir="IMP_PDF;ORIENTATION:LANDSCAPE;TITLETABLE:BILAN;FONT_SIZE:10;EXTANDTABLE:1;BORDER:1;FITHEIGHT:1;HEIGHT:21;FREEZEPLAN:A5;FITWIDTH:1" <?php echo $css; ?>
            align="center" bordercolordark=#000000 bordercolorlight=#000000 id="tab_Bilan">
            <thead>
            <?php echo EnteteTab::HTML_EnteteTab(array("Intitule" => $TitleTable, "colspanCenter" => 4)); ?>

            <tr class="EnteteTab">
                <td libelle="1" class="tdfixe" width="150">Libell&eacute;</td>
                <td class="tdfixe" width="50">
                    <div class="div90"></div>
                    Brut
                </td>
                <td class="tdfixe" width="50">
                    <div class="div90"></div>
                    Amor/Prov
                </td>
                <td class="tdfixe" width="50">
                    <div class="div90"></div>
                    Net
                </td>
                <td class="colvide" width="5" style='border-bottom: none;background-color:#FFFFFF;'></td>
                <td class="tdfixe" width="150">
                    <div class="div90"></div>
                    Libell&eacute;
                </td>
                <td class="tdfixe" width="60">
                    <div class="div90"></div>
                    Net
                </td>
            </tr>
            </thead>
            <tbody>

            <?php
            $Table1 = $Table2 = array();
            
            $NbCols = 4;
            $cssligne = $cssligne1 = '';
            foreach ($MesLignesActif as $codecompte => $UneLigne) {
                if ($cssligne == 'bdlignepaireTD' || $cssligne1 == "lnstotal") {
                    $cssligne = 'bdligneimpaireTD';
                } else {
                    $cssligne = 'bdlignepaireTD';
                }

                if (stristr($codecompte, "STOTAL")) {
                    $cssligne1 = "lnstotal";
                } elseif (stristr($codecompte, "TOTAUX")) {
                    $cssligne1 = "lntotal";
                } elseif (stristr($codecompte, "TITRE")) {
                    $cssligne1 = "EnteteTab";
                } elseif (stristr($codecompte, "BIG")) {
                    $cssligne1 = "EnteteTab";
                } else {
                    $cssligne1 = $cssligne;
                }

                $Table1[] = table::getLine($UneLigne, array("class" => $cssligne1), $NbCols);
            }

            $NbCols = 2;
            foreach ($MesLignesPassif as $codecompte => $UneLigne) {
                if ($cssligne == 'bdlignepaireTD' || $cssligne1 == "lnstotal") {
                    $cssligne = 'bdligneimpaireTD';
                } else {
                    $cssligne = 'bdlignepaireTD';
                }

                if (stristr($codecompte, "STOTAL")) {
                    $cssligne1 = "lnstotal";
                } elseif (stristr($codecompte, "TOTAUX")) {
                    $cssligne1 = "lntotal";
                } elseif (stristr($codecompte, "TITRE")) {
                    $cssligne1 = "EnteteTab";
                } elseif (stristr($codecompte, "BIG")) {
                    $cssligne1 = "EnteteTab";
                } else {
                    $cssligne1 = $cssligne;
                }

                $Table2[] = table::getLine($UneLigne, array("class" => $cssligne1), $NbCols);
            }

            foreach ($Table2 as $codeLigne => $Ligne) {
                if ($Table1[$codeLigne]) {
                    if (strpos($Table2[$codeLigne], '<tr class="bdligneimpaireTD" >') !== false) {
                        $Table2[$codeLigne] = str_replace('<tr class="bdligneimpaireTD" >', "", $Table2[$codeLigne]);
                        $Table2[$codeLigne] = str_replace('<td ', "<td class='bdligneimpaireTD' style=''", $Table2[$codeLigne]);
                    }

                    if (strpos($Table2[$codeLigne], '<tr class="bdlignepaireTD" >') !== false) {
                        $Table2[$codeLigne] = str_replace('<tr class="bdlignepaireTD" >', "", $Table2[$codeLigne]);
                        $Table2[$codeLigne] = str_replace('<td ', "<td class='bdlignepaireTD' style=''", $Table2[$codeLigne]);
                    }

                    if (strpos($Table2[$codeLigne], '<tr class="lnstotal" >') !== false) {
                        $Table2[$codeLigne] = str_replace('<tr class="lnstotal" >', "", $Table2[$codeLigne]);
                        $Table2[$codeLigne] = str_replace('<td ', "<td class='lnstotal' style=''", $Table2[$codeLigne]);
                    }

                    if (strpos($Table2[$codeLigne], '<tr class="lntotal" >') !== false) {
                        $Table2[$codeLigne] = str_replace('<tr class="lntotal" >', "", $Table2[$codeLigne]);
                        $Table2[$codeLigne] = str_replace('<td ', "<td class='lntotal' style=''", $Table2[$codeLigne]);
                    }

                    if (strpos($Table2[$codeLigne], '<tr class="EnteteTab" >') !== false) {
                        $Table2[$codeLigne] = str_replace('<tr class="EnteteTab" >', "", $Table2[$codeLigne]);
                        $Table2[$codeLigne] = str_replace('<td ', "<td class='EnteteTab' style=''", $Table2[$codeLigne]);
                    }

                    if (strpos($Table1[$codeLigne], '<tr class="bdligneimpaireTD" >') !== false) {
                        $Table1[$codeLigne] = str_replace('<tr class="bdligneimpaireTD" >', "<tr>", $Table1[$codeLigne]);
                        $Table1[$codeLigne] = str_replace('<td ', "<td class='bdligneimpaireTD' style=''", $Table1[$codeLigne]);
                    }

                    if (strpos($Table1[$codeLigne], '<tr class="bdlignepaireTD" >') !== false) {
                        $Table1[$codeLigne] = str_replace('<tr class="bdlignepaireTD" >', "<tr>", $Table1[$codeLigne]);
                        $Table1[$codeLigne] = str_replace('<td ', "<td class='bdlignepaireTD' style=''", $Table1[$codeLigne]);
                    }

                    if (strpos($Table1[$codeLigne], '<tr class="lnstotal" >') !== false) {
                        $Table1[$codeLigne] = str_replace('<tr class="lnstotal" >', "<tr>", $Table1[$codeLigne]);
                        $Table1[$codeLigne] = str_replace('<td ', "<td class='lnstotal' style=''", $Table1[$codeLigne]);
                    }

                    if (strpos($Table1[$codeLigne], '<tr class="lntotal" >') !== false) {
                        $Table1[$codeLigne] = str_replace('<tr class="lntotal" >', "<tr>", $Table1[$codeLigne]);
                        $Table1[$codeLigne] = str_replace('<td ', "<td class='lntotal' style=''", $Table1[$codeLigne]);
                    }

                    if (strpos($Table1[$codeLigne], '<tr class="EnteteTab" >') !== false) {
                        $Table1[$codeLigne] = str_replace('<tr class="EnteteTab" >', "<tr>", $Table1[$codeLigne]);
                        $Table1[$codeLigne] = str_replace('<td ', "<td class='EnteteTab' style=''", $Table1[$codeLigne]);
                    }

                    echo str_replace("</tr>", "", $Table1[$codeLigne]) . "<td class='colvide' style=''></td>" . $Table2[$codeLigne];


                } else {
                    if (strpos($Table2[$codeLigne], '<tr class="bdligneimpaireTD" >') !== false) {
                        $Table2[$codeLigne] = str_replace('<td ', "<td class='bdligneimpaireTD' style=';'", $Table2[$codeLigne]);
                        $Table2[$codeLigne] = str_replace('<tr class="bdligneimpaireTD" >', "<tr><td style='border:1px solid white;'></td><td style='border:1px solid white;'></td><td style='border:1px solid white;'></td><td style='border:1px solid white;'></td><td style=''></td>", $Table2[$codeLigne]);
                    }

                    if (strpos($Table2[$codeLigne], '<tr class="bdlignepaireTD" >') !== false) {
                        $Table2[$codeLigne] = str_replace('<td ', "<td class='bdlignepaireTD' style=';'", $Table2[$codeLigne]);
                        $Table2[$codeLigne] = str_replace('<tr class="bdlignepaireTD" >', "<tr><td style='border:1px solid white;'></td><td style='border:1px solid white;'></td><td style='border:1px solid white;'></td><td style='border:1px solid white;'></td><td style=''></td>", $Table2[$codeLigne]);
                    }

                    if (strpos($Table2[$codeLigne], '<tr class="lnstotal" >') !== false) {
                        $Table2[$codeLigne] = str_replace('<td ', "<td class='lnstotal' style=';'", $Table2[$codeLigne]);
                        $Table2[$codeLigne] = str_replace('<tr class="lnstotal" >', "<tr><td style='border:1px solid white;'></td><td style='border:1px solid white;'></td><td style='border:1px solid white;'></td><td style='border:1px solid white;'></td><td style=''></td>", $Table2[$codeLigne]);
                    }

                    if (strpos($Table2[$codeLigne], '<tr class="lntotal" >') !== false) {
                        $Table2[$codeLigne] = str_replace('<td ', "<td class='lntotal' style=';'", $Table2[$codeLigne]);
                        $Table2[$codeLigne] = str_replace('<tr class="lntotal" >', "<tr><td style='border:1px solid white;'></td><td style='border:1px solid white;'></td><td style='border:1px solid white;'></td><td style='border:1px solid white;'></td><td style=''></td>", $Table2[$codeLigne]);
                    }

                    if (strpos($Table2[$codeLigne], '<tr class="EnteteTab" >') !== false) {
                        $Table2[$codeLigne] = str_replace('<td ', "<td class='EnteteTab' style=';'", $Table2[$codeLigne]);
                        $Table2[$codeLigne] = str_replace('<tr class="EnteteTab" >', "<tr><td style='border:1px solid white;'></td><td style='border:1px solid white;'></td><td style='border:1px solid white;'></td><td style='border:1px solid white;'></td><td style=''></td>", $Table2[$codeLigne]);
                    }

                    echo $Table2[$codeLigne];
                }
            } ?>

            </tbody>
        </table>
    </center>

<?php
if (!isset($EntetePiedFalse) || !$EntetePiedFalse) { ?>
    <br/><br/>
    <?php include_once __DIR__ . "/../include/pied.inc.php"; ?>
    </body>
    </html>
    <?php
}
