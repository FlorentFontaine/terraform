<?php

use Facades\Modules\Commentaire\Commentaire;
use Helpers\StringHelper;
use Services\ModuleService;

?>

<!---------   JS   --------->

<!-- jQuery -->
<script type="text/javascript" src="../javascript/jquery/jquery-3.7.1.min.js"></script>

<!-- jQuery UI-->
<script type="text/javascript" src="../javascript/jquery-ui/jquery-ui.min-1.13.2.js"></script>

<!-- Push server client -->
<script src="<?= StringHelper::add_version('../PUSH_SERVER_CLIENT/js/push-server-client.js') ?>"></script>

<!-- Print server client -->
<script src="<?= StringHelper::add_version('../HTML2PDF_CLIENT/js/print.js') ?>"></script>

<!-- Applicatif -->
<script type="text/javascript" src="<?= StringHelper::add_version('../javascript/javascript.js') ?>"></script>

<!---------   CSS  --------->

<!-- jQuery UI-->
<link rel="stylesheet" type="text/css" href="../javascript/jquery-ui/jquery-ui.min-1.13.2.css">

<!-- Applicatif -->
<link rel="stylesheet" type="text/css" href="<?= StringHelper::add_version('../style.css') ?>">
<link rel="stylesheet" type="text/css" href="<?= StringHelper::add_version('../print.css') ?>" media="print">


<script type="text/javascript">
    function InitDocument() {
        $("[date]").each(function () {
            if (!$(this).val()) {
                $(this).val("jj/mm/aaaa");
                $(this).css("color", "gray");

                $(this).focus(function () {
                    if ($(this).val() === "jj/mm/aaaa") {
                        $(this).val("");
                    }

                    $(this).css("color", "black");
                }).select(function () {
                    if ($(this).val() === "jj/mm/aaaa") {
                        $(this).val("");
                    }

                    $(this).css("color", "black");
                }).blur(function () {
                    if (!$(this).val()) {
                        $(this).val("jj/mm/aaaa");
                        $(this).css("color", "gray");
                    }
                });
            }
        });

        $('tr[class="bdligneimpaireTD"]').hover(function () {
            $(this).addClass("trHover");
        }, function () {
            $(this).removeClass("trHover");
        });

        $('tr[class="bdlignepaireTD"]').hover(function () {
            $(this).addClass("trHover");
        }, function () {
            $(this).removeClass("trHover");
        });

        $("input[class='submit']").mousedown(function () {
            $(this).removeClass("submit_clicked").addClass("submit_clicked");
        });

        $("input[class='submit']").mouseout(function () {
            $(this).removeClass("submit_clicked");
        });

        $("form").change(function () {
            $(this).find("input:enabled:visible[type=submit]").each(function () {
                if ($(this).val() == "Enregistrer") {
                    $(this).css("color", "red");
                }
            });
        });
    }

    $(function () {
        InitDocument();
    });

    window.onload = function () {
        <?php if(isset($Enregistrement) && $Enregistrement) { ?>
            document.getElementById('Enregistrement').style.display = 'block';
            window.setTimeout(function () {
               document.getElementById('Enregistrement').style.display = 'none';
            }, 3000);
        <?php }

        if(isset($Wait) && $Wait) { ?>
            <?php if(!isset($impression) || !$impression) { ?>
                document.getElementById('corp').style.display = 'block';
            <?php }
        }

        if(isset($impression) && $impression) { ?>
            window.print();
        <?php }

        if(isset($loadhref) && $loadhref) { ?>
            window.setTimeout(function () {
                window.location.href = '<?php echo $loadhref; ?>'
            }, 1000);
        <?php } ?>
    }

    <?php if(
        isset($_SESSION["station_DOS_NUM"]) && $_SESSION["station_DOS_NUM"]
        && (!isset($impression) || !$impression)
    ) { ?>
        window.onbeforeprint = function () {
            customAlert(
                "Impression",
                "Utilisez le menu Impression -> Liste pour imprimer les etats",
                function () {
                    window.location.href = "../ImprimBack/Liste.php";
                });
        }
    <?php } ?>
</script>

<?php
    if ($_SESSION['MODULES'][ModuleService::COMMENTAIRE]) {
?>
    <script src="../javascript/Modules/Commentaire/commentaire.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
	<script>
		$(function () {
			commentaire.load();
		})
	</script>
    <div id="commentaires">
    </div>
<?php
    }
?>

<div id="tetepage">
    <?php
    if (!isset($EntetePiedFalse) || !$EntetePiedFalse) {
        ?>
        <div style="width:100%; height:85px; background:#eeeeee">
            <a href="/StationBack/Liste.php" style="text-decoration: none">
                <img src="../images/logo_myreport.png" alt="logo" style="height:75%; margin: 8px 14px;vertical-align: middle;">
            </a>
            <span style="margin-left: 10px;padding-bottom: 10px;color:#23316F;">
                <?php
                echo $_SESSION["Utilisateur"]["Prenom"] . " " . mb_strtoupper($_SESSION["Utilisateur"]["Nom"]);
                echo " - " . $_SESSION["Utilisateur"]["Type"];

                if ($_SESSION["Utilisateur"]["Mail"] != $_SESSION["LOCKERS_MAIL"]) {
                    echo "<span style='color:orange;margin-left:5px;'>(ADMIN)</span>";
                }
                ?>
            </span>
            <?php if (isset($_SESSION["station_STA_NUM"]) && $_SESSION["station_STA_NUM"]) { ?>
                <fieldset id="sarlEntete">
                    <table>
                        <tr>
                            <td>PDV :</td>
                            <td><?php echo $_SESSION["station_LIE_NOM"]; ?></td>
                            <td colspan="2" >Exercice :
                            <span>
                                <?php echo date("d/m/Y", strtotime($_SESSION["station_DOS_DEBEX"])); ?> au <?php echo date("d/m/Y", strtotime($_SESSION["station_DOS_FINEX"])); ?>
                            </span>
                            </td>
                            
                        </tr>
                        <tr>
                            <td>Soci&eacute;t&eacute; :</td>
                            <td><?php echo $_SESSION["station_STA_SARL"]; ?></td>
                            <td>Nombre de mois trait&eacute;s : </td>
                            <td><?php echo sprintf("%02d", $_SESSION["NbMois"]) ?></td>
                        </tr>
                    </table>
                </fieldset>
            <?php } ?>
        </div>

        <table style="width:100%;margin:0px;padding:0px;height: 0px" cellpadding="0" cellspacing="0" >
            <tr>
                <td>
                    <?php include_once __DIR__ . "/../include/MenuHaut.php"; ?>
                </td>
            </tr>
        </table>

    <?php } ?>
</div>

<?php if (!isset($impression) || !$impression) { ?>
<div id="corp"
        <?php if (isset($CloseWindow) && $CloseWindow && isset($impression) && $impression) { ?>
        style="display: none;"
        <?php } ?>
    >
<?php } ?>

    <div style="position: fixed; border-radius: 5px; right: 20px; margin: 10px; background-color: green; color: white; font-size: 12px;font-weight: bolder; padding: 8px 14px; display: none; z-index: 9999999" id="Enregistrement">
        Enregistrement effectu&eacute;
    </div>
    <div id="SuiviBilan" class="none"></div>
    <div id="EttendeurImp"></div>
