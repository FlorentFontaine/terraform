<?php
use Helpers\StringHelper;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . "/../ctrl.php";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>My Report - Utilisateurs</title>
    <link rel="stylesheet" href="../style.css" type="text/css"/>
    <link rel="stylesheet" href="../../style.css" type="text/css"/>
    <link rel="icon" type="image/png" href="../../images/favicon/favicon.ico">
</head>

<body>
<?php
include_once __DIR__ . "/../include/entete.inc.php";
?>

<div class="div-center">
    <div class="titresection">Liste des utilisateurs</div>
</div>

<div id="container">
    <div id="filters">
        <div class="flex">
            <label for="ROLE">R&ocirc;le : </label>
            <select name="ROLE" id="ROLE" class="select">
                <option value="">Tous</option>
                <option value="CDR">CDR</option>
                <option value="CDS">CDS</option>
                <option value="CDV">CDV</option>
                <option value="SIEGE">Si&egrave;ge</option>
                <option value="COMPTABLE">Comptable</option>
            </select>

            <label for="ORDER">Trier par : </label>
            <select name="ORDER" id="ORDER" class="select">
                <option value="USER_NOM_ASC">Nom (croissant)</option>
                <option value="USER_NOM_DESC">Nom (d&eacute;croissant)</option>
                <option value="USER_PRENOM_ASC">Pr&eacute;nom (croissant)</option>
                <option value="USER_PRENOM_DESC">Pr&eacute;nom (d&eacute;croissant)</option>
                <option value="ROLE_TYPE_ASC">Type (croissant)</option>
                <option value="ROLE_TYPE_DESC">Type (d&eacute;croissant)</option>
                <option value="CAB_NOM_ASC" class="filterCabinet">Cabinet (croissant)</option>
                <option value="CAB_NOM_DESC" class="filterCabinet">Cabinet (d&eacute;croissant)</option>
            </select>

            <input class="custom-input-text" type="text" name="USER" id="USER" placeholder="Chercher un utilisateur par son nom ou pr&eacute;nom" style="width: 360px">
        </div>
        <div>
            <button style="float: right" id="addUser" class="button-spring">Ajouter un utilisateur</button>
        </div>

    </div>

    <div id="listContainer">
        <div class="tabhead">
            <table>
                <thead>
                <tr>
                    <th style="min-width: 160px">Nom</th>
                    <th style="min-width: 150px">Pr&eacute;nom</th>
                    <th style="min-width: 190px">Email</th>
                    <th style="width: 90px">Type</th>
                    <th style="width: 180px">Cabinet</th>
                    <th style="min-width: 50px; width: 50px">Admin.<br>Cpt</th>
                    <th style="min-width: 70px; width: 90px"></th>
                    <th style="min-width: 40px; width: 70px"></th>
                    <th style="min-width: 40px; width: 90px"></th>
                </tr>
                </thead>
            </table>
        </div>
        <div class="tabdatas">
            <table>
                <tbody></tbody>
            </table>
        </div>
        <div class="tabresults">
            <p>Nombre de r&eacute;sultats : <span id="nb"></span></p>
        </div>
    </div>
</div>
</table>
<?php
include_once __DIR__ . "/../include/pied.inc.php";
?>

<script src="../../javascript/stringUtils.js" type="text/javascript"></script>
<script type="text/javascript" src="<?= StringHelper::add_version("../Utilisateurs/Utilisateurs.js"); ?>"></script>
<script type="text/javascript">
    $(document).ready(function () {
        utilisateurs.init();
    });
</script>

</body>
</html>
