<?php


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . "/../ctrl.php";
require_once __DIR__ . "/../../dbClasses/AccesDonnees.php";

if(isset($_POST["saveCabinetComptable"]) && $_POST["saveCabinetComptable"]) {
    unset($_POST["saveCabinetComptable"]);
    if($_GET["action"] == "new") {
        $cabinetComptable = dbAcces::setCabinet($_POST);
        $_SESSION["messageCabinetComptable"] = "Le cabinet comptable a &eacute;t&eacute; ajout&eacute; avec succès";
    } else {
        $cabinetComptable = dbAcces::setCabinet($_POST, $_GET["action"]);
        $_SESSION["messageCabinetComptable"] = "Le cabinet comptable a &eacute;t&eacute; modifi&eacute; avec succès";
    }
} elseif (isset($_POST["deleteCabinetComptable"]) && $_POST["deleteCabinetComptable"]) {
    dbAcces::deleteCabinet($_POST);
} elseif (isset($_GET["delete"]) && $_GET["delete"] == 1) {
    $_SESSION["messageCabinetComptable"] = "Le cabinet comptable a &eacute;t&eacute; supprim&eacute; avec succès";
} else {
    $_SESSION["messageCabinetComptable"] = null;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>My Report - Cabinet Comptable</title>
    <link rel="stylesheet" href="../style.css" type="text/css"/>
    <link rel="stylesheet" href="../../style.css" type="text/css"/>
    <link rel="icon" type="image/png" href="../../images/favicon/favicon.ico">
</head>

<body>
<?php
include_once __DIR__ . "/../include/entete.inc.php";

$opt["join"] = " LEFT JOIN balanceformat USING (BAF_NUM)
                LEFT JOIN comptable USING (CAB_NUM)
                LEFT JOIN stationcc USING (CC_NUM) ";

$opt["groupBy"] = "CAB_NUM";

$opt["addSelect"] = ", COUNT(comptable.CC_NUM) AS NbComptable, COUNT(stationcc.STA_NUM) AS NbStation";

if(isset($_GET["action"]) && $_GET["action"]) {
    $cabinetComptable = null;
    $balanceFormal = dbAcces::get_BalanceFormat();

    if($_GET["action"] != "new") {
        $cabinetComptable = dbAcces::getCabinet($_GET["action"], $opt);
        $cabinetComptable = reset($cabinetComptable);
    }

    include_once __DIR__ . "/form.php";
} else {
    $cabinetComptable = dbAcces::getCabinet(null, $opt);
    include_once __DIR__ . "/liste.php";
}

include_once __DIR__ . "/../include/pied.inc.php";
?>

</body>
</html>

<script>
    setTimeout(function () {
        $(".alert").fadeOut();
    }, 3000);
</script>
