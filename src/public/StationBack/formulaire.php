<?php

use Helpers\StringHelper;
use Classes\DB\Database;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../ctrl/ctrl.php';
require_once __DIR__ . '/../dbClasses/station.php';

global $User;

$Section = "FormStation";

$TabError = $ligneStations = array();

if (isset($infoObl) && $infoObl && !$_POST["valid"]) {
    $_POST = station::GetStation($_POST['STA_NUM']);
    $_POST["valid"] = true;
    $KillError = true;
}

if (isset($_POST["valid"]) && $_POST["valid"]) {
    $ErrorUnique = false;

    if (!isset($_POST["STA_ACTIVE"]) || !$_POST["STA_ACTIVE"]) {
        $_POST["STA_ACTIVE"] = "0";
    }

    if (!isset($_POST["STA_TYPE_SHELL"]) || !$_POST["STA_TYPE_SHELL"]) {
        $_POST["STA_TYPE_SHELL"] = "0";
    }

    $STA_NUMUpd = station::Add($_POST, $TabError, $ErrorUnique);

    if ($STA_NUMUpd) {
        if (isset($new) && $new) {
            header("Location: ../StationBack/formulaire2.php?STA_NUM=" . $STA_NUMUpd . "&new=new");
            exit();
        }

        header("Location: ../StationBack/Liste.php");
        exit();

    } else {
        if (!isset($_POST["UpdateStation"]) || !$_POST["UpdateStation"]) {
            station::Update("STA_INFOCOMPLET", 0, $_POST['STA_NUM']);
        }

        if (!$ErrorUnique) {
            $MessErr = "Il manque des informations obligatoires";
        } else {
            $MessErr = "Identifiant existant pour une autre station";
            $TabError[] = "STA_MAIL";
            $TabError[] = "STA_MDP";
        }

        $ligneStations = $_POST;
        $ligneStations["STA_NUM"] = $_POST['STA_NUM'];
    }
}

if (isset($KillError) && $KillError) {
    $MessErr = null;
}

if ((!isset($MessErr) || !$MessErr) && isset($STA_NUM) && $STA_NUM && ($ligneStations = station::GetStation($STA_NUM))) {
    $UpdateStation = $STA_NUM;
}

if ((!isset($STA_NUM) || !$STA_NUM) && (!isset($UpdateStation) || !$UpdateStation)) {
    $new = true;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
    <link rel="stylesheet" href="../style.css" type="text/css" media="screen"/>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>Fiche Soci&eacute;t&eacute;</title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
</head>

<body>

<?php
include_once __DIR__ . "/../include/entete.inc.php";
?>

<script type="text/javascript" src="../javascript/stringUtils.js"></script>
<script type="text/javascript" src="../StationBack/formulaire.js"></script>

<center>
    <?php if (!$_SESSION["User"]->getAut($Section)) { ?>

    <form method="post">
        <?php } ?>
        <input type="submit" name="valid" id="valid" class="button-spring"
               value="<?= (isset($new) && $new) ? "Continuer" : "Enregistrer" ?>"
               style="visibility: hidden;position: absolute;"/>
        <table>
            <tr>
                <td>
                    <a href="../StationBack/Liste.php" class="button-spring">Retour</a>
                </td>
                <td>

                    <?php if (isset($ligneStations["STA_INFOCOMPLET"]) && $ligneStations["STA_INFOCOMPLET"]) { ?>
                        <a class="button-spring"
                           href="../StationBack/open.php?STA_NUM=<?php echo $ligneStations["STA_NUM"]; ?>">Ouvrir le
                            dossier</a>
                    <?php } ?>

                </td>
            </tr>
        </table>

        <?php if (isset($MailSend) && $MailSend) {
            echo "<br/><a style='color:blue'>Codes d'acc&egrave;s envoy&eacute;s</a><br/><br/>";
        } else {
            echo "<br/>";
        } ?>


        <input type="hidden" id="STA_NUM" name="STA_NUM"
               value="<?php if (isset($STA_NUM) && $STA_NUM) {
                   echo $STA_NUM;
               } ?>"/>
        <?php
        if (isset($UpdateStation) && $UpdateStation) {
            ?>
            <input type="hidden" name="UpdateStation" value="<?php echo $UpdateStation; ?>"/>
            <?php
        }

        if (isset($new) && $new) {
            $ligneStations["STA_ACTIVE"] = true;
            ?>
            <input type="hidden" name="new" value="new"/>
            <?php
        }
        ?>

        <table>
            <tr>
                <td colspan="2" align="center">
                    <?php if (!$_SESSION["User"]->getAut($Section)) { ?>
                        <input type="submit" name="valid" class="button-spring" id="valid"
                               value="<?= (isset($new) && $new) ? "Continuer" : "Enregistrer" ?>"/>
                    <?php } ?>
                    <br/>
                    <br/>
                </td>
            </tr>

            <tr>
                <td class="EnteteTab TitreTable" colspan="2" style="text-align:center;font-weight:bold;border:none">
                    <?php if (isset($new) && $new) {
                        echo "Cr&eacute;ation d'une";
                    } ?>
                    Soci&eacute;t&eacute; <?php if (!isset($new) || !$new) {
                        echo " - " . $ligneStations['STA_SARL'];
                    } ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align:center;border:none">
                    <div style="height:5px"></div>
                </td>
            </tr>
            <tr>
                <td rowspan="2" valign="top">

                    <table class='tabBalance' style="background-color: #E8E8E8;" cellspacing="5">

                        <tr>
                            <td class='bdFormulaireTitre'
                                style="text-align: center;border-bottom: 1px solid black;border-top: 1px solid black;background: white"
                                colspan="2">INFORMATION STATION
                            </td>

                        </tr>

                        <tr>
                            <td></td>
                            <td></td>
                        </tr>

                        <tr>
                            <td class='bdFormulaireTitre' valign='top' <?php echo StringHelper::InputInError("CC_NUM", $TabError); ?>>
                                Comptable
                            </td>
                            <td class='bdFormulaireTexte' valign="top">

                                <?php
                                if ($User->Type == "comptable" && !$User->Var["CC_IS_ADMIN"]) {
                                    echo "<input type='hidden' name='CC_NUM[]' value='" . $User->Var["CC_NUM"] . "'/>" . $User->Var["CC_NOM"] . " " . $User->Var["CC_PRENOM"];
                                } else {
                                    if (!isset($_POST["CC_NUM"]) || !$_POST["CC_NUM"]) {
                                        if (isset($ligneStations["STA_NUM"]) && $ligneStations["STA_NUM"]) {
                                            $ValDef = station::GetNumCcStation($ligneStations["STA_NUM"]);
                                            if (empty($ValDef)) {
                                                $ValDef = array();
                                            }
                                        } else {
                                            $ValDef = array();
                                        }
                                    } else {
                                        if (is_array($_POST["CC_NUM"])) {
                                            $ValDef = $_POST["CC_NUM"];
                                        } else {
                                            $ValDef = array($_POST["CC_NUM"]);
                                        }
                                    }

                                    $joinR = $User->JoinRequired("comptable");
                                    $WhereR = $User->WhereRequired("comptable");
                                    $WhereR .= $User->WhereRequired("cabinet");
                                    $RestriSta = "";

                                    if (isset($ligneStations["STA_NUM"]) && $ligneStations["STA_NUM"]) {
                                        $RestriSta = " and STA_NUM = " . $ligneStations["STA_NUM"] . " ";
                                    }

                                    $cabdef = "";
                                    $sql = "select CC_NUM,CC_NOM,CAB_NOM from comptable  $joinR where 1 $WhereR order by cabinet.CAB_NOM,comptable.CC_NOM";
                                    ?>

                                    <select name="CC_NUM[]" type="text" id="CC_NUM_select" class="gapiarea"
                                            multiple="multiple"
                                            style="width:100%;height:150px;" <?php echo $_SESSION["User"]->getAut($Section, $Section, "select"); ?>>

                                        <?php

                                        Database::query($sql);

                                        while ($ln = Database::fetchArray()) {
                                            if ($cabdef != $ln["CAB_NOM"]) {
                                                echo "<optgroup label='" . $ln["CAB_NOM"] . "'>";
                                            }

                                            $cabdef = $ln["CAB_NOM"];

                                            $select = "";

                                            if (in_array($ln["CC_NUM"], $ValDef)) {
                                                $select = " selected='selected' ";
                                            }

                                            echo "<option value='" . $ln["CC_NUM"] . "' $select>" . $ln["CC_NOM"] . "</option>";
                                        }

                                        ?>
                                    </select>
                                    <?php
                                }
                                ?>

                                </td>
                        </tr>


                        <tr>
                            <td class='bdFormulaireTitre' <?php echo StringHelper::InputInError("LIE_NUM", $TabError); ?> >PDV
                                exploit&eacute; *
                            </td>
                            <td class='bdFormulaireTexte' valign="top">
                                <?php
                                $MesLieux = dbAcces::getLieu(false, array("order" => "LIE_NOM"));
                                ?>
                                <select name="LIE_NUM" autocomplete="1"
                                        style="width: 100%" <?php echo $_SESSION["User"]->getAut($Section, null, "select"); ?>>
                                    <option value=""></option>
                                    <?php

                                    foreach ($MesLieux as $code => $LigneLieu) {
                                        ?>

                                        <option
                                            value="<?php echo $LigneLieu["LIE_NUM"] ?>"
                                            <?php
                                            if (
                                                (isset($ligneStations["LIE_NUM"]) && $ligneStations["LIE_NUM"] == $LigneLieu["LIE_NUM"])
                                                || (isset($LIE_NUM) && $LIE_NUM == $LigneLieu["LIE_NUM"])
                                            ) {
                                                echo " selected='selected' ";
                                            } ?> >
                                            <?= $LigneLieu["LIE_CODE"] . " - " . $LigneLieu["LIE_NOM"] ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>


                        <?php
                        //Uniquement admin CICD
                        //Affichage des champs "Commentaire 1" et "Commentaire 2" pour la facturation
                        if ($_SESSION["User"]->Var["AG_TYPE"] === "ADMIN") {
                            ?>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>

                            <tr>
                                <td colspan="2"
                                    style="text-align: center;border-bottom: 1px solid black;border-top: 1px solid black;background: white">
                                    FACTURATION
                                </td>
                            </tr>
                            <tr>
                                <td class='bdFormulaireTitre'>Commentaire 1</td>
                                <td class='bdFormulaireTexte' valign="top">
                                    <input type="text" name="STA_FACTURATION_COM1"
                                           value="<?php if (isset($ligneStations["STA_FACTURATION_COM1"]) && $ligneStations["STA_FACTURATION_COM1"]) {
                                               echo $ligneStations["STA_FACTURATION_COM1"];
                                           } ?>"
                                           maxlength="40"
                                           class="gapiarea" size="40"/>
                                </td>
                            </tr>

                            <tr>
                                <td class='bdFormulaireTitre'>Commentaire 2</td>
                                <td class='bdFormulaireTexte' valign="top">
                                    <input type="text" name="STA_FACTURATION_COM2"
                                           value="<?php if (isset($ligneStations["STA_FACTURATION_COM2"]) && $ligneStations["STA_FACTURATION_COM2"]) {
                                               echo $ligneStations["STA_FACTURATION_COM2"];
                                           } ?>"
                                           maxlength="40"
                                           class="gapiarea" size="40"/>
                                </td>
                            </tr>

                            <tr>
                                <td></td>
                                <td></td>
                            </tr>
                            <?php
                        }
                        ?>


                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class='bdFormulaireTitre'
                                style="text-align: center;border-bottom: 1px solid black;border-top: 1px solid black;background: white"
                                colspan="2">Informations soci&eacute;t&eacute;
                            </td>

                        </tr>

                        <tr>
                            <td class='bdFormulaireTitre' valign='top'>Soci&eacute;t&eacute; en activit&eacute; sur le PDV</td>
                            <td class='bdFormulaireTexte' valign='top'>
                                <input type="checkbox" name="STA_ACTIVE"
                                      value="1"
                                      <?php if (isset($ligneStations["STA_ACTIVE"]) && $ligneStations["STA_ACTIVE"]) {
                                          echo " checked='checked' ";
                                      } ?>
                                <?= $_SESSION["User"]->getAut($Section, 'checkbox'); ?>/>
                            </td>
                        </tr>

                        <tr>
                            <td class='bdFormulaireTitre' <?php echo StringHelper::InputInError("STA_CODECLIENT", $TabError); ?>>Code
                                Soci&eacute;t&eacute; *
                            </td>
                            <td class='bdFormulaireTexte' valign="top">
                                <input <?php echo $_SESSION["User"]->getAut($Section); ?>
                                    name="STA_CODECLIENT"
                                  type="text" size='12'
                                  value="<?php if (isset($ligneStations['STA_CODECLIENT']) && $ligneStations['STA_CODECLIENT']) {
                                      echo trim(stripslashes(htmlentities($ligneStations['STA_CODECLIENT'], null, 'ISO-8859-1')));
                                  } ?>"
                                  class="gapiarea"
                                  maxlength="200"/>
                            </td>
                        </tr>
                        <tr>
                            <td class='bdFormulaireTitre' <?php echo StringHelper::InputInError("STA_SARL", $TabError); ?>>Nom de la soci&eacute;t&eacute;
                                *
                            </td>
                            <td class='bdFormulaireTexte' valign="top">
                                <input <?php echo $_SESSION["User"]->getAut($Section); ?>
                                    name="STA_SARL" type="text"
                                  value="<?php if (isset($ligneStations['STA_SARL']) && $ligneStations['STA_SARL']) {
                                      echo trim(stripslashes($ligneStations['STA_SARL']));
                                  } ?>"
                                  class="gapiarea" size="40"
                                  maxlength="200"/></td>
                        </tr>


                        <tr>
                            <td class='bdFormulaireTitre' <?php echo StringHelper::InputInError("STA_ADR1", $TabError); ?>>Adresse
                            </td>
                            <td class='bdFormulaireTexte' valign="top">
                                <input <?php echo $_SESSION["User"]->getAut($Section); ?>
                                    name="STA_ADR1" type="text"
                                  value="<?php if (isset($ligneStations['STA_ADR1']) && $ligneStations['STA_ADR1']) {
                                      echo trim(stripslashes(htmlentities($ligneStations['STA_ADR1'], null, 'ISO-8859-1')));
                                  } ?>"
                                  class="gapiarea" size="40"
                                  maxlength="200"/>
                            </td>
                        </tr>
                        <tr>
                            <td class='bdFormulaireTitre'>Compl&eacute;ment d'adresse</td>
                            <td class='bdFormulaireTexte' valign="top">
                                <input <?php echo $_SESSION["User"]->getAut($Section); ?>
                                    name="STA_ADR2" type="text"
                                    value="<?php if (isset($ligneStations['STA_ADR2']) && $ligneStations['STA_ADR2']) {
                                        echo trim(stripslashes(htmlentities($ligneStations['STA_ADR2'], null, 'ISO-8859-1')));
                                    } ?>"
                                    class="gapiarea" size="40"
                                    maxlength="200"/>
                            </td>
                        </tr>
                        <tr>
                            <td class='bdFormulaireTitre' <?php echo StringHelper::InputInError("STA_CP", $TabError); ?>>Code Postal
                            </td>
                            <td class='bdFormulaireTexte' valign="top">
                                <input <?php echo $_SESSION["User"]->getAut($Section); ?>
                                    name="STA_CP" type="text"
                                    value="<?php if (isset($ligneStations['STA_CP']) && $ligneStations['STA_CP']) {
                                        echo trim(stripslashes(htmlentities($ligneStations['STA_CP'], null, 'ISO-8859-1')));
                                    } ?>"
                                    class="gapiarea" size="40"
                                    maxlength="200"/>
                            </td>
                        </tr>
                        <tr>
                            <td class='bdFormulaireTitre' <?php echo StringHelper::InputInError("STA_VILLE", $TabError); ?>>Ville</td>
                            <td class='bdFormulaireTexte' valign="top">
                                <input <?php echo $_SESSION["User"]->getAut($Section); ?>
                                    name="STA_VILLE" type="text"
                                    value="<?php if (isset($ligneStations['STA_VILLE']) && $ligneStations['STA_VILLE']) {
                                        echo trim(stripslashes(htmlentities($ligneStations['STA_VILLE'], null, 'ISO-8859-1')));
                                    } ?>"
                                    class="gapiarea" size="40"
                                    maxlength="200"/>
                            </td>
                        </tr>
                        <tr>
                            <td class='bdFormulaireTitre' <?php echo StringHelper::InputInError("STA_TEL", $TabError); ?>>T&eacute;l&eacute;phone</td>
                            <td class='bdFormulaireTexte' valign="top">
                                <input <?php echo $_SESSION["User"]->getAut($Section); ?>
                                    name="STA_TEL" type="text"
                                    value="<?php if (isset($ligneStations['STA_TEL']) && $ligneStations['STA_TEL']) {
                                        echo trim(stripslashes(htmlentities($ligneStations['STA_TEL'], null, 'ISO-8859-1')));
                                    } ?>"
                                    class="gapiarea" size="40"
                                    maxlength="200"/></td>
                        </tr>
                        <tr>
                            <td class='bdFormulaireTitre'>Fax</td>
                            <td class='bdFormulaireTexte' valign="top">
                                <input <?php echo $_SESSION["User"]->getAut($Section); ?>
                                    name="STA_FAX" type="text"
                                    value="<?php if (isset($ligneStations['STA_FAX']) && $ligneStations['STA_FAX']) {
                                        echo trim(stripslashes(htmlentities($ligneStations['STA_FAX'], null, 'ISO-8859-1')));
                                    } ?>"
                                    class="gapiarea" size="40"
                                    maxlength="200"/></td>
                        </tr>

                        <tr>
                            <td class='bdFormulaireTitre' <?php echo StringHelper::InputInError("STA_SIRET", $TabError); ?>>N&deg;
                                Siret
                            </td>
                            <td class='bdFormulaireTexte' valign="top">
                                <input <?php echo $_SESSION["User"]->getAut($Section); ?>
                                    name="STA_SIRET" type="text"
                                    value="<?php if (isset($ligneStations['STA_SIRET']) && $ligneStations['STA_SIRET']) {
                                        echo trim(stripslashes(htmlentities($ligneStations['STA_SIRET'], null, 'ISO-8859-1')));
                                    } ?>"
                                    class="gapiarea" size="40"
                                    maxlength="200"/></td>
                        </tr>

                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>


                        <tr>
                            <td class='bdFormulaireTitre' <?php echo StringHelper::InputInError("STA_GERANT", $TabError); ?>>Pr&eacute;nom
                                NOM G&eacute;rant 1
                            </td>
                            <td class='bdFormulaireTexte' valign="top">
                                <input <?php echo $_SESSION["User"]->getAut($Section); ?>
                                    name="STA_GERANT" type="text"
                                    value="<?php if (isset($ligneStations['STA_GERANT']) && $ligneStations['STA_GERANT']) {
                                        echo trim(stripslashes(htmlentities($ligneStations['STA_GERANT'], null, 'ISO-8859-1')));
                                    } ?>"
                                    class="gapiarea" size="40"
                                    maxlength="200"/></td>
                        </tr>
                        <tr>
                            <td class='bdFormulaireTitre'>Pr&eacute;nom NOM G&eacute;rant 2</td>
                            <td class='bdFormulaireTexte' valign="top">
                                <input <?php echo $_SESSION["User"]->getAut($Section); ?>
                                    name="STA_GERANT1" type="text"
                                    value="<?php if (isset($ligneStations['STA_GERANT1']) && $ligneStations['STA_GERANT1']) {
                                        echo trim(stripslashes(htmlentities($ligneStations['STA_GERANT1'], null, 'ISO-8859-1')));
                                    } ?>"
                                    class="gapiarea" size="40"
                                    maxlength="200"/></td>
                        </tr>

                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>

                        <tr>
                            <td class='bdFormulaireTitre underline'>G&eacute;rant :</td>
                            <td>
                                <input type="hidden" id="GER_NUM" class="fieldGerant">
                            </td>
                        </tr>

                        <tr>
                            <td class='bdFormulaireTitre'>
                                <div style="float: left;">Mail</div>
                            </td>
                            <td class='bdFormulaireTexte' valign="top"><span id="GER_MAIL" class="fieldGerant"></span>
                            </td>
                        </tr>
                        <tr>
                            <td class='bdFormulaireTitre'>
                                <div style="float: left;">Nom</div>
                            </td>
                            <td class='bdFormulaireTexte' valign="top"><span id="GER_NOM" class="fieldGerant"></span>
                            </td>
                        </tr>
                        <tr>
                            <td class='bdFormulaireTitre'>
                                <div style="float: left;">Pr&eacute;nom</div>
                            </td>
                            <td class='bdFormulaireTexte' valign="top"><span id="GER_PRENOM" class="fieldGerant"></span>
                            </td>
                        </tr>
                        <tr>
                            <td class='bdFormulaireTitre'></td>
                            <td class='bdFormulaireTexte' valign="top">
                                <?php if (!$_SESSION["User"]->getAut($Section) && isset($STA_NUM) && $STA_NUM) { ?>
                                    <a id="gerant" data-type="add" href="javascript:void(0)">Modifier</a>
                                <?php } ?>
                            </td>
                        </tr>

                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>

                        <tr>
                            <td class='bdFormulaireTitre' valign='top'>Format compta</td>
                            <td class='bdFormulaireTexte' valign="top">
                                <select class="gapiarea" name='STA_BAF_NUM' id='idSelectFormat_compta'>
                                    <option value=""></option>
                                    <?php

                                    if (isset($ligneStations['STA_BAF_NUM']) && $ligneStations['STA_BAF_NUM']) {
                                        $valDefaut = $ligneStations['STA_BAF_NUM'];
                                    } elseif (isset($new) && $new && isset($_SESSION["logedVar"]["BAF_NUM"]) && $_SESSION["logedVar"]["BAF_NUM"]) {
                                        $valDefaut = $_SESSION["logedVar"]["BAF_NUM"];
                                    } else {
                                        $valDefaut = (isset($ligneStations['BAF_NUM']) && $ligneStations['BAF_NUM']) ? $ligneStations['BAF_NUM'] : '';
                                    }

                                    $tabBalanceFormat = dbAcces::get_BalanceFormat();
                                    if ($tabBalanceFormat) {
                                        foreach ($tabBalanceFormat as $BAF_NUM => $ligneFormatSelect) {

                                            if ($valDefaut == $BAF_NUM) {
                                                $selected = "selected='selected'";
                                            } else {
                                                $selected = '';
                                            }


                                            if ((isset($ligneStations['BAF_NUM']) && $ligneStations['BAF_NUM'] == $BAF_NUM)
                                                || (isset($_SESSION["logedVar"]["BAF_NUM"]) && $_SESSION["logedVar"]["BAF_NUM"] == $BAF_NUM)) {
                                                $format_defaut = " *";
                                            } else {
                                                $format_defaut = '';
                                            }
                                            ?>
                                            <option value="<?php echo $BAF_NUM; ?>" <?php echo $selected; ?>>
                                                <?php echo trim(stripslashes(htmlentities($ligneFormatSelect['BAF_LIBELLE'], null, 'ISO-8859-1'))) . $format_defaut;

                                                ?>

                                            </option>
                                        <?php }
                                    } ?>
                                </select>
                                <span style="font-style: italic">* : Format du cabinet par d&eacute;faut</span>
                            </td>
                        </tr>

                        <tr>
                            <td class='bdFormulaireTitre' <?php echo StringHelper::InputInError("STA_DATECREATION", $TabError); ?>>
                                Date de cr&eacute;ation
                            </td>
                            <td class='bdFormulaireTexte' valign="top">


                                <input <?php echo $_SESSION["User"]->getAut($Section); ?>
                                    name="STA_DATECREATION"
                                    type="text" size='12'
                                    value="<?php

                                    if (isset($new) && $new && !isset($ligneStations['STA_DATECREATION'])) {
                                        echo date("d/m/Y");
                                    } elseif (strpos($ligneStations['STA_DATECREATION'], "/") !== false) {
                                        echo $ligneStations['STA_DATECREATION'];
                                    } else {
                                        echo StringHelper::MySql2DateFr($ligneStations['STA_DATECREATION']);
                                    } ?>"
                                    class="gapiarea"
                                    maxlength="200"
                                    style="text-align: center;"/>
                            </td>
                        </tr>


                        <?php
                        if (isset($STA_NUM) && $STA_NUM) {
                            ?>
                            <tr>
                                <td class='bdFormulaireTitre'>Exercice</td>
                                <td class='bdFormulaireTexte' valign="top" style="text-align: left;"><?php

                                    $MonDernDossier = dbAcces::getDossier($STA_NUM, false, false, false, false, 1);
                                    $MonDernDossier = $MonDernDossier[0];

                                    if (!isset($MonDernDossier) || !$MonDernDossier) {
                                        echo "<a style='color:red'>Exercice non cr&eacute;&eacute;</a>";
                                    } else {
                                        echo StringHelper::MySql2DateFr($MonDernDossier["DOS_DEBEX"]) . " &rarr; " . StringHelper::MySql2DateFr($MonDernDossier["DOS_FINEX"]);
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>

                    </table>
                </td>
            </tr>

            <tr>
                <td valign="middle" align="center">
                </td>
            </tr>

            <tr>
                <td colspan="2" align="center"><br/>

                    <?php if (!$_SESSION["User"]->getAut($Section)) { ?>
                    <input type="submit" name="valid" id="valid" class="button-spring"
                           value="<?= (isset($new) && $new) ? "Continuer" : "Enregistrer" ?>"/></td>
            </tr>
            <?php } ?>
        </table>

        <?php if (!$_SESSION["User"]->getAut($Section)) { ?>
    </form>
<?php
}

if (isset($MessErr) && $MessErr) {
    ?>
    <script type="text/javascript">
        customAlert("Erreur", "<?= $MessErr; ?>");
    </script>
    <?php
}
?>
</center>

<?php
include_once __DIR__ . "/../include/pied.inc.php";
?>

</body>
</html>
