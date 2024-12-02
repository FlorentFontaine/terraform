<div id="pied">
    <?php

    use Classes\Debug\Debugbar\Debug;
    use Helpers\StringHelper;

    if ((!isset($EntetePiedFalse) || !$EntetePiedFalse) && (!isset($notlogo) || !$notlogo)) { ?>
        <center>
            <div id="print_button"></div>
            <img src="../images/CICDLogo.jpg" style="width: 30px;" alt="logo CICD" id="logopied">
            <br/><br/>
            <span
                    style="cursor: help"
                    title="Version <?= getenv("APP_VERSION") ?> du <?= StringHelper::Mysql2DateFr(getenv("APP_BUILD_DATE")) ?>"
            >
                CICD - My Report - v.<?= getenv("APP_VERSION"); ?>
            </span>
            <br/><br/><br/>
            <div class="noprint OLD_PRINT">
                <a style="text-decoration: none" href="#" onclick="window.print()">
                    <img src="../images/imp.png" alt="logo imprimante" style="width: 20px"/>
                    Imprimer
                </a>
            </div>
        </center>
    <?php } ?>
</div>

<div id="myunload"
     style="display: none; position:fixed; left: 50%; top:50%; border: 1px solid black; background-color: #fff; padding:20px;">
    <p style="vertical-align: middle; text-align: center;">
        <b>Chargement en cours...</b>
        <br/><br/><br/>
        <img src='../images/rouegrise.gif' alt="loader"/>
    </p>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        loadTooltipPoste();
    });
</script>

<?php
 Debug::logResponse();
?>

</div><!-- ne pas toucher à ce div et ne rien mettre après pour extjs -->
