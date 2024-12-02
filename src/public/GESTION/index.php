<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__."/ctrl.php";

header("Location: Utilisateurs/Utilisateurs.php");
exit();
