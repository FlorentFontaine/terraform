<?php

use Classes\DB\Database;

require_once __DIR__ . "/../../../Init/bootstrap.php";

$steps = require_once __DIR__ . '/Steps.php';

?>


<!DOCTYPE>
<html lang="fr">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <title>Purge</title>
    <link rel="stylesheet" href="../style.css" type="text/css"/>
    <link rel="stylesheet" href="../../style.css" type="text/css"/>
    <link rel="icon" type="image/png" href="../../images/favicon/favicon.ico">
</head>

<body>
    <?php
    include_once __DIR__ . "/../include/entete.inc.php";
    ?>
    <div class="div-center">
        <div class="titresection">Purge de la base de donn&eacute;es</div>
    </div>

    <div style="margin-top: 100px; margin-left: 50px">
        <h2>Remise &agrave; z&eacute;ro de MyReport DEMO</h2>

        <?php
        foreach ($steps as $step) {
            echo '<p>' . utf8_encode($step['DESCRIPTION']) . ' : ...... ';

            // Affiche le feedback à chaque itération
            ob_flush();
            flush();

            try {
                $purgeQueries = file_get_contents(__DIR__ . '/SQL/' . $step['KEY'] . '.sql');
                $status = Database::exec($purgeQueries);
                echo '<span style="color: green;">OK</span></p>';
            } catch (PDOException $e) {
                echo '<span style="color: red;">KO</span></p>';
                echo '<pre>' . $e->getMessage() . '</pre>';
            }

            // Affiche le feedback à chaque itération
            ob_flush();
            flush();
        }

        echo '<hr /><p>' . utf8_encode('Opération terminée') . '</p>';
        ?>
    </div>
</body>
</html>
