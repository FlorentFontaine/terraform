<?php

global $User;

use htmlClasses\TableV2;

if(!isset($EntetePiedFalse) || !$EntetePiedFalse) {
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>SUIVI</title>
    <link rel="stylesheet" href="../style.css" type="text/css" media="screen">
    <link rel="stylesheet" type="text/css" href="../print.css" media="print">
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
</head>

<body>
<?php
include_once __DIR__ . "/../include/entete.inc.php";
?>

<script type="text/javascript">
    function Trier(Champ) {
        document.getElementById('ChampTri').value = Champ;
        document.formSuivi.submit();
    }
</script>

<?php
}//entetepied
?>
<center>
    <?php
    include_once __DIR__ . "/../SuiviBack/Moteur.php";
    ?>

    <center>
        <br/>

        <table style="width: 650px">
            <tr>
                <td align="right">Balance standard :</td>
                <td bgcolor="#009933">
                    <div style="width: 20px;height: 0;"></div>&nbsp;
                </td>
                <td align="right">Situation Interm&eacute;diaire :</td>
                <td bgcolor="#0000FF">
                    <div style="width: 20px;height: 0;"></div>&nbsp;
                </td>
                <td align="right">Balance Pr&eacute;-bilan :</td>
                <td bgcolor="yellow">
                    <div style="width: 20px;height: 0;"></div>&nbsp;
                </td>
                <td align="right">Balance d&eacute;finitive :</td>
                <td bgcolor="#FF0000">
                    <div style="width: 20px;height: 0;"></div>&nbsp;
                </td>
            </tr>
        </table>
        <br/>

        <table align="center" class="tabBalance" bordercolordark=#000000
               bordercolorlight=#000000 id="tab_Suivi">
            <thead>

            <tr>
                <td class="EnteteTab TitreTable" colspan="32" style="color:white;text-align:center;font-weight:bold;">
                    SUIVI
                </td>
            </tr>
            <tr>
                <td width="60px" rowspan="2" class="tdfixe EnteteTab">
                    <a href="#" onclick="Trier('Lieu.LIE_CODE')">Code<br/>PDV</a>
                </td>

                <td width="100px" rowspan="2" class="tdfixe EnteteTab">
                    <a href="#" onclick="Trier('Lieu.LIE_NOM')">Nom PDV</a>
                </td>

                <td width="100px" rowspan="2" class="tdfixe EnteteTab">
                    <a href="#" onclick="Trier('station.STA_SARL')">Soci&eacute;t&eacute;</a>
                </td>

                <td width="90px" rowspan="2" class="tdfixe EnteteTab">
                    <a href="#" onclick="Trier('dossier.DOS_DEBEX')">Dbt Exercice</a>
                </td>

                <td width="90px" rowspan="2" class="tdfixe EnteteTab">
                    <a href="#" onclick="Trier('dossier.DOS_FINEX')">Fin Exercice</a>
                </td>

                <td width="100px" rowspan="2" class="tdfixe EnteteTab">
                    <a href="#" onclick="Trier('cabinet.CAB_NOM')">Comptable</a>
                </td>

                <td width="100px" rowspan="2" class="tdfixe EnteteTab">
                    <a href="#" onclick="Trier('chefSecteur.Nom')">CDS</a>
                </td>

                <?php
                if ($User->Niveau == 4) {
                    ?>
                    <td width="100px" rowspan="2" class="tdfixe EnteteTab">
                        <a href="#" onclick="Trier('station.STA_DERNCONNECTION_CDS')">
                            Dern. Conn.<br/>CDS
                        </a>
                    </td>
                <?php } ?>

                <td colspan="12" class="EnteteTab" style="height: 28px">
                    <div>
                        <?php if (isset($YearN1) && $YearN1) {
                            echo $YearN1;
                        } ?>
                    </div>
                </td>
                <td colspan="12" class="EnteteTab" style="height: 28px">
                    <div>
                        <?php if (isset($Year) && $Year) {
                            echo $Year;
                        } ?>
                    </div>
                </td>
            </tr>
            <tr class="EnteteTab" style="position: sticky">
                <?php
                for ($i = 1; $i < 13; $i++) {
                    echo "<td  class='tdfixe'>" . $i . "</td>";
                }
                for ($i = 1; $i < 13; $i++) {
                    echo "<td class='tdfixe'>" . $i . "</td>";
                }
                ?>
            </tr>
            </thead>
            <tbody>
            <?php
            //D&eacute;finition des dateS au format jour-mois-ann&eacute;e

            if (($User->Infos['Type'] == "comptable") || ($User->Type == "Secteur")) {
                $NbCols = 29;
            } else {
                $NbCols = 30;
            }

            foreach ($MesLignes as $codecompte => $UneLigne) {
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

                echo table::getLine($UneLigne, array("class" => $cssligne1), $NbCols);
            }
            ?>
            </tbody>
        </table>

    </center>
</center>
<br/>

<?php
if (!isset($EntetePiedFalse) || !$EntetePiedFalse) {
    include_once __DIR__ . "/../include/pied.inc.php";
?>
</body>
</html>
<?php
}
?>
