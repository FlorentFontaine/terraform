<?php

require_once __DIR__ . '/../ctrl/ctrl.php';
require_once __DIR__ . '/../dbClasses/AccesDonnees.php';

use Helpers\StringHelper;
use Repositories\UserRepository;

if (!isset($EntetePiedFalse) || !$EntetePiedFalse) {
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
            "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" lang="fr">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
        <title>Informations station</title>
        <link rel="stylesheet" href="../style.css" type="text/css" media="screen"/>
        <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
    </head>

    <body>
    <?php include_once __DIR__ . "/../include/entete.inc.php";
}
?>

    <div class="div-center">

        <?php

        if ($_SESSION["agip_AG_NUM"]) {
            if ($_SESSION["station_STA_DERNCONNECTION"] > 0) {
                echo "Derni&egrave;re connection de la soci&eacute;t&eacute; sur le dossier : <b>"
                    . StringHelper::MySql2DateFr($_SESSION["station_STA_DERNCONNECTION"]) . "</b><br/>";
            }
        } ?>
        <br/>

        <div class="">

            <?php

            if ($_SESSION["station_STA_ATTENTECRP"] > 0 && $_SESSION["User"]->Infos["Type"] == "comptable") {
                $MessageBox[0]["titre"] = "Pr&eacute;visionnel";
                $MessageBox[0]["message"] = "Un pr&eacute;visionnel est disponible pour ce dossier<br/>Date de d&eacute;part : <b>" . StringHelper::Mysql2DateFr($_SESSION["station_STA_ATTENTECRP"]) . "</b><br/><br/>Vous pouvez le r&eacute;cup&eacute;rer avec la fonction :<br/>Outils &rarr; R&eacute;cup&eacute;rer le pr&eacute;visionnel.";
            }

            ?>

            <table style="width:0;" dir="IMP_PDF;FONT_SIZE:16;HEIGHT:19;TITLETABLE:ETAT CIVIL;FITHEIGHT:1;FITWIDTH:1"
                   class="table_border">
                <tr>
                    <td style="width:50px;">
                        <div class="div70"></div>
                    </td>
                    <td style="width:100px;">
                        <div class="div70"></div>
                    </td>
                    <td style="width:100px;">
                        <div class="div70"></div>
                    </td>
                    <td style="width:50px;">
                        <div class="div70"></div>
                    </td>
                    <td style="width:50px;">
                        <div class="div70"></div>
                    </td>
                    <td style="width:10px;">
                        <div class="div40"></div>
                    </td>
                    <td style="width:50px;">
                        <div class="div50"></div>
                    </td>
                    <td style="width:10px;">
                        <div class="div40"></div>
                    </td>
                    <td style="width:30px;">
                        <div class="div70"></div>
                    </td>
                    <td style="width:50px;">
                        <div class="div70"></div>
                    </td>
                </tr>
                <tr>
                    <td class="EnteteTab TitreTable" style="text-align:center;font-weight:bold;border:none" colspan="10">
                        ETAT CIVIL
                    </td>
                </tr>
                <tr class="border_left border_right">
                    <td colspan="10"><br/></td>
                </tr>
                <tr>
                    <td class="border_left"></td>
                    <td class="bolder left">PDV :</td>
                    <td class="bolder left" colspan="2"><?php echo $_SESSION["station_LIE_NOM"]; ?></td>
                    <td colspan="3" class="bolder left">D&eacute;signation soci&eacute;t&eacute; :</td>
                    <td colspan='3' class="bolder border_right left"><?php echo $_SESSION["station_STA_SARL"]; ?></td>
                </tr>
                <tr>
                    <td class="border_left"></td>
                    <td class="bolder left">Adresse :</td>
                    <td colspan="2" class="left"><?php echo $_SESSION["station_STA_ADR1"]; ?></td>
                    <td colspan="3" class="bolder left">Siret :</td>
                    <td colspan='3' class="border_right left"><?php echo $_SESSION["station_STA_SIRET"]; ?></td>
                </tr>
                <tr>
                    <td class="border_left"></td>
                    <td class="bolder left">CP et Ville :</td>
                    <td colspan="2"
                        class="left"><?php echo $_SESSION["station_STA_CP"] . " " . $_SESSION["station_STA_VILLE"]; ?></td>
                    <td colspan="3" class="bolder left">Date cr&eacute;ation :</td>
                    <td colspan="3"
                        class="border_right left"><?php echo StringHelper::MySql2DateFr($_SESSION["station_STA_DATECREATION"]); ?></td>
                </tr>
                <tr>
                    <td class="border_left"></td>
                    <td class="bolder left">G&eacute;rant :</td>
                    <td class="left"><?php echo utf8_encode($_SESSION["station_STA_GERANT"]); ?></td>
                    <td></td>
                    <td colspan="3" class="bolder left">G&eacute;rant :</td>
                    <td colspan="3" class="border_right left"><?php echo $_SESSION["station_STA_GERANT1"]; ?></td>
                </tr>
                <tr class="border_left border_right">
                    <td colspan="10"></td>
                </tr>
                <tr style="display:none" toprint="toprint" class="border_left border_right">
                    <td style='height:10px;' colspan="10"></td>
                </tr>
                <tr>
                    <td colspan="10" style="height:auto" class="area_garde">
                        <hr/>
                        <br/>
                    </td>
                </tr>
                <tr>
                    <td colspan="10" class="border_left border_right"></td>
                </tr>
                <tr>
                    <td class="border_left"></td>
                    <td class="bolder left">Cabinet Comptable :</td>
                    <td class="bolder left"><?php echo $_SESSION["station_CAB_NOM"]; ?></td>
                    <td></td>
                    <td colspan="3" class="bolder left">Collaborateur :</td>
                    <td colspan="3" class="bolder left border_right"><a style="text-decoration: none"
                                                                        href="mailto:<?php echo $_SESSION["station_CC_MAIL"]; ?>">@ <?php echo $_SESSION["station_CC_NOM"]; ?></a>
                    </td>
                </tr>
                <tr>
                    <td class="border_left"></td>
                    <td class="bolder left">Adresse :</td>
                    <td colspan="8" class="border_right left"><?php echo $_SESSION["station_CAB_ADR1"]; ?></td>
                </tr>
                <tr>
                    <td class="border_left"></td>
                    <td class="bolder left">CP et Ville :</td>
                    <td class="left"><?php echo $_SESSION["station_CAB_CP"] . " " . $_SESSION["station_CAB_VILLE"]; ?></td>
                    <td></td>
                    <td colspan="3" class="bolder left">T&eacute;l&eacute;phone Cabinet :</td>
                    <td colspan="3" class="border_right left"><?php echo $_SESSION["station_CAB_TEL"]; ?></td>
                </tr>
                <tr>
                    <td colspan="10" class="border_left border_right"></td>
                </tr>
                <tr style="display:none" toprint="toprint">
                    <td style="height:10px;" colspan="10" class="border_left border_right"></td>
                </tr>
                <tr>
                    <td colspan="10" style="height:auto" class="area_garde">
                        <hr/>
                        <br/>
                    </td>
                </tr>
                <tr>
                    <td colspan="10" class="border_left border_right"></td>
                </tr>
                <tr>
                    <td class="border_left"></td>
                    <td class="bolder left">Code PDV :</td>
                    <td class="left"><?php echo $_SESSION["station_LIE_CODE"]; ?></td>
                    <td></td>
                    <td colspan="3" class="bolder left">Code Soci&eacute;t&eacute; :</td>
                    <td colspan="3" class="border_right left"><?php echo $_SESSION["station_STA_CODECLIENT"]; ?></td>
                </tr>
                <?php

                $userRepository = new UserRepository();

                $MonChefRegion = $userRepository->getchefRegion();

                $CleCdr = array_keys($MonChefRegion);
                $CleCdr = $CleCdr[0];

                $MonChefSecteur = $userRepository->getchefSecteur();

                $CleCds = array_keys($MonChefSecteur);
                $CleCds = $CleCds[0];

                ?>
                <tr>
                    <td class="border_left"></td>
                    <td class="bolder left">Chef de r&eacute;gion :</td>
                    <td class="left"><?php echo $MonChefRegion[$CleCdr]["Prenom"] . " " . $MonChefRegion[$CleCdr]["Nom"]; ?></td>
                    <td></td>
                    <td colspan="3" class="bolder left">Chef de Secteur :</td>
                    <td colspan="3"
                        class="border_right left"><?php echo $MonChefSecteur[$CleCds]["Prenom"] . " " . $MonChefSecteur[$CleCds]["Nom"]; ?></td>
                </tr>
                <tr>
                    <td colspan="10" class="border_left border_right"><br/></td>
                </tr>
            </table>
        </div>
    </div>
<?php
if (!isset($EntetePiedFalse) || !$EntetePiedFalse) {
    include_once __DIR__ . "/../include/pied.inc.php"; ?>
    </body>
    </html>
    <?php
}
//entetepied

