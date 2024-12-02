<?php

use Classes\DB\Database;

require_once '../dbClasses/AccesDonnees.php';
require_once '../dbClasses/User.php';

session_start();
$LIE_NUM = $_GET['LIE_NUM'];

if ($LIE_NUM) {
    $_SESSION["inLIE_NUM"] = $LIE_NUM;

    $MonLieu = dbAcces::getLieu($LIE_NUM);
    $MonLieu = $MonLieu[$LIE_NUM];

    $_SESSION["LIE"] = $MonLieu;

    header("Location: ../LieuBack/formulaire.php?LIE_NUM=$LIE_NUM");
    exit();
}

header("Location: ../LieuBack/Liste.php?notselect=1");
exit();
