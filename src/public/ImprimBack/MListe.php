<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <title>Impression</title>
    <link rel="stylesheet" href="../style.css" type="text/css" media="screen">
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">

</head>

<style type="text/css">
    #impression {
        text-align: center;
        padding: 3px;
        border: 1px dotted #A8A8A8;
        width: 50%;
        margin: 10px;
        font-family: Arial, Helvetica, sans-serif;
        color: #646079;
        background-color: #F4F4F6;
        font-size: 12px;
    }
</style>


<script type="text/javascript">
    var MonTypeImpression = "";
</script>

<body>
    <?php
    $No_IMP = "No_IMP";

    include("../include/entete.inc.php");
    ?>

    <center>

        <?php if (isset($_POST['Imprimer']) && $_POST['Imprimer']) { ?>
            <div id="impression">
        <?php }

        if (isset($_POST['emails']) && $_POST['emails'] !== '') {
            $emailCorrect = filter_var($_POST['emails'], FILTER_VALIDATE_EMAIL) !== false;
        } else {
            $emailCorrect = true;
        }

        //affiche message indiquant impression en cours
        if (isset($_POST['Imprimer']) && $_POST['Imprimer'] && $MyImp && $emailCorrect) {
            ?>

                <p>Veuillez patienter. L'impression est en cours.</p>
                <p>
                    Vous allez recevoir les &eacute;tats dans quelques instants &agrave; l'adresse email suivante :<br />
                    <span style="color:#FF6600;"><strong><?php echo $_POST['email']; ?></strong></span>
                </p>
                <br />
                <p>
                    Vous pouvez<span style="color:#FF6600;"><strong> continuer &agrave; utiliser l'application </strong></span>normalement.
                </p>

            <?php
                                    } elseif (isset($_POST['Imprimer']) && $_POST['Imprimer'] && !$MyImp) {
            ?>

                <p style="color:red">Veuillez selectionner le(s) document(s) &agrave; imprimer</p>
                <br />

            <?php
                                    }

                                    if (isset($_POST['Imprimer']) && $_POST['Imprimer'] && !$emailCorrect) {
            ?>

                <p style="color:red">L'email <strong><?php echo $_POST['email']; ?></strong> est incorrect, l'impression a &eacute;t&eacute; annul&eacute;.</p>

            <?php
                                    }
            ?>
            <?php if (isset($_POST['Imprimer']) && $_POST['Imprimer']) {
            ?>
            </div> <?php } ?>

    </center>

    <center>


        <form action="../ImprimBack/Liste.php" method="post" name="MyForm" onsubmit="//if(!VerifMail()) {displayErrorMail();return false;}if(MonTypeImpression == 'HTML') this.target = '_blank'; else this.target='';">
            <!--<form method="post" name="ImpressionRapide" id="ImpressionRapide" action="../HTML2PDF/imprimer.php" target="_Blank" >-->

            <script type="text/javascript">
                function chkall(El) {
                    var taille = document.forms['MyForm'].elements.length;
                    var element = null;
                    for (i = 0; i < taille; i++) {
                        element = document.forms['MyForm'].elements[i];
                        if (element.type == "checkbox" && element.id != "cocher" && element.id != "majb" && element.disabled != true) {
                            if (El.checked) {
                                element.checked = true;
                            } else {
                                element.checked = false;
                            }
                        }
                    }
                }
                $(document).ready(function() {
                    $("." + printServerClient.getConfigParameters().old_print).hide();
                });
            </script>

            <table width="680px" border="0" cellspacing="20">
                <tr>
                    <td class="EnteteTab TitreTable" colspan="2" style="text-align:center;font-weight:bold;border:none">IMPRESSION DES ETATS</td>
                </tr>
                <tr>
                    <td>
                        <table width='100%'>
                            <tr>
                                <td align="right"> - Tout S&eacute;lectionner &rarr; <input type="checkbox" onclick="chkall(this)" id="cocher" /></td>
                            </tr>
                        </table>



                        <fieldset style="padding:10px">
                            <legend class="bolder">Etats REPORT</legend>
                            <table border="0" cellspacing="8">


                                <tr>
                                    <td align='left' style="width:196px"><label><input type="checkbox" name="CheckMyImp[garde]" value="1" />&nbsp;&nbsp;&nbsp;Informations soci&eacute;t&eacute;</label></td>
                                    <td align='left' style="width:196px"><label><input type="checkbox" name="CheckMyImp[compproduit]" value="1" />&nbsp;&nbsp;&nbsp;Produits de l'exercice</label></td>
                                    <td align='left'><label><input type="checkbox" name="CheckMyImp[compcharge]" value="1" />&nbsp;&nbsp;&nbsp;Charges de l'exercice</label></td>

                                </tr>

                                <tr>
                                    <td align='left' style="width:196px"><label><input type="checkbox" name="CheckMyImp[synthese]" value="1" />&nbsp;&nbsp;&nbsp;Synth&egrave;se</label></td>
                                    <td align='left' style="width:196px"><label><input type="checkbox" name="CheckMyImp[bilan]" value="1" />&nbsp;&nbsp;&nbsp;Bilan</label></td>

                                </tr>
                            </table>
                        </fieldset>
                        <br />
                        <fieldset style="padding:10px">
                            <legend class="bolder">D&eacute;tail des comptes</legend>
                            <table border="0" cellspacing="8">
                                <tr>
                                    <td align='left'><label><input type="checkbox" name="CheckMyImp[detailproduit]" value="1" />&nbsp;&nbsp;&nbsp;D&eacute;tail des comptes produits</label></td>
                                    <td align='left'><label><input type="checkbox" name="CheckMyImp[detailcharge]" value="1" />&nbsp;&nbsp;&nbsp;D&eacute;tail des comptes charges</label></td>
                                    <td></td>
                                </tr>
                            </table>
                        </fieldset>
                        <br />
                        <fieldset style="padding:10px">
                            <legend class="bolder">Objectifs</legend>
                            <table border="0" cellspacing="8">
                                <tr>
                                    <td align='left' style="width:196px"><label><input type="checkbox" name="CheckMyImp[obj_CA]" value="1" />&nbsp;&nbsp;&nbsp;Objectif CA</label></td>
                                    <td align='left' style="width:196px"><label><input type="checkbox" name="CheckMyImp[obj_marge]" value="1" />&nbsp;&nbsp;&nbsp;Objectifs marges</label></td>
                                    <td align='left'><label><input type="checkbox" name="CheckMyImp[obj_charge]" value="1" />&nbsp;&nbsp;&nbsp;Objectif charges</label></td>

                                </tr>
                            </table>
                        </fieldset>
                        <br />
                        <fieldset style="padding:10px">
                            <legend class="bolder">Annexes</legend>
                            <table cellspacing="8">
                                <tr>
                                    <td align='left' style="width:196px"><label><input type="checkbox" name="CheckMyImp[renseignement]" value="1" />&nbsp;&nbsp;&nbsp;Renseignements</label></td>
                                    <td align='left' style="width:196px"><label><input type="checkbox" name="CheckMyImp[balance]" value="1" />&nbsp;&nbsp;&nbsp;Balance</label></td>
                                    <td align='left'><label><input type="checkbox" name="CheckMyImp[marge]" value="1" />&nbsp;&nbsp;&nbsp;Calcul marges</label></td>

                                </tr>
                                <tr>
                                    <td align='left' style="width:196px"><label><input type="checkbox" name="CheckMyImp[prev]" value="1" />&nbsp;&nbsp;&nbsp;Pr&eacute;visionnel</label></td>
                                    <td align='left'><label><input type="checkbox" name="CheckMyImp[anomalie]" value="1" />&nbsp;&nbsp;&nbsp;Anomalies</label></td>
                                </tr>

                            </table>
                        </fieldset>
                    </td>
                </tr>

                <tr>
                    <td align="center">
                        <script type="text/javascript">
                                $(document).ready(function() {
                                    $('input[name=Imprimer]').bind('click', function(event) {
                                        event.preventDefault();

                                        if ($('form[name=MyForm] input[type=checkbox]:checked').length > 0) {
                                            printServerClient.printDialog(null, false, 'form[name=MyForm]');
                                            return;
                                        }

                                        $('<div>').dialog({
                                            title: "Information",
                                            width: '400px',
                                            height: 'auto',
                                            modal: true,
                                            resizable: false,
                                            buttons: {
                                                "Fermer": function() {
                                                    $(this).dialog("close")
                                                }
                                            },
                                            open: function() {
                                                $(this).append(
                                                    $('<p>')
                                                        .html('Vous devez s&eacute;lectionner des documents &agrave; imprimer.')
                                                        .css('marginTop', 10)
                                                );
                                            },
                                            close: function() {
                                                $(this).dialog('destroy').remove();
                                            }
                                        });
                                    });
                                });
                        </script>
                    </td>
                </tr>

                <tr>
                    <td align="center">
                        <input type="button" class='button-spring' value="Imprimer" name="Imprimer" />
                    </td>
                </tr>
            </table>
        </form>
    </center>

    <?php include("../include/pied.inc.php"); ?>
</body>

</html>