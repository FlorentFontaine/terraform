<?php

use Classes\DB\Database;
use Helpers\StringHelper;

require_once __DIR__ . '/../ctrl/ctrl.php';
require_once __DIR__ . '/../LieuBack/lieu.class.php';
require_once __DIR__ . '/../include/Filtres.php';

$Section = "lieu";

$TabError = array();

if (isset($_SESSION["agip_AG_NUM"]) && $_SESSION["agip_AG_NUM"] && isset($_SESSION["station_DOS_NUM"]) && $_SESSION["station_DOS_NUM"]) {
    $_SESSION["station_DOS_NUM"] = false;
}

if (!isset($LIE_NUM) || !$LIE_NUM) {
    $LIE_NUM = $_SESSION["inLIE_NUM"];
    $_POST["LIE_NUM"] = $_SESSION["inLIE_NUM"];
}

if ((isset($_POST["valid"]) && $_POST["valid"]) || (isset($majheures) && $majheures)) {
    $_POST = StringHelper::cleanTab(array("LIE_", "codeChefVente", "codeChefSecteur", "codeChefRegion"), $_POST);

    if (isset($_POST["LIE_HDEB"]) && strpos($_POST["LIE_HDEB"], ":") === false) {
        $_POST["LIE_HDEB"] .= ":00";
    }

    if (isset($_POST["LIE_HFIN"]) && strpos($_POST["LIE_HFIN"], ":") === false) {
        $_POST["LIE_HFIN"] .= ":00";
    }

    if (isset($_POST["LIE_HDEBD"]) && strpos($_POST["LIE_HDEBD"], ":") === false) {
        $_POST["LIE_HDEBD"] .= ":00";
    }

    if (isset($_POST["LIE_HFIND"]) && strpos($_POST["LIE_HFIND"], ":") === false) {
        $_POST["LIE_HFIND"] .= ":00";
    }

    if (isset($_POST["LIE_SHDEB"]) && strpos($_POST["LIE_SHDEB"], ":") === false) {
        $_POST["LIE_SHDEB"] .= ":00";
    }

    if (isset($_POST["LIE_SHFIN"]) && strpos($_POST["LIE_SHFIN"], ":") === false) {
        $_POST["LIE_SHFIN"] .= ":00";
    }

    if (isset($_POST["LIE_SHDEBD"]) && strpos($_POST["LIE_SHDEBD"], ":") === false) {
        $_POST["LIE_SHDEBD"] .= ":00";
    }

    if (isset($_POST["LIE_SHFIND"]) && strpos($_POST["LIE_SHFIND"], ":") === false) {
        $_POST["LIE_SHFIND"] .= ":00";
    }

    $_POST["LIE_LUB"] = (isset($_POST["LIE_LUB"]) && $_POST["LIE_LUB"]) ? StringHelper::Texte2Nombre($_POST["LIE_LUB"]) : null;
    $_POST["LIE_CARB"] = (isset($_POST["LIE_CARB"]) && $_POST["LIE_CARB"]) ? StringHelper::Texte2Nombre($_POST["LIE_CARB"]) : null;
    $_POST["LIE_GPLC"] = (isset($_POST["LIE_GPLC"]) && $_POST["LIE_GPLC"]) ? StringHelper::Texte2Nombre($_POST["LIE_GPLC"]) : null;

    $_POST["LIE_SDEB"] = (isset($_POST["LIE_SDEB"]) && $_POST["LIE_SDEB"]) ? StringHelper::DateFr2MySql($_POST["LIE_SDEB"], array("jourmois" => 1)) : null;
    $_POST["LIE_SFIN"] = (isset($_POST["LIE_SFIN"]) && $_POST["LIE_SFIN"]) ? StringHelper::DateFr2MySql($_POST["LIE_SFIN"], array("jourmois" => 1)) : null;

    $LIE_NUMupd = $LIE_NUM;

    $TabVerif = array("LIE_CODE", "LIE_NOM");

    if (!$TabError = StringHelper::donneesExists($_POST, $TabVerif)) {
        $LIE_NUM = dbAcces::AddLieu($_POST, $LIE_NUMupd);

        if ($LIE_NUMupd) {
            $LIE_NUM = $LIE_NUMupd;
        }

        header("Location: ../LieuBack/formulaire.php?LIE_NUM=$LIE_NUM");

        exit();
    } else {
        $MessErr = "Il manque des informations obligatoires";
        $ligneLieu = $_POST;
        $Enregistrement = false;
    }
}

$UpdateLieu = false;

if (!isset($MessErr) || !$MessErr) {
    if (isset($LIE_NUM) && $LIE_NUM && $ligneLieu = dbAcces::getLieu($LIE_NUM)) {
        $ligneLieu = $ligneLieu[$LIE_NUM];
        $UpdateLieu = $LIE_NUM;
    } else {
        $ligneLieu = null;
    }
}


//si pas de comfixe ou valid
if (
    (isset($ligneLieu["LIE_COMFIXE"]) && $ligneLieu["LIE_COMFIXE"] == 0)
    || (isset($_POST["LIE_ACTIVE"]) && $_POST["LIE_ACTIVE"])
) {
    //calcul commission fixe//////////////////////////////////////////////////

    if (isset($ligneLieu["LIE_AMP"]) && $ligneLieu["LIE_AMP"] == 24) {
        $CommissionFixe = 100000;
    } else {
        $SaisonDeb = str_replace("0000-", "2009-", $ligneLieu["LIE_SDEB"]);
        $SaisonFin = str_replace("0000-", "2009-", $ligneLieu["LIE_SFIN"]);

        $NbJoursSaison = StringHelper::GetNbJourEcart($SaisonFin, $SaisonDeb);

        if ($NbJoursSaison > 1 && $SaisonFin > 0 && $SaisonDeb > 0) {
            //calcul du nombre d'heures en saison

            $NbHeuresSLS = StringHelper::NbHeuresEcart(date("H:i", strtotime($ligneLieu["LIE_SHFIN"])), date("H:i", strtotime($ligneLieu["LIE_SHDEB"])));
            $NbHeuresSD = StringHelper::NbHeuresEcart(date("H:i", strtotime($ligneLieu["LIE_SHFIND"])), date("H:i", strtotime($ligneLieu["LIE_SHDEBD"])));

            $NbDimancheS = StringHelper::getNbDim($SaisonFin, $SaisonDeb);

            $TotalNbHeureSDim = $NbHeuresSD * $NbDimancheS;
            $TotalNbHeureSLS = $NbHeuresSLS * ($NbJoursSaison - $NbDimancheS);

            $TotalHS = $TotalNbHeureSLS + $TotalNbHeureSDim;

            $NbDimanche = StringHelper::getNbDim($SaisonDeb, "2009-01-01") + StringHelper::getNbDim("2009-12-31", $SaisonFin);

        } else {
            $TotalHS = 0;
            $NbDimanche = StringHelper::getNbDim("2009-12-31", "2009-01-01");
        }

        $NbHeuresLS = StringHelper::NbHeuresEcart(date("H:i", strtotime($ligneLieu["LIE_HFIN"])), date("H:i", strtotime($ligneLieu["LIE_HDEB"])));
        $NbHeuresD = StringHelper::NbHeuresEcart(date("H:i", strtotime($ligneLieu["LIE_HFIND"])), date("H:i", strtotime($ligneLieu["LIE_HDEBD"])));


        $TotalNbHeureDim = $NbHeuresD * $NbDimanche;
        $TotalNbHeureLS = $NbHeuresLS * (366 - $NbJoursSaison - $NbDimanche);

        $TotalH = $TotalNbHeureLS + $TotalNbHeureDim;

        $HeureAnnee = $TotalH + $TotalHS;

        $CommissionFixe = round((($HeureAnnee - 5110) * 9.863) + 60000);
    }
    $_SESSION["LIE"]["LIE_COMFIXE"] = $CommissionFixe;
    Lieu::Update("LIE_COMFIXE", $CommissionFixe, $LIE_NUM);
    ///////////////////////////////////////////////////////////////////////
}

$_SESSION["inLIE"] = $ligneLieu;

if ($ligneLieu["LIE_AMP"] == "24" && ($ligneLieu["LIE_SHFIN"] != "24:00:00" || $ligneLieu["LIE_HFIN"] != "24:00:00" || $ligneLieu["LIE_SHFIND"] != "24:00:00" || $ligneLieu["LIE_HFIND"] != "24:00:00")) {
    $ligneLieu["LIE_HDEB"] = "00:00";
    $ligneLieu["LIE_HFIN"] = "24:00";
    $ligneLieu["LIE_HDEBD"] = "00:00";
    $ligneLieu["LIE_HFIND"] = "24:00";
    $ligneLieu["LIE_SHDEB"] = "00:00";
    $ligneLieu["LIE_SHFIN"] = "24:00";
    $ligneLieu["LIE_SHDEBD"] = "00:00";
    $ligneLieu["LIE_SHFIND"] = "24:00";

    dbAcces::AddLieu($ligneLieu, $LIE_NUM);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
    <link rel="stylesheet" href="../style.css" type="text/css" media="screen"/>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>PDV</title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
</head>

<body>

<?php
include_once __DIR__ . "/../include/entete.inc.php";
?>

<center>

    <?php if (!$_SESSION["User"]->getAut($Section, "create")) { ?>
    <form method="post">
        <?php } ?>

        <input type="hidden" name="Enregistrement" value="1"/>
        
        <?php
        if ($UpdateLieu) {
            ?>
            <input type="hidden" name="UpdateLieu" value="<?php echo $UpdateLieu; ?>"/>
            <?php
        }

        if (isset($new) && $new) {
            ?>
            <input type="hidden" name="new" value="new"/>
            <?php
        }
        ?>

        <table border="0" style="width:590px">
            <tr>
                <td class="EnteteTab TitreTable" colspan="2" style="text-align:center;font-weight:bold;border:none">
                    <?php if (isset($new) && $new) {
                        echo "CREATION D'UN PDV ";
                    } else {
                        echo $ligneLieu['LIE_NOM'];
                    } ?></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align:center;border:none">
                    <div style="height:5px"></div>
                </td>
            </tr>
            <tr>
                <td align="center" colspan="2">
                    <a href="../LieuBack/Liste.php" class="button-spring">Retour</a>
                    <?php if (!$_SESSION["User"]->getAut($Section, "create")) { ?>
                        <input type="submit" class="button-spring" name="valid" id="valid" value="Enregistrer"/>
                    <?php } ?>
                    <br/><br/>
                </td>
            </tr>
            <tr>
                <td valign="top" align="center" rowspan="2" style="width:295px;padding-right: 10px;">
                    <table class='tabBalance' style="width: 100%">
                        <tr>
                            <td class='bdFormulaireTitre'>En activit&eacute;</td>
                            <td class='bdFormulaireTexte' valign="top">
                                <select
                                    name="LIE_ACTIVE" <?php echo $_SESSION["User"]->getAut($Section, "create", "select"); ?>>
                                    <option
                                        value="oui" <?php if ($ligneLieu['LIE_ACTIVE'] == "oui") {
                                            echo " selected='selected' ";
                                        } ?>>
                                        oui
                                    </option>
                                    <option
                                        value="non" <?php if ($ligneLieu['LIE_ACTIVE'] == "non") {
                                            echo " selected='selected' ";
                                        } ?>>
                                        non
                                    </option>
                                </select>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td class='bdFormulaireTitre' <?php echo StringHelper::InputInError("LIE_CODE", $TabError); ?>>Code
                                PDV *
                            </td>
                            <td class='bdFormulaireTexte' valign="top">

                                <input <?php echo $_SESSION["User"]->getAut($Section, "create"); ?> name="LIE_CODE"
                                                                                                    type="text"
                                                                                                    value="<?php print trim(stripslashes(htmlentities($ligneLieu['LIE_CODE'], null, 'UTF-8'))); ?>"
                                                                                                    class="gapiarea"
                                                                                                    size='10'
                                                                                                    maxlength="10"/>
                            </td>
                        </tr>

                        <tr>
                            <td class='bdFormulaireTitre' <?php echo StringHelper::InputInError("LIE_NOM", $TabError); ?>>Nom PDV
                                *
                            </td>
                            <td class='bdFormulaireTexte' valign="top">

                                <input <?php echo $_SESSION["User"]->getAut($Section, "create"); ?> name="LIE_NOM"
                                                                                                    type="text"
                                                                                                    value="<?php print trim(stripslashes(htmlentities($ligneLieu['LIE_NOM'], null, 'UTF-8'))); ?>"
                                                                                                    class="gapiarea"
                                                                                                    size='30'
                                                                                                    maxlength="200"/>
                            </td>
                        </tr>

                        <tr>
                            <td class='bdFormulaireTitre'>Adresse 1</td>
                            <td class='bdFormulaireTexte' valign="top">

                                <input <?php echo $_SESSION["User"]->getAut($Section, "create"); ?> name="LIE_ADR1"
                                                                                                    type="text"
                                                                                                    value="<?php print trim(stripslashes(htmlentities($ligneLieu['LIE_ADR1'], null, 'UTF-8'))); ?>"
                                                                                                    class="gapiarea"
                                                                                                    size='30'
                                                                                                    maxlength="200"/>
                            </td>
                        </tr>

                        <tr>
                            <td class='bdFormulaireTitre'>Adresse 2</td>
                            <td class='bdFormulaireTexte' valign="top">

                                <input <?php echo $_SESSION["User"]->getAut($Section, "create"); ?> name="LIE_ADR2"
                                                                                                    type="text"
                                                                                                    value="<?php print trim(stripslashes(htmlentities($ligneLieu['LIE_ADR2'], null, 'UTF-8'))); ?>"
                                                                                                    class="gapiarea"
                                                                                                    size='30'
                                                                                                    maxlength="200"/>
                            </td>
                        </tr>

                        <tr>
                            <td class='bdFormulaireTitre'>Code Postal</td>
                            <td class='bdFormulaireTexte' valign="top">

                                <input <?php echo $_SESSION["User"]->getAut($Section, "create"); ?> name="LIE_CP"
                                                                                                    type="text"
                                                                                                    value="<?php print trim(stripslashes(htmlentities($ligneLieu['LIE_CP'], null, 'UTF-8'))); ?>"
                                                                                                    class="gapiarea"
                                                                                                    size='10'
                                                                                                    maxlength="200"/>
                            </td>
                        </tr>

                        <tr>
                            <td class='bdFormulaireTitre'>Ville</td>
                            <td class='bdFormulaireTexte' valign="top">

                                <input <?php echo $_SESSION["User"]->getAut($Section, "create"); ?> name="LIE_VILLE"
                                                                                                    type="text"
                                                                                                    value="<?php print trim(stripslashes(htmlentities($ligneLieu["LIE_VILLE"], null, 'UTF-8'))); ?>"
                                                                                                    class="gapiarea"
                                                                                                    size='30'
                                                                                                    maxlength="200"/>
                            </td>
                        </tr>

                        <tr>
                            <td class='bdFormulaireTitre'>T&eacute;l&eacute;phone</td>
                            <td class='bdFormulaireTexte' valign="top">

                                <input <?php echo $_SESSION["User"]->getAut($Section, "create"); ?> name="LIE_TEL"
                                                                                                    type="text"
                                                                                                    value="<?php print trim(stripslashes(htmlentities($ligneLieu["LIE_TEL"], null, 'UTF-8'))); ?>"
                                                                                                    class="gapiarea"
                                                                                                    size='30'
                                                                                                    maxlength="200"/>
                            </td>
                        </tr>

                    </table>
                </td>
                <td valign="top">

                </td>
            </tr>
            <tr>
                <td valign="top">

                    <table class='tabBalance' style="width: 100%">
                        <tr>
                            <td class='bdFormulaireTitre' style="width: 100px">Responsable r&eacute;seau</td>
                            <td class='bdFormulaireTexte' valign="top">
                                <?php


                                ?>
                                <select
                                    name="codeChefRegion" <?php echo $_SESSION["User"]->getAut($Section, "create", "select"); ?>
                                    style="width: 100%">
                                    <option value=""></option>
                                    <?php
                                    $sql = "select * from ChefRegion order by Nom,Prenom";
                                    Database::query($sql);
                                    while ($LnRegion = Database::fetchArray()) {
                                        $selected = "";
                                        if ($LnRegion["codeChefRegion"] == $ligneLieu['codeChefRegion']) {
                                            $selected = " selected='selected' ";
                                        }
                                        ?>
                                        <option
                                            value="<?php echo $LnRegion["codeChefRegion"]; ?>" <?php echo $selected; ?>><?php echo $LnRegion["Nom"] . " " . $LnRegion["Prenom"] ?></option>
                                        <?php
                                    }

                                    ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td class='bdFormulaireTitre'>Chef de secteur</td>
                            <td class='bdFormulaireTexte' valign="top">
                                <?php


                                ?>
                                <select
                                    name="codeChefSecteur" <?php echo $_SESSION["User"]->getAut($Section, "create", "select"); ?>
                                    style="width: 100%">
                                    <option value=""></option>
                                    <?php
                                    $sql = "select * from chefSecteur order by Nom,Prenom";
                                    $resRegion = Database::query($sql);

                                    while ($LnRegion = Database::fetchArray()) {
                                        $selected = "";
                                        if (isset($ligneLieu['codeChefSecteur']) && $LnRegion["codeChefSecteur"] == $ligneLieu['codeChefSecteur']) {
                                            $selected = " selected='selected' ";
                                        }
                                        ?>
                                        <option
                                            value="<?php echo $LnRegion["codeChefSecteur"]; ?>" <?php echo $selected; ?>><?php echo $LnRegion["Nom"] . " " . $LnRegion["Prenom"] ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <br/><br/>
                </td>
            </tr>

            <tr>
                <td colspan="2" align="center">
                    <br/>
                    <?php include_once __DIR__ . "/../LieuBack/Typo.php"; ?>

                </td>
            </tr>

            <tr>
                <td align="center" colspan="2"><br/>
                    <?php if (!$_SESSION["User"]->getAut($Section, "create")) { ?>
                    <input type="submit" class="button-spring" name="valid" id="valid" value="Enregistrer"/> <br/> <br/>
                </td>
            </tr>
            <?php } ?>
            <tr>
                <td colspan="2" align="center">
                    <?php if (!isset($new) || !$new) { ?>
                        <fieldset style="width: 100%">
                            <legend>Soci&eacute;t&eacute; sur le PDV</legend>
                            <div style="width: 100%;height:200px;overflow: scroll;">
                                <table align="center" style="width: 95%" class="tabBalance" bordercolordark=#000000
                                       bordercolorlight=#000000>
                                    <?php
                                    $optionGet = array(
                                        "select" => "CAB_NOM,station.STA_INFOCOMPLET,station.STA_CODECLIENT,station.STA_SARL,station.STA_NUM,lieu.LIE_CODE,lieu.LIE_NOM,lieu.LIE_NUM,'maxdebexo','dernbal','NbMois','ResCumul','PrevCumul','DeltaPrev','ResN1','DeltaN1','Proj','Alarme',dossier.DOS_NUM  ",
                                        "join" => " join stationcc on stationcc.STA_NUM = station.STA_NUM join comptable on comptable.CC_NUM = stationcc.CC_NUM join cabinet on cabinet.CAB_NUM=comptable.CAB_NUM left join dossier on DOSSIER.STA_NUM = station.STA_NUM "
                                    );

                                    if ($LIE_NUM) {
                                        $optionGet["where"] = array("and" => array("station.LIE_NUM" => " = '$LIE_NUM' "));
                                        $optionGet["select"] .= ",DOS_FINEX as maxdebexo, station.STA_DERNBAL as dernbal";
                                        $optionGet["group"] = " group by station.STA_NUM ";
                                        $optionGet["order"] = " order by maxdebexo";
                                    } else {
                                        $optionGet["select"] .= ",dossier.DOS_FINEX as maxdebexo,station.STA_DERNBAL as dernbal";
                                        $optionGet["order"] = " order by LIE_CODE";
                                        $optionGet["group"] .= " group by station.STA_NUM having dossier.DOS_NUM  = MAX(dossier.DOS_NUM)";
                                    }

                                    if (isset($order) && $order) {
                                        $optionGet["order"] = " order by $order";
                                    }

                                    $MesSARL = station::GetStation(false, $optionGet);
                                    if (empty($MesSARL)) {
                                        $MesSARL = array();
                                    }

                                    $TotalRes = 0;
                                    ?>

                                    <tr class="EnteteTab">
                                        <td>Cabinet</td>
                                        <td>Soci&eacute;t&eacute;</td>
                                        <td>Fin <br/>Exercice</td>
                                        <td>Dernier <br/>mois trait&eacute;</td>
                                        <td>Nbre mois<br/> &eacute;coul&eacute;</td>
                                        <td>R&eacute;sultat <br/>cumul&eacute;</td>
                                    </tr>

                                    <?php
                                    foreach ($MesSARL as $STA_NUM => $uneLigne) {
                                        if (isset($cssligne) && $cssligne == 'bdligneimpaireTD') {
                                            $cssligne = 'bdlignepaireTD';
                                        } else {
                                            $cssligne = 'bdligneimpaireTD';
                                        }

                                        $NbMois = dbAcces::getDateMAJBase($uneLigne["DOS_NUM"]);
                                        $NbMois = count($NbMois);
                                        if ($NbMois < 10) {
                                            $NbMois = "0" . $NbMois;
                                        }

                                        $MonRes = dbAcces::getResultat($uneLigne["DOS_NUM"]);
                                        $TotalRes += $MonRes["BALI_RES"];

                                        if (!isset($uneLigne["STA_SARL"]) || !$uneLigne["STA_SARL"]) {
                                            $uneLigne["STA_SARL"] = "## inconnu ##";
                                        }

                                        if (isset($_SESSION["code_listestation"]) && $_SESSION["code_listestation"]) {
                                            $StrLien = $uneLigne["STA_CODECLIENT"] . " - " . $uneLigne["STA_SARL"];
                                        } else {
                                            $StrLien = $uneLigne["STA_SARL"];
                                        }

                                        $LienSta = "<a href='../StationBack/open.php?STA_NUM=" . $uneLigne["STA_NUM"] . "'>" . $StrLien . "</a>";

                                        ?>
                                        <tr class="<?php echo $cssligne; ?>">
                                            <td align="left"><?php echo $uneLigne["CAB_NOM"]; ?></td>
                                            <td align="left"><?php echo $LienSta; ?></td>
                                            <td align="center"><?php echo StringHelper::MySql2DateFr($uneLigne["maxdebexo"]); ?></td>
                                            <td align="center"><?php echo StringHelper::MySql2DateFr($uneLigne["dernbal"]); ?></td>
                                            <td align="center"><?php echo $NbMois; ?></td>
                                            <td align="right"><?php echo StringHelper::NombreFr($MonRes["BALI_RES"], 0, true); ?></td>
                                        </tr>
                                        <?php
                                    }

                                    ?>

                                    <tr class="EnteteTab">
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>Total :</td>
                                        <td style="text-align: right;"><?php echo StringHelper::NombreFr($TotalRes, 0, true); ?></td>
                                    </tr>

                                </table>
                            </div>
                        </fieldset>
                    <?php } ?>
                </td>
            </tr>
        </table>
    </form>
    <?php

    if (isset($MessErr) && $MessErr) {
        ?>
        <script type="text/javascript">
            customAlert("Erreur", "<?php echo $MessErr; ?>");
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
