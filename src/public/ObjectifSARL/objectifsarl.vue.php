<?php
/** @var string $param1 */
/** @var bool $param2 */

if(!isset($EntetePiedFalse) || !$EntetePiedFalse)
{
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
    <link rel="stylesheet" href="../style.css" type="text/css" media="screen"/>
    <link rel="stylesheet" href="../print.css" type="text/css" media="print"/>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>Objectifs | MyReport</title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
</head>

<body>
<?php
include("../include/entete.inc.php");
}//entetepied

?>
<center>
    <?php

    if ($_SESSION["inLIE_NUM"]) {
        $_SESSION["station_DOS_NUM"] = $_SESSION["inLIE_NUM_station_DOS_NUM"];
    }

    objectifSARL::get_Tab($param1, $param2);

    if ($_SESSION["inLIE_NUM"]) {
        $_SESSION["station_DOS_NUM"] = null;
    }

    ?>
</center>
<?php
if (!isset($EntetePiedFalse) || !$EntetePiedFalse)
{
include("../include/pied.inc.php");
?>
</body>
</html>
<?php
}

