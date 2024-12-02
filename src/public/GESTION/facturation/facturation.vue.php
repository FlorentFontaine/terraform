<!DOCTYPE>
<html lang="fr">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <title>Facturation</title>
    <link rel="stylesheet" href="../style.css" type="text/css" />
    <link rel="stylesheet" href="../../style.css" type="text/css"/>
    <link rel="icon" type="image/png" href="../../images/favicon/favicon.ico">
</head>

<body>
    <?php
    use Helpers\StringHelper;
    if (!isset($Imprimer) || !$Imprimer) {
        include_once __DIR__ . "/../include/entete.inc.php";
    ?>
        <div class="div-center">
            <div class="titresection">Facturation</div>
        </div>
    <?php
    }
    ?>
    
    <div class="div-center">

        <?php
        if (!isset($_POST["Imprimer"]) || !$_POST["Imprimer"]) {
        ?>

            <div id="formFacturation">
                <form action="" method="post" name="formfacturation">
                    <div style="text-align: left;">
                        <span>
                            Date d&eacute;but
                        </span>
                        <input type="text" size="10" name="DateDebut" value="<?php echo StringHelper::MySql2DateFr($DateDebut); ?>" style="text-align: center;" />
                        <span style="text-align: center;">
                            Date fin
                        </span>
                        <input type="text" size="10" name="DateFin" value="<?php echo StringHelper::MySql2DateFr($DateFin); ?>" style="text-align: center;" />
                        <div style="display: inline-block; margin-left: 10px;">
                            <input type="submit" name="Valider" value="Valider" />
                            <input type="submit" name="Imprimer" value="Imprimer" onclick="document.formfacturation.target='_blank'" />
                        </div>
                    </div>
                    <div style='text-align: left; margin-top: 5px;'>
                        <span>Affichage</span>
                        <label>
                            <input type="checkbox" name="display_com1" value="1" <?php if (isset($_POST["display_com1"]) && $_POST["display_com1"]) {
                                                                                        echo "checked='checked' ";
                                                                                    } ?> />&nbsp;Commentaire 1
                        </label>

                        <label>
                            <input type="checkbox" name="display_com2" value="1" <?php if (isset($_POST["display_com2"]) && $_POST["display_com2"]) {
                                                                                        echo "checked='checked' ";
                                                                                    } ?> />&nbsp;Commentaire 2
                        </label>
                    </div>
                </form>
            </div>
        <?php }

        facturation::echoLines($DateDebut, $DateFin, $Imprimer);
        ?>
    </div>
    <br /><br />

    </table>
    <?php
    if (!isset($Imprimer) || !$Imprimer) {
        include_once __DIR__ . "/../include/pied.inc.php";
    } else {
    ?>
        <script type="text/javascript">
            window.print();
        </script>
    <?php } ?>
</body>

</html>
