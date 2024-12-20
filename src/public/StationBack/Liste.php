<?php


use Helpers\StringHelper;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$_SESSION["station_STA_NUM"] = false;
$_SESSION["station_DOS_NUM"] = false;
$_SESSION["station_LIE_NUM"] = false;
$_SESSION["inLIE_NUM"] = false;
$_SESSION["MoisHisto"] = false;
$_SESSION["station_STA_MAINTENANCE"] = false;

require_once __DIR__ . '/../../Init/bootstrap.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../ctrl/ctrl.php';
require_once __DIR__ . '/../dbClasses/station.php';
require_once __DIR__ . '/../dbClasses/comptable.php';
require_once __DIR__ . '/../htmlClasses/table.php';
require_once __DIR__ . '/../include/Filtres.php';

global $User;

if ($User->Infos["Type"] == "station") {
    header("Location: ../StationBack/open.php?STA_NUM=" . $User->Var["STA_NUM"]);
    exit();
}

$Section = "Soci&eacute;t&eacute;";

// D�finition des cl�s de SESSION des champs pour lesquels il y a des filtres sur le page
$expectedFields = [
    'sarl_liste_codeChefRegion',
    'sarl_liste_codeChefSecteur',
    'sarl_liste_LIE_NUM',
    'sarl_liste_CAB_NUM',
];
Filtres::initFieldsValue($expectedFields);

if (
    (isset($_GET["inactif"]) && $_GET["inactif"])
    || (isset($_POST["inactif"]) && $_POST["inactif"])
) {
    $_POST['inactif'] = true;
} else {
    $_POST['inactif'] = false;
}

if ($User->Type == "comptable" || $User->Var["AG_TYPE"] == "ADMIN") {
    $LiaisonDossier = " LEFT ";
} else {
    $LiaisonDossier = "";
}

$dos_nums = dbAcces::getMaxDossier(null, $LiaisonDossier);

$optionGet = array(
    "select" => "
        station.STA_INFOCOMPLET,
        station.STA_CODECLIENT,
        station.STA_SARL,
        station.STA_NUM,
        lieu.LIE_CODE,
        lieu.LIE_NOM,
        lieu.LIE_NUM,
        cabinet.CAB_NOM,
        CONCAT(CONCAT(DOS_DEBEX,' - '),DOS_FINEX) as DOS_FINEX,
        station.STA_DERNBAL AS dernbal,
        MAX(BALI_MOIS) as dernbal,
        COUNT( distinct balanceimport.BALI_MOIS) as NbMois ,
        COUNT( distinct stationcc.CC_NUM) as NbCC ,
        dossier.DOS_NUM,
        SUM(balanceimport.BALI_RES  ) AS BALI_RES",
    "join" => "
        left JOIN stationcc ON stationcc.STA_NUM = station.STA_NUM
        left JOIN comptable ON comptable.CC_NUM = stationcc.CC_NUM
        left JOIN cabinet ON cabinet.CAB_NUM = comptable.CAB_NUM
        LEFT JOIN dossier ON dossier.STA_NUM = station.STA_NUM
        LEFT JOIN balanceimport  ON balanceimport.DOS_NUM = dossier.DOS_NUM and balanceimport.BALI_DATE_MAJBASE > 0
        LEFT JOIN chefSecteur ON chefSecteur.codeChefSecteur = lieu.codeChefSecteur
        LEFT JOIN chefRegion ON chefRegion.codeChefRegion = lieu.codeChefRegion"
);

$optionGet["whereSTR"] = " and STA_ACTIVE = " . (int)!$_POST['inactif'];

if (isset($_POST['LIE_NUM']) && $_POST['LIE_NUM']) {
    $optionGet["where"] = array("and" => array("station.LIE_NUM" => " = '" . $_POST['LIE_NUM'] . "'"));
    $optionGet["group"] = " GROUP BY station.STA_NUM  ";
    $optionGet["order"] = " order by maxdebexo";
} else {
    $optionGet["order"] = " order by LIE_CODE ";
    $optionGet["group"] = " GROUP BY station.STA_NUM  ";
}


if (isset($_POST['CAB_NUM']) && $_POST['CAB_NUM']) {
    $optionGet["where"]["and"]["cabinet.CAB_NUM"] = " = '" . $_POST['CAB_NUM'] . "'";
}

if (isset($_POST['codeChefSecteur']) && $_POST['codeChefSecteur']) {
    $optionGet["where"]["and"]["chefSecteur.codeChefSecteur"] = " = '" . $_POST['codeChefSecteur'] . "'";
}

if (isset($_POST['codeChefRegion']) && $_POST['codeChefRegion']) {
    $optionGet["where"]["and"]["chefRegion.codeChefRegion"] = " = '" . $_POST['codeChefRegion'] . "'";
}

if (isset($_POST['LIE_CODE']) && $_POST['LIE_CODE']) {
    $optionGet["where"]["and"]["lieu.LIE_CODE"] = " = '" . $_POST['LIE_CODE'] . "'";
}

$optionGet["whereSTR"] .= " and (dossier.DOS_NUM in (" . implode(', ', $dos_nums) . ")  or dossier.DOS_NUM is NULL) ";

if (isset($order) && $order) {
    $optionGet["order"] = " order by $order";
} else {
    $optionGet["order"] = " order by STA_NUM_CLUSTER, STA_NUM,LIE_NOM,STA_SARL";
}

$MesStations = station::GetStation(false, $optionGet);
$importsBalance = dbAcces::getAllBalanceImport($dos_nums, true);

if (empty($MesStations)) {
    $MesStations = array();
}

$Totaux['BALI_RES'] = 0;
$MesStationsTab = array();

foreach ($MesStations as $cle => &$UneLigne) {
    $MonStyle = $LienFiche = "";

    if (!isset($UneLigne["STA_INFOCOMPLET"]) || !$UneLigne["STA_INFOCOMPLET"]) {
        $MonStyle = "background-color:#FFC7C7";
        $LienFiche = "&infoObl=1";
    }

    $MesStationsTab[$cle][] = array("<a href='../StationBack/formulaire.php?STA_NUM=" . $UneLigne["STA_NUM"] . "$LienFiche'><img src='../images/b_edit.png' width='15px' alt='edition'/></a>" => array("style" => $MonStyle));

    foreach ($UneLigne as $cle1 => $valeur) {
        if ($cle1 == "STA_NUM") {
            if (!$UneLigne["STA_SARL"]) {
                $UneLigne["STA_SARL"] = "## inconnu ##";
            }

            //recherche si contenu ds nom fichier pour import crp
            $CRP = "";
            if (isset($admin) && $admin) {
                $NbMot = 0;
                $MySearch = explode(" ", $UneLigne["STA_SARL"]);

                foreach ($MySearch as $UnMot) {
                    if (in_array(strtolower($UnMot), $_SESSION["crp_filelist"])) {
                        $NbMot++;
                    }
                }

                if ($NbMot) {
                    $CRP = "<a style='color:red'>CRP !! ($NbMot mot(s) trv)</a>";
                }
            }

            $MesStationsTab[$cle][] = $UneLigne["STA_CODECLIENT"];
            $StrLien = $UneLigne["STA_SARL"];

            if (!isset($UneLigne["STA_INFOCOMPLET"]) || !$UneLigne["STA_INFOCOMPLET"]) {
                $MesStationsTab[$cle][] = $StrLien;
            } else {
                $MesStationsTab[$cle][] = "<a href='../StationBack/open.php?STA_NUM=" . $UneLigne["STA_NUM"] . "' style='text-decoration:none;font-weight: bolder;'>" . $StrLien . "</a>$CRP";
            }

        } elseif ($cle1 == "LIE_NUM") {
            $CRP = "";
            if (isset($admin) && $admin && in_array($UneLigne["LIE_CODE"], $_SESSION["crp_filelist"])) {
                $CRP = "<a style='color:red'>trouv&eacute; (num: " . $UneLigne["LIE_CODE"] . ")</a>";
            }

            $MesStationsTab[$cle][] = $UneLigne["LIE_CODE"];
            $StrLien = $UneLigne["LIE_NOM"];
            $MesStationsTab[$cle][] = $StrLien . " $CRP ";

        } elseif ($cle1 == "LIE_CODE") {
            $valeur = null;
        } elseif ($cle1 == "DOS_NUM") {
            $valeur = null;
        } elseif ($cle1 == "STA_CODECLIENT") {
            $valeur = null;
        } elseif ($cle1 == "DOS_FINEX") {
            $valeur = explode(" - ", $valeur);

            if ($valeur[0]) {
                $MesStationsTab[$cle][] = array(StringHelper::MySql2DateFr($valeur[0]) . " &rarr; " . StringHelper::MySql2DateFr($valeur[1]) => array("align" => "center"));
            } else {
                $MesStationsTab[$cle][] = "";
            }
        } elseif ($cle1 == "dernbal") {
            if (isset($UneLigne["dernbal"]) && $UneLigne["dernbal"]) {
                $BALI_TYPE = isset($importsBalance[$UneLigne['DOS_NUM']][$UneLigne["dernbal"]]['BALI_TYPE'])
                    ? $importsBalance[$UneLigne['DOS_NUM']][$UneLigne["dernbal"]]['BALI_TYPE']
                    : '';
                global $colorSituation;
                $MesStationsTab[$cle][] = array("<table><tr><td><div style='width:10px;background-color:" . $colorSituation[$BALI_TYPE]["color"] . "'>&nbsp;</div></td><td>&nbsp;" . StringHelper::MySql2DateFr($valeur) . "</td></tr></table>" => array("title" => $colorSituation[$BALI_TYPE]["desc"]));
            } else {
                $MesStationsTab[$cle][] = '';
            }
        } elseif ($cle1 == "NbMois") {
            $MesStationsTab[$cle][] = array($valeur => array("align" => "center"));
        } elseif ($cle1 == "BALI_RES") {
            $UneLigne["BALI_RES"] = $valeur;
            $MesStationsTab[$cle][] = array(StringHelper::NombreFr($valeur, 0, true) => array("align" => "right"));
            $Totaux["BALI_RES"] += $valeur;
        } elseif ($cle1 != "STA_SARL" && $cle1 != "LIE_NOM" && $cle1 != "STA_INFOCOMPLET" && $cle1 != "NbCC") {
            $MesStationsTab[$cle][] = $valeur;
        }
    }
}

unset($UneLigne);

if (empty($MesStations) && isset($_POST['LIE_NUM']) && $_POST['LIE_NUM']) {
    $MesLieux = dbAcces::getLieu($_POST['LIE_NUM']);
    $UneLigne = $MesLieux[$_POST['LIE_NUM']];
}

if (!isset($EntetePiedFalse) || !$EntetePiedFalse)
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>Soci&eacute;t&eacute;</title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
</head>

<body>
<?php
include_once __DIR__ . "/../include/entete.inc.php";
}//entetepied
?>
<center>
    <table style="margin:8px auto;">
        <tr>
            <td>
                <?php
                if (!$_SESSION["User"]->getAut("station", "create")) { ?>
                    <a href="../StationBack/formulaire.php<?php if (isset($_POST['LIE_NUM']) && $_POST['LIE_NUM']) {
                        echo "?LIE_NUM=" . $_POST['LIE_NUM'];
                    } ?>">
                        <button class="button-spring">
                            Cr&eacute;er une soci&eacute;t&eacute;
                        </button>
                    </a>
                    <br/><br/>
                <?php } ?>
            </td>
        </tr>
    </table>

    <form action="" method="post" name="formsarl">
        <table border="0">
            <tr>

                <?php if (isset($_SESSION['agip_AG_NUM']) && $_SESSION['agip_AG_NUM'] && $_SESSION['User']->Type != "Secteur") { ?>
                    <td>
                        <?php
                        $params = array(
                            'value' => isset($_POST['codeChefRegion']) && $_POST['codeChefRegion'] ? $_POST['codeChefRegion'] : '',
                            'name' => 'codeChefRegion',
                            'style' => "width: 200px;",
                            'onchange' => "document.forms.formsarl.submit();",
                        );
                        echo Filtres::cdr($params);
                        ?>
                    </td>
                <?php } ?>

                <?php if (isset($_SESSION['agip_AG_NUM']) && $_SESSION['agip_AG_NUM'] && $_SESSION['User']->Type != "Secteur") { ?>
                    <td>
                        <?php
                        $params = array(
                            'value' => isset($_POST['codeChefSecteur']) && $_POST['codeChefSecteur'] ? $_POST['codeChefSecteur'] : '',
                            'name' => 'codeChefSecteur',
                            'style' => "width: 200px;",
                            'onchange' => "document.forms.formsarl.submit();",
                        );
                        echo Filtres::cds($params);
                        ?>
                    </td>
                <?php } ?>

                <?php

                if (isset($_SESSION['agip_AG_NUM']) && $_SESSION["agip_AG_NUM"]) { ?>
                    <td>
                        <?php
                        $params = array(
                            'value' => isset($_POST['LIE_NUM']) && $_POST['LIE_NUM'] ? $_POST['LIE_NUM'] : '',
                            'name' => 'LIE_NUM',
                            'id' => 'LIE_NUMcherche',
                            'style' => "width: 200px;",
                            'onchange' => "document.forms.formsarl.submit();",
                        );
                        echo Filtres::lieu($params);
                        ?>
                    </td>

                    <td>
                        <?php
                        $params = array(
                            'value' => isset($_POST['CAB_NUM']) && $_POST['CAB_NUM'] ? $_POST['CAB_NUM'] : '',
                            'name' => 'CAB_NUM',
                            'style' => "width: 200px;",
                            'onchange' => "document.forms.formsarl.submit();",
                        );
                        echo Filtres::cabinet($params);
                        ?>
                    </td>

                <?php } ?>

            </tr>
            <tr>
                <td></td>
                <td style="padding-top: 8px; text-align: right;">
                    <label for="search_wherecode" class="custom-input-text-label">Code PDV :</label>
                    <input type="text" name="LIE_CODE" id="search_wherecode"
                           style="width: 110px" class="custom-input-text"
                           value="<?php if (isset($_POST['LIE_CODE']) && $_POST['LIE_CODE']) {
                               echo $_POST['LIE_CODE'];
                           } ?>">
                </td>
                <td style="padding-top: 8px">
                    <input type="submit" value="Rechercher" class="button-spring">
                </td>
                <td></td>
            </tr>
        </table>
    </form>
</center>

<table align="center">
    <tr>
        <td>
            <?php
            if ($_POST['inactif']) {
                $queryParam = '';
                $selected = " checked='checked' ";
            } else {
                $queryParam = "?inactif=1";
                $selected = "";
            }
            ?>
            <div class="custom-checkbox-wrapper">
                <input type="checkbox" <?= $selected ?>
                       onchange="document.location.href='<?= $_SERVER['PHP_SELF'] . $queryParam ?>'" name="inactif" id="inactif"/>
                <label for="inactif">Stations inactives</label>
            </div>

            <table align="center" class="tabBalance" style="width:1400px;"
                   bordercolordark=#000000 bordercolorlight=#000000 id="tab_stations">
                <thead>
                <tr>
                    <td class="EnteteTab TitreTable" colspan="10"
                        style="text-align:center;font-weight:bold;height:15px;color:white" height="25">Liste des <?php echo count($MesStationsTab); ?> soci&eacute;t&eacute;
                    </td>
                </tr>
                <tr class="EnteteTab">
                    <td class="tdfixe" style="width:10px"></td>

                    <td class="tdfixe">
                        <div style="width:60px">
                            <a href="?order=station.STA_CODECLIENT&LIE_NUM=<?php if (isset($_POST['LIE_NUM']) && $_POST['LIE_NUM']) {
                                echo $_POST['LIE_NUM'];
                            } ?>"
                               style="color: white">Code <br/>Soci&eacute;t&eacute;</a>
                        </div>
                    </td>
                    <td class="tdfixe">
                        <div style="width:200px">
                            <a href="?order=station.STA_SARL&LIE_NUM=<?php if (isset($_POST['LIE_NUM']) && $_POST['LIE_NUM']) {
                                echo $_POST['LIE_NUM'];
                            } ?>"
                               style="color: white">Soci&eacute;t&eacute;</a>
                        </div>
                    </td>
                    <td class="tdfixe">
                        <div style="width:60px">
                            <a href="?order=LIE_CODE&LIE_NUM=<?php if (isset($_POST['LIE_NUM']) && $_POST['LIE_NUM']) {
                                echo $_POST['LIE_NUM'];
                            } ?>"
                               style="color: white">Code <br/>PDV</a>
                        </div>
                    </td>
                    <td class="tdfixe">
                        <div style="width:200px">
                            <a href="?order=LIE_NOM&LIE_NUM=<?php if (isset($_POST['LIE_NUM']) && $_POST['LIE_NUM']) {
                                echo $_POST['LIE_NUM'];
                            } ?>"
                               style="color: white">PDV</a>
                        </div>
                    </td>
                    <td class="tdfixe">
                        <div style="width:200px">
                            <a href="?order=CAB_NOM&LIE_NUM=<?php if (isset($_POST['LIE_NUM']) && $_POST['LIE_NUM']) {
                                echo $_POST['LIE_NUM'];
                            } ?>"
                               style="color: white">Cabinet</a>
                        </div>
                    </td>
                    <td class="tdfixe">
                        <div style="width:200px">
                            <div class="div70"></div>
                            Exercice
                        </div>
                    </td>
                    <td class="tdfixe">
                        <div style="width:80px">
                            <div class="div70"></div>
                            Dernier <br/>mois trait&eacute;
                        </div>
                    </td>
                    <td class="tdfixe">
                        <div style="width:80px">
                            <div class="div70"></div>
                            Nb mois<br/> &eacute;coul&eacute;
                        </div>
                    </td>
                    <td class="tdfixe" style="width:80px">
                        <div class="div50"></div>
                        R&eacute;sultat <br/>cumul&eacute;
                    </td>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($MesStationsTab as $UneLigne) {
                    if (isset($cssligne) && $cssligne == 'bdligneimpaireTD') {
                        $cssligne = 'bdlignepaireTD';
                    } else {
                        $cssligne = 'bdligneimpaireTD';
                    }

                    echo table::getLine($UneLigne, array("class" => $cssligne), 0);
                }

                if (!$MesStations) {
                    echo "<tr><td align='center' colspan='30'><br/><b>Aucune soci&eacute;t&eacute;</b><br/><br/></td></tr>";
                } else {
                    ?>
                    <tr class="EnteteTab">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Total :</td>

                        <td style="text-align: right;"><?php echo StringHelper::NombreFr($Totaux["BALI_RES"], 0, true); ?></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
        </td>
    </tr>
</table>

<?php
include_once "../include/pied.inc.php";
?>

</body>
</html>
