<?php
if(!isset($EntetePiedFalse) || !$EntetePiedFalse)
{
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
    <link rel="stylesheet" href="../style.css" type="text/css" media="screen"/>
    <link rel="stylesheet" href="../print.css" type="text/css" media="print"/>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>Gestion des CRP | MyReport</title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
</head>

<body>
<?php
include __DIR__ . "/../include/entete.inc.php";
?>
<script type="text/javascript" src="GestionCRP.function.js"></script>
<script type="text/javascript" src="GestionCRP.liste.js"></script>
<?php
}//entetepied
?>
<br/>

<div id="maDIV_POPUP"></div>
<div id="maDIV_POPUP_INFO"></div>
<center>
    <a type="button" id="new_crp" class="button-spring">Nouveau CRP</a>
    <input type="hidden" id="new_crp_click" value="<?php echo $new_crp_click; ?> "/>
    <br/><br/>
    <?= $Tab; ?>
</center>
<?php
if (!isset($EntetePiedFalse) || !$EntetePiedFalse)
{
include __DIR__ . "/../include/pied.inc.php";
?>
</body>
</html>
<?php
}
