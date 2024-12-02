<?php

require_once __DIR__ . '/../dbClasses/User.php';

switch ($_SESSION["loged"]) {
    case "comptable":
        include_once __DIR__ . "/../dbClasses/comptable.php";
        $_SESSION["User"] = $User = new comptable($_SESSION["logedVar"]);
        break;

    case "agip":
        include_once __DIR__ . "/../dbClasses/agip.php";
        $_SESSION["User"] = $User = new agip($_SESSION["logedVar"], "agip");
        break;

    case "cds":
        include_once __DIR__ . "/../dbClasses/agip.php";
        $_SESSION["User"] = $User = new agip($_SESSION["logedVar"], "Secteur");
        break;

    case "cdr":
        include_once __DIR__ . "/../dbClasses/agip.php";
        $_SESSION["User"] = $User = new agip($_SESSION["logedVar"], "Region");
        break;

    case "cdv":
        include_once __DIR__ . "/../dbClasses/agip.php";
        $_SESSION["User"] = $User = new agip($_SESSION["logedVar"], "Vente");
        break;

    case "station":
    default:
        $_SESSION["User"] = $User = new station($_SESSION["logedVar"]);
        break;
}
