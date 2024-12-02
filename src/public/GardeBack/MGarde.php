<?php
use Helpers\StringHelper;

if (!isset($EntetePiedFalse) || !$EntetePiedFalse) {

?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" lang="fr">
    <html xmlns="http://www.w3.org/1999/xhtml" lang="fr">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <title>Informations station</title>
        <link rel="stylesheet" href="../style.css" type="text/css" media="screen" />
        <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
    </head>

    <body>
    <?php include_once "../include/entete.inc.php";
}
?>

    <div class="div-center">

        <?php

        if (isset($_SESSION["agip_AG_NUM"]) && $_SESSION["agip_AG_NUM"]) {
            if ($_SESSION["station_STA_DERNCONNECTION"] > 0) {
                echo "<p>Derni&egrave;re connection de la soci&eacute;t&eacute; sur le dossier : <b>"
                    . StringHelper::MySql2DateFr($_SESSION["station_STA_DERNCONNECTION"]) . "</b></p>";
            }
        }   ?>
        <br />

        <div>
            <?php

            if ($_SESSION["station_STA_ATTENTECRP"] > 0 && $_SESSION["User"]->Infos["Type"] == "comptable") {
                $MessageBox[0]["titre"] = "Pr&eacute;visionnel";
                $MessageBox[0]["message"] = "Un pr&eacute;visionnel est disponible pour ce dossier<br/>Date de d&eacute;part : <b>" . StringHelper::Mysql2DateFr($_SESSION["station_STA_ATTENTECRP"]) . "</b><br/><br/>Vous pouvez le r&eacute;cup&eacute;rer avec la fonction :<br/>Outils &rarr; R&eacute;cup&eacute;rer le pr&eacute;visionnel.";
            }

            ?>

            <table style="width:0;" dir="IMP_PDF;FONT_SIZE:16;HEIGHT:19;TITLETABLE:PAGE DE GARDE;FITHEIGHT:1;FITWIDTH:1" class="table_border">
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
                <tr class="EnteteTab border_top border_left border_right">
                    <td colspan="10">TOTAL DES CHARGES ET PRODUITS</td>
                </tr>
                <tr class="border_left border_right">
                    <td colspan="10"><br /></td>
                </tr>
                <tr>
                    <td class="border_left"></td>
                    <td colspan="2"></td>
                    <td class="bolder center">R&eacute;el</td>
                    <td class="bolder center" colspan="2">Pr&eacute;vu</td>
                    <td class="bolder center" colspan="2">N1</td>
                    <td colspan="2" class="border_right"></td>
                </tr>
                <tr>
                    <td class="border_left"></td>
                    <td colspan="2" class="bolder left">Total Charges</td>
                    <td class="right"><?php echo StringHelper::NombreFr($TotalCharges["real"]["Montant"], 0); ?></td>
                    <td class="right"><?php echo StringHelper::NombreFr($TotalCharges["prev"]["Montant"], 0); ?></td>
                    <td>
                        <?php
                        $d = null;
                        if ($TotalCharges["real"]["Montant"]) {
                            $d["VA"] = $TotalCharges["real"]["Montant"];
                        }
                        if ($TotalCharges["prev"]["Montant"]) {
                            $d["VD"] = $TotalCharges["prev"]["Montant"];
                        }
                        $d["DESC"] = true;
                        echo gardeBack::getTendance($d);
                        ?>
                    </td>
                    <td class="right"><?php echo StringHelper::NombreFr($TotalCharges["an1"]["Montant"], 0); ?></td>
                    <td>
                        <?php
                        if ($TotalCharges["an1"]["Montant"]) {
                            $d["VD"] = $TotalCharges["an1"]["Montant"];
                        } else {
                            unset($d["VD"]);
                        }
                        echo gardeBack::getTendance($d);
                        ?>
                    </td>
                    <td colspan="2" class="border_right"></td>
                </tr>
                <tr>
                    <td class="border_left"></td>
                    <td colspan="2" class="bolder left">Total Produits</td>
                    <td class="right"><?php echo StringHelper::NombreFr(-$TotalProd["real"]["Montant"], 0); ?></td>
                    <td class="right"><?php echo StringHelper::NombreFr($TotalProd["prev"]["Montant"], 0); ?></td>
                    <td><?php
                        $d = null;
                        if ($TotalProd["real"]["Montant"]) {
                            $d["VA"] = -$TotalProd["real"]["Montant"];
                        }
                        if ($TotalProd["prev"]["Montant"]) {
                            $d["VD"] = $TotalProd["prev"]["Montant"];
                        }
                        echo gardeBack::getTendance($d);
                        ?></td>
                    <td class="right"><?php echo StringHelper::NombreFr(-$TotalProd["an1"]["Montant"], 0); ?></td>
                    <td>
                        <?php
                        if ($TotalProd["an1"]["Montant"]) {
                            $d["VD"] = -$TotalProd["an1"]["Montant"];
                        } else {
                            unset($d["VD"]);
                        }
                        echo gardeBack::getTendance($d);
                        ?>
                    </td>
                    <td colspan="2" class="border_right"></td>
                </tr>
                <tr>
                    <td class="border_left"></td>
                    <td colspan="2" class="bolder left">Masse Salariale</td>
                    <td class="right"><?php echo StringHelper::NombreFr(isset($MasseSal["13||#||UMoisRealise"]["Montant"]) && $MasseSal["13||#||UMoisRealise"]["Montant"] ? $MasseSal["13||#||UMoisRealise"]["Montant"] : null, 0); ?></td>
                    <td class="right"><?php echo StringHelper::NombreFr(isset($MasseSal["13||#||UMoisPrevu"]["Montant"]) && $MasseSal["13||#||UMoisPrevu"]["Montant"] ? $MasseSal["13||#||UMoisPrevu"]["Montant"] : null, 0); ?></td>
                    <td><?php
                        $d = null;
                        if (isset($MasseSal["13||#||UMoisRealise"]["Montant"]) && $MasseSal["13||#||UMoisRealise"]["Montant"]) {
                            $d["VA"] = $MasseSal["13||#||UMoisRealise"]["Montant"];
                        }
                        if (isset($MasseSal["13||#||UMoisPrevu"]["Montant"]) && $MasseSal["13||#||UMoisPrevu"]["Montant"]) {
                            $d["VD"] = $MasseSal["13||#||UMoisPrevu"]["Montant"];
                        }
                        $d["DESC"] = true;
                        echo gardeBack::getTendance($d);
                        ?></td>
                    <td class="right"><?php echo StringHelper::NombreFr($MasseSal["13||#||UMoisAnneeMoinsUn"]["Montant"], 0); ?></td>
                    <td>
                        <?php
                        if ($MasseSal["13||#||UMoisAnneeMoinsUn"]["Montant"]) {
                            $d["VD"] = $MasseSal["13||#||UMoisAnneeMoinsUn"]["Montant"];
                        } else {
                            unset($d["VD"]);
                        }
                        echo gardeBack::getTendance($d);
                        ?>
                    </td>
                    <td colspan="2" class="border_right"></td>
                </tr>

                <tr>
                    <td class="border_left"></td>
                    <td colspan="2" class="bolder left">Griv&egrave;leries</td>
                    <td class="right"><?php echo StringHelper::NombreFr(isset($Grivelerie["572||#||UMoisRealise"]["Montant"]) && $Grivelerie["572||#||UMoisRealise"]["Montant"] ? $Grivelerie["572||#||UMoisRealise"]["Montant"] : null, 0); ?></td>
                    <td class="right"><?php echo StringHelper::NombreFr(isset($Grivelerie["572||#||UMoisPrevu"]["Montant"]) && $Grivelerie["572||#||UMoisPrevu"]["Montant"] ? $Grivelerie["572||#||UMoisPrevu"]["Montant"] : null, 0); ?> </td>
                    <td><?php
                        $d = null;
                        if (isset($Grivelerie["572||#||UMoisRealise"]["Montant"]) && $Grivelerie["572||#||UMoisRealise"]["Montant"]) {
                            $d["VA"] = $Grivelerie["572||#||UMoisRealise"]["Montant"];
                        }
                        if (isset($Grivelerie["572||#||UMoisPrevu"]["Montant"]) && $Grivelerie["572||#||UMoisPrevu"]["Montant"]) {
                            $d["VD"] = $Grivelerie["572||#||UMoisPrevu"]["Montant"];
                        }
                        $d["DESC"] = true;
                        echo gardeBack::getTendance($d);
                        ?></td>
                    <td class="right"><?php echo StringHelper::NombreFr($Grivelerie["572||#||UMoisAnneeMoinsUn"]["Montant"], 0); ?> </td>
                    <td>
                        <?php
                        if ($Grivelerie["572||#||UMoisAnneeMoinsUn"]["Montant"]) {
                            $d["VD"] = $Grivelerie["572||#||UMoisAnneeMoinsUn"]["Montant"];
                        } else {
                            unset($d["VD"]);
                        }
                        echo gardeBack::getTendance($d);
                        ?>
                    </td>
                    <td colspan="2" class="border_right"></td>
                </tr>
                <tr>
                    <td colspan="10" class="border_left border_right">
                        <div style="height: 5px"></div>
                    </td>
                </tr>
                <tr>
                    <td class="border_left"></td>
                    <td colspan="2" class="bolder left">R&eacute;sultat</td>
                    <td class="right"><?php echo StringHelper::NombreFr($Resultat["BALI_RES"], 0); ?></td>
                    <td class="right"><?php echo StringHelper::NombreFr($Resultat["BALI_RESPREV"], 0); ?></td>
                    <td><?php
                        $d = null;
                        if ($Resultat["BALI_RES"]) {
                            $d["VA"] = $Resultat["BALI_RES"];
                        }
                        if ($Resultat["BALI_RESPREV"]) {
                            $d["VD"] = $Resultat["BALI_RESPREV"];
                        }
                        echo gardeBack::getTendance($d);
                        ?></td>
                    <td class="right"><?php echo StringHelper::NombreFr($Resultat["BALI_RESN1"], 0); ?></td>
                    <td>
                        <?php
                        if ($Resultat["BALI_RESN1"]) {
                            $d["VD"] = $Resultat["BALI_RESN1"];
                        } else {
                            unset($d["VD"]);
                        }
                        echo gardeBack::getTendance($d);
                        ?>
                    </td>
                    <td colspan="2" class="border_right"></td>
                </tr>
                <tr>
                    <td colspan="10" class="border_left border_right">
                        <div style="height: 5px"></div>
                    </td>
                </tr>
                <tr>
                    <td class="border_left"></td>
                    <td colspan="8" style="height:auto" class="area_garde_silver">
                        <hr />
                    </td>
                    <td class="border_right"></td>
                </tr>
                <tr>
                    <td colspan="10" class="border_left border_right"></td>
                </tr>
                <tr>
                    <td class="border_left"></td>
                    <td colspan="2" class="bolder left">Agios</td>
                    <?php
                    if (isset($Agios["9767UMois"]["BAL_BALANCE"]) && $Agios["9767UMois"]["BAL_BALANCE"]) {
                        if ($Agios["9767UMois"]["BAL_BALANCE"] > gardeBack::$Seuil_Agios) {
                            echo "<td class='red right'>" . $Agios["9767UMois"]["BAL_BALANCE"] . "</td>";
                        } else {
                            echo "<td class='darkgreen right'>" . $Agios["9767UMois"]["BAL_BALANCE"] . "</td>";
                        }
                    } else {
                        echo "<td class='darkgreen right'>0</td>";
                    }
                    ?>
                    <td style="font-style: italic" class="right" colspan="2">(Seuil : <?php echo gardeBack::$Seuil_Agios; ?>)</td>
                    <td colspan="4" class="border_right"></td>
                </tr>
                <tr>
                    <td class="border_left"></td>
                    <td colspan="2" class="bolder left">Solde de caisse</td>
                    <?php
                    if (isset($SoldeCaisse["9315UMois"]["BAL_BALANCE"]) && $SoldeCaisse["9315UMois"]["BAL_BALANCE"]) {
                        if ($SoldeCaisse["9315UMois"]["BAL_BALANCE"] > gardeBack::$Seuil_SoldeCaisse) {
                            echo "<td class='red right'>" . $SoldeCaisse["9315UMois"]["BAL_BALANCE"] . "</td>";
                        } else {
                            echo "<td class='darkgreen right'>" . $SoldeCaisse["9315UMois"]["BAL_BALANCE"] . "</td>";
                        }
                    } else {
                        echo "<td class='darkgreen right'>0</td>";
                    }
                    ?>
                    <td style="font-style: italic" class="right" colspan="2">(Seuil : <?php echo gardeBack::$Seuil_SoldeCaisse; ?>)</td>
                    <td colspan="4" class="border_right"></td>
                </tr>
                <tr>
                    <td colspan="10" class="border_left border_right"></td>
                </tr>
                <tr>
                    <td class="border_left"></td>
                    <td colspan="8" style="height:auto" class="area_garde_silver">
                        <hr />
                    </td>
                    <td class="border_right"></td>
                </tr>
                <tr>
                    <td colspan="10" class="border_left border_right"></td>
                </tr>
                <tr>
                    <td class="border_left"></td>
                    <td colspan="2" class="bolder left">Date dernier inventaire</td>
                    <td class="center"><?php echo StringHelper::MySql2DateFr($DateInv); ?></td>
                    <td colspan="6" class="border_right"></td>
                </tr>
                <tr>
                    <td colspan="10" class="border_left border_right">
                        <div style="height: 10px"></div>
                    </td>
                </tr>
                <tr class="EnteteTab">
                    <td colspan="10" class="border_left border_right">ETATS DE GESTION MENSUEL</td>
                </tr>
                <tr>
                    <td colspan="10" class="border_left border_right"><br /></td>
                </tr>
                <tr>
                    <td class="border_left"></td>
                    <td colspan="1" class="left">Exercice du :</td>
                    <td><?php echo StringHelper::Mysql2DateFr($_SESSION["station_DOS_DEBEX"]) . " au " . StringHelper::Mysql2DateFr($_SESSION["station_DOS_FINEX"]); ?></td>
                    <td></td>
                    <td colspan="3" class="left">Visualisation de la p&eacute;riode :</td>
                    <td colspan="3" class="border_right"><?php echo StringHelper::Mysql2DateFr($_SESSION["MoisHisto"]); ?></td>
                </tr>
                <tr>
                    <td class="border_left"></td>
                    <td colspan="1" class="left">Type d'import :</td>
                    <td><?php echo $_SESSION["station_BALI_TYPE"]; ?></td>
                    <td></td>
                    <td colspan="3" class="left">Nombre de mois trait&eacute;s :</td>
                    <td colspan="3" class="border_right left"><?php echo (strlen($_SESSION['NbMois']) == 1) ?  "0" . $_SESSION['NbMois'] : $_SESSION['NbMois']; ?></td>
                </tr>
                <tr>
                    <td colspan="10"></td>
                </tr>
                <tr>
                    <td colspan="10"><br /></td>
                </tr>
                <tr class="EnteteTab" style="height:20px">
                    <td colspan="10" class="border_left border_right">SUIVI DE GESTION </td>
                </tr>
                <?php if ((!$_SESSION["User"]->getAut($Section) && $_SESSION["ModifOK"]) || !empty($pathFileCom)) { ?>
                    <tr class="no_print">
                        <td class="border_left"></td>
                        <td colspan="8">
                            <?php if (!empty($pathFileCom)) {
                                echo "<a href='../GardeBack/Garde.php?download=1' >Cliquez ici pour t&eacute;l&eacute;charger le fichier contenant les commentaires</a>&nbsp&nbsp;";
                            }
                            if (!empty($pathFileCom) && !$_SESSION["User"]->getAut($Section) && $_SESSION["ModifOK"]) {
                                echo "<input type='button' value='supprimer' onclick='document.location=\"../GardeBack/Garde.php?delFileCom=1\"'/>";
                            }
                            if (!empty($pathFileCom)) {
                                echo "<br/>";
                            }
                            ?>
                            <br />
                            <?php
                            //si le cabinet est authoris&eacute; &agrave; d&eacute;pos&eacute; des fichiers de commentaires
                            if ($authorization) {
                            ?>
                                <div id="updateFile" class="noPrint">
                                    <?php if (!$_SESSION["User"]->getAut($Section) && $_SESSION["ModifOK"]) {
                                    ?>
                                        <form enctype="multipart/form-data" action="../GardeBack/Garde.php" method="post" style="width:250px;" name="formCom">
                                            <fieldset>
                                                <legend>Envoyer un fichier</legend>
                                                <input type="file" name="fileCom" id="fileCom" />
                                                <input type="submit" name="fileComSubmit" id="fileComSubmit" value="Envoyer" />
                                            </fieldset>
                                        </form>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </td>
                        <td class="border_right"></td>
                    </tr>
                    <tr class="no_print">
                        <td class="border_left"></td>
                        <td colspan="8" style="height:auto" class="area_garde_silver">
                            <hr />
                        </td>
                        <td class="border_right"></td>
                    </tr>
                <?php } ?>
                <?php if (!$_SESSION["User"]->getAut($Section)) {  ?>
                    <form method="post">
                    <?php } ?>
                    <tr>
                        <td colspan="10" class="border_left border_right">
                            <div style="height: 5px"></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="border_left"></td>
                        <td colspan="9" class="bolder border_right left">- Charges et produits divers de gestion</td>
                    </tr>
                    <tr style="display:none" toprint="toprint">
                        <td style="height:10px;" colspan="10" class="border_left border_right"></td>
                    </tr>
                    <tr>
                        <td class="border_left"></td>
                        <td colspan="8" class="textarea" style="height:auto;">
                            <textarea <?php if ($_SESSION["User"]->getAut($Section)) {  ?> readonly="readonly" <?php } ?> name="BALI_COM[BALI_COM_CP_DIVERS]" style="width:99%" rows="5"><?php echo utf8_encode($BALI[0]["BALI_COM_CP_DIVERS"]); ?></textarea>
                        </td>
                        <td class="border_right"></td>
                    </tr>
                    <tr style="display:none" toprint="toprint">
                        <td style="height:10px;" colspan="10" class="border_left border_right"></td>
                    </tr>
                    <tr>
                        <td class="border_left"></td>
                        <td colspan="9" class="bolder border_right left">- Charges et produits exceptionnels</td>
                    </tr>
                    <tr style="display:none" toprint="toprint">
                        <td style="height:10px;" colspan="10" class="border_left border_right"></td>
                    </tr>
                    <tr>
                        <td class="border_left"></td>
                        <td colspan="8" class="textarea" style="height:auto">
                            <textarea <?php if ($_SESSION["User"]->getAut($Section)) {  ?> readonly="readonly" <?php } ?> name="BALI_COM[BALI_COM_CP_EXCEP]" style="width:99%" rows="5"><?php echo utf8_encode($BALI[0]["BALI_COM_CP_EXCEP"]); ?></textarea>
                        </td>
                        <td class="border_right"></td>
                    </tr>
                    <tr style="display:none" toprint="toprint">
                        <td style="height:10px;" colspan="10" class="border_left border_right"></td>
                    </tr>
                    <tr>
                        <td class="border_left"></td>
                        <td colspan="9" class="bolder border_right left">- Autres &eacute;l&eacute;ments exceptionnels</td>
                    </tr>
                    <tr style="display:none" toprint="toprint">
                        <td style="height:10px;" colspan="10" class="border_left border_right"></td>
                    </tr>
                    <tr>
                        <td class="border_left"></td>
                        <td colspan="8" class="textarea" style="height:auto">
                            <textarea <?php if ($_SESSION["User"]->getAut($Section)) {  ?> readonly="readonly" <?php } ?> name="BALI_COM[BALI_COM_AUTRES_EXCEP]" style="width:99%" rows="5"><?php echo utf8_encode($BALI[0]["BALI_COM_AUTRES_EXCEP"]); ?></textarea>
                        </td>
                        <td class="border_right"></td>
                    </tr>
                    <tr>
                        <td colspan="10" class="border_left border_right"></td>
                    </tr>
                    <?php if (!$Imprimer) { ?>
                        <tr>
                            <td colspan="10" class="border_left border_right center">
                                <?php if (!$_SESSION["User"]->getAut($Section) && $_SESSION["ModifOK"]) {  ?>
                                    <div class="div-center">
                                        <div style="color:#993399">
                                            <?php if (isset($ComOk) && $ComOk) {
                                                echo "Commentaires enregistr&eacute;s avec succ&egrave;s.";
                                            } ?>
                                        </div>
                                        <input type='submit' class="button-spring" style="width: 200px;" name='validCom' value="Enregistrer commentaires" />
                                        <br />
                                    </div><br />
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (!$_SESSION["User"]->getAut($Section)) {  ?>
                    </form>
                <?php } ?>
                <tr style="display:none" toprint="toprint">
                    <td style="height:10px;" colspan="10" class="border_left border_right"></td>
                </tr>
            </table>
        </div>
    </div>
    <?php if (!isset($EntetePiedFalse) || !$EntetePiedFalse) {
        include_once "../include/pied.inc.php"; ?>
    </body>
    </html>
<?php
    }
//entetepied
