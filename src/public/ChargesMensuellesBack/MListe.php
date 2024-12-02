<?php

use Helpers\StringHelper;
use htmlClasses\TableV2;

include_once '../ctrl/ctrl.php';

if (!isset($EntetePiedFalse) || !$EntetePiedFalse) {
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
            "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
    <head>
        <link rel="stylesheet" href="../style.css" type="text/css" media="screen"/>
        <link rel="stylesheet" type="text/css" href="../print.css" media="print"/>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
        <title>Comparatif</title>
        <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">


    </head>

    <body>


    <?php
    include("../include/entete.inc.php");
}//entetepied
?>

    <center>
        <?php


        $Mois = StringHelper::DatePlus(str_replace('-00', '-01', $MoisVoulu), array("dateformat" => "Y-m-01"));

        if (!$_GET['Produits']) {
            $TitleTable = "COMPARATIF DES CHARGES";
        } else {
            $TitleTable = "COMPARATIF DES PRODUITS";
        }

        if ($cluster) {
            $TitleTable .= " CLUSTER";
        }

        $InfoTable = StringHelper::DateComplete(str_replace("-00", "-01", $_SESSION["MoisHisto"])) . " - " . $_SESSION["station_BALI_TYPE_exp"];

        if ($_POST["btn_avant"]) {
            $k = $_POST["plus"];
            $k++;
        } elseif ($_POST["btn_arriere"]) {
            $k = $_POST["plus"];
            $k--;
        } else {
            $k = 0;
        }

        if ($_SESSION["station_DOS_NBMOIS"] > 12) {
            ?>
            <form action="" method="post">
                <input type="hidden" value="<?php echo $k ?>" name="plus"/>
                <?php
                echo '<input type="submit" value="<" name="btn_arriere" />&nbsp;';
                echo '<input type="submit" value=">" name="btn_avant" />';
                ?>
            </form>
        <?php } ?>

        <?php
        $Intitule = '';

        if (isset($Type) && $Type == "Produits") {
            $Intitule = "- CA BOUTIQUE ET AUTRES PRESTATIONS DE SERVICE";
        }

        compChargesProdMensuel::display_EnteteTab($TitleTable . " " . $Intitule . " - " . $InfoTable, $Type, $cluster, $k, $MoisVoulu, $Intitule, $Imprimer);

        $NbCols = 20;

        $opt["colorsigne"] = array(19, 20);

        $LigneInsere = true;

        $LnNumber = 0;

        $PRINTBREAKROW1 = ($Type == "Charges") ? 0 : 44;
        $PRINTBREAKROW2 = ($Type == "Charges") ? 0 : 87;

        $Intitule1 = "- MARGE BOUTIQUE ET AUTRES PRESTATIONS DE SERVICE";
        $Intitule2 = "- PRODUITS MANDAT ET HORS MANDAT";

        if ($Type == "Charges") {
            $opt["colorreverse"] = array(19, 20);
        }

        foreach ($MesLignes as $codecompte => $UneLigne) {
            $LnNumber++;

            if ($LigneInsere) {
                if ($cssligne == 'bdligneimpaireTD') {
                    $cssligne = 'bdlignepaireTD';
                } else {
                    $cssligne = 'bdligneimpaireTD';
                }
            }

            if ($LnNumber == $PRINTBREAKROW1) {
                echo "</tbody></table>";
                compChargesProdMensuel::display_EnteteTab($TitleTable . " " . $Intitule1 . " - " . $InfoTable, $Type, $cluster, $k, $MoisVoulu, $Intitule1, $Imprimer);
            }

            if ($LnNumber == $PRINTBREAKROW2) {
                echo "</tbody></table>";
                compChargesProdMensuel::display_EnteteTab($TitleTable . " " . $Intitule2 . " - " . $InfoTable, $Type, $cluster, $k, $MoisVoulu, $Intitule2, $Imprimer);
            }

            if (stristr($codecompte, "MARGESTOTALONFR")) {
                $cssligne1 = $cssligne;
            } elseif (stristr($codecompte, "STOTAL")) {
                $cssligne1 = "lnstotal";
            } elseif (stristr($codecompte, "TOTAL")) {
                $cssligne1 = "lntotal";
            } elseif (stristr($codecompte, "TITRE")) {
                $cssligne1 = "EnteteTab";
            } else {
                $cssligne1 = $cssligne;
            }

            echo $LigneInsere = table::getLine($UneLigne, array("class" => $cssligne1), $NbCols, $opt);
        }
        ?>


        </tbody>
        </table>


    </center>

<?php
if (!$EntetePiedFalse) {
    ?>


    <?php
    include("../include/pied.inc.php");
    ?>
    </body>
    </html>
    <?php
}
