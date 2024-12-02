<?php

use Classes\DB\Database;
use Helpers\StringHelper;

require_once __DIR__ . '/../../Init/bootstrap.php';
require_once __DIR__ . "/../dbClasses/AccesDonnees.php";

// Vérification de l'existence d'un message à afficher MàJ
$sql = "SELECT * FROM _meta";
Database::query($sql);

$msg = '';
$showMessage = false;
if (($ln = Database::fetchArray()) && $ln["show"] == "1") {
    $showMessage = true;
    $msg = $ln["message"];
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="iso-8859-1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>My Report</title>

    <link rel="stylesheet" href="<?= StringHelper::add_version("../auth/libs/bootstrap.min.css"); ?>">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?= StringHelper::add_version("../auth/css/login.css"); ?>">
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="panel panel-left col-md-7 col-sm-12">
            <div class="wrapper">
                <img src="../images/logo_myreport.png" alt="logo" style="height: 150px;">
                <br />
                <div id="form-container">
                    <?php
                    if ($showMessage) {
                        echo "<p class='informations'>" . $msg . "</p>";
                    }
                    ?>
                    <form>
                        <div class="form-group">
                            <label class="labels" for="email">Adresse email</label>
                            <input type="email" class="form-control inputs" id="email" name="email">
                            <div id="email-error" class="feedback invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label class="labels" for="password">Mot de passe</label>
                            <div class="input-group">
                                <input type="password" class="form-control inputs" id="password" name="password">
                                <div class="input-group-append">
                                    <button class="btn btn-primary button" type="button" id="checkPassword">
                                        <i id="icon-password" class="fa fa-eye" aria-hidden="true"
                                           style="color: white !important"></i>
                                    </button>
                                </div>
                                <div id="password-error" class="feedback invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="form-group forgot-link">
                            <a href="../auth/mail-password.php">Mot de passe oubli&eacute; ?</a>
                        </div>
                        <button id="submitLogin" type="submit" class="btn btn-primary button">Connexion</button>
                    </form>
                </div>
                <div id="file-container">
                    <div class="message">
                        Vous devez accepter les conditions g&eacute;n&eacute;rales d'utilisations pour poursuivre la navigation.
                    </div>
                    <div id="embed"></div>
                    <div class="actions">
                        <button id="acceptFile" class="btn btn-primary button">Accepter</button>
                        <button id="declineFile" class="btn btn-danger button">Refuser</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-right col-md-5 d-none d-md-block">
            <div class="wrapper">
                <div id="welcome-container">
                    <p class="welcome-message">Bienvenue sur votre application</p>
                    <img src="../images/app_logo.png" alt="" class="welcome-image">
                </div>
            </div>
        </div>
    </div>
    <div class="row footer fixed-bottom">
        <div class="col-md-7 col-sm-12 footer-element">
        </div>
        <div class="col-md-5 d-none d-md-flex footer-element">
            <div>
                <a class="link link-cicd" href="https://cicd.biz" target="_blank">
                    <img src="../images/cicd-white.png" alt="">
                </a>
            </div>
        </div>
    </div>
</div>

<script src="<?= StringHelper::add_version("../auth/libs/jquery-3.4.1.min.js"); ?>"></script>
<script src="<?= StringHelper::add_version("../auth/libs/popper.min.js"); ?>"></script>
<script src="<?= StringHelper::add_version("../auth/libs/bootstrap.min.js"); ?>"></script>
<script src="<?= StringHelper::add_version("../auth/javascript/login.js"); ?>"></script>
<script>
    $(document).ready(function () {
        login.init();
    });
</script>
</body>
</html>
