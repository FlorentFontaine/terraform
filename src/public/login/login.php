<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "../auth/classes/Auth.class.php";

// Nouvelle instance d'authentification
$Auth = new Auth;

// Si on provient de la page de login
if (isset($_GET["fromLoginPage"]) || isset($_GET["retrieveAdmin"])) {
    if (isset($_GET["retrieveAdmin"]) && $_GET["retrieveAdmin"]) {
        $Auth->cleanSession();
    }

    if ($Auth->attemptLogin()) {
        header("Location: ../login/login.ctrl.php");
    } else {
        header("Location: ../login/noaccount.php");
    }
    exit;
}

// Connexion en tant que depuis l'administration
if ((isset($_GET["loginAs"]) || isset($_GET["hasAccounts"])) && isset($_GET["lockersId"])) {
    $Auth->loginAs($_GET["lockersId"], $_GET["roleNum"], $_GET["roleType"]);

    if ($_GET["roleType"] == "STATION") {
        header("Location: ../StationBack/open.php?STA_NUM={$_SESSION["logedVar"]["STA_NUM"]}");
    } else {
        header("Location: ../StationBack/Liste.php");
    }
    exit;
}

// Déconnexion de l'utilisateur
if (isset($_GET["logout"])) {
    $Auth->logout();
    header("Location: ../");
    exit;
}

// Aucune des actions ci-dessus
header("Location: ../");
exit;
