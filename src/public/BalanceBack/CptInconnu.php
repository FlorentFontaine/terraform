<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <link rel="stylesheet" href="../style.css" type="text/css" media="screen">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>Balance</title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
    <style type="text/css">
        .tabBalance .EnteteTab td {
            text-align: center;
        }
    </style>
</head>

<body>

<?php

use Helpers\StringHelper;

include("../include/entete.inc.php");
?>


<center>

    <table style="width: 100%; margin: -1px;">
        <tr>
            <td class="EnteteTab TitreTable"  style="text-align:center;font-weight:bold;border:none">
                IMPORT DE BALANCE : COMPTE(S) INCONNU(S)
            </td>
        </tr>
    </table>

    <div style="display: none">
        <select id="Masque_MesComptes">
            <?php
            $MesComptes = dbAcces::getComptes();

            foreach ($MesComptes as $codeCptDb => $Ln) {
                echo "<option value='" . $Ln["numero"] . "'>" . $Ln["numero"] . " - " . utf8_encode($Ln["libelle"]) . "</option>";
            }


            ?>
        </select>
    </div>


    <form method="post">
        <input type="hidden" name="BAL_MOISNouv1" value="<?php echo $BAL_MOISNouv1; ?>"/>
        <table border="0" width="0px" class="tabBalance" style="width:0px">
            <tr class="EnteteTab">
                <td>Compte inconnu</td>
                <td>Libelle</td>
                <td>Montant</td>
                <td>
                    <div class="div290"></div>
                    Affectation
                </td>
                <td class="TitleMe" title="Cochez les cases pour sauvegarder votre choix d'affectation">
                    <img src="../images/save.gif" width="25px" alt="save logo"/>
                </td>

            </tr>

            <?php


            foreach ($Diff as $codeCpt => $Tab) {
                if (isset($cssligne) && $cssligne == 'bdligneimpaireTD') {
                    $cssligne = 'bdlignepaireTD';
                } else {
                    $cssligne = 'bdligneimpaireTD';
                }
                ?>

                <tr class="<?php echo $cssligne; ?>">
                    <td align="center">&nbsp;<?php echo $codeCpt; ?></td>
                    <td align='left'>&nbsp;<?php echo ($Tab[1]); ?></td>
                    <td align="right"><?php echo StringHelper::NombreFr(StringHelper::Texte2Nombre($Tab["cumulbal"]), 2, true, true); ?>
                        &nbsp;&nbsp;
                    </td>

                    <td>
                        <select affect_compte="1" disabled="1" name="AffectImp[<?php echo $codeCpt; ?>]"
                                id="sel<?php echo $codeCpt;
                                $MesIdSelect[] = "sel" . $codeCpt; ?>" style="width: 400px">
                            <?php
                            $trouve = false;
                            $i = 7;

                            while (!$trouve) {
                                foreach ($MesComptes as $codeCptDb => $Ln) {
                                    if (preg_match("/^" . substr($codeCpt, 0, $i) . "/", $Ln["numero"]) && !$selected) {
                                        echo "<option value='" . $Ln["numero"] . "' selected='selected'>" . $Ln["numero"] . " - " . utf8_encode($Ln["libelle"]) . "</option>";
                                        $trouve = true;
                                        break;
                                    }
                                }

                                if ($i == 0 && !$trouve) {
                                    break;
                                }

                                $i--;
                            }


                            ?>
                        </select>
                    </td>

                    <td align="center">

                        <input type="checkbox" name="equivalence[<?php echo $codeCpt; ?>]" checked="1"/>

                    </td>


                </tr>


                <?php
            }

            ?>

            <tr>
                <td colspan="5" align="center" style="border-top: 1px solid gray;"><br/>
                    <input type="hidden" name="CumulWithPre" value="<?php echo $_POST["CumulWithPre"]; ?>"/>
                    <input type="submit" class="button-spring" disabled="1" name="ValidCorrectImp" id="ValidCorrectImp" value=" Valider "
                           style="width:150px"/>
                    <br/><br/>
                </td>
            </tr>

        </table>
    </form>
    <script type="text/javascript">
        $(document).ready(function () {

            $("[affect_compte]").each(function () {

                $(this).focus(function () {

                    var Me = $(this);

                    $("#Masque_MesComptes").find("option").each(function () {
                        Me.append($(this).clone());
                    });

                });

                $(this).prop("disabled", 0);


            });
            $("#ValidCorrectImp").prop("disabled", 0);

        });



    </script>

    <?php
    include("../include/pied.inc.php");


    if ($MessErr) {
        ?>
        <script type="text/javascript" language="javascript">
            alert("<?php echo $MessErr; ?>");
        </script>
        <?php
    }

    ?>
</center>
</body>

</html>