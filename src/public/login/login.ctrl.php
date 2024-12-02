<?php

use Classes\DB\Database;

require_once __DIR__."/../../Init/bootstrap.php";
require_once __DIR__."/../dbClasses/AccesDonnees.php";

// Cas d'un utilisateur pas Admin CICD
if(!isset($_SESSION["Utilisateur"]["LockersAdmin"]))
{
    // On cherche le nombre de compte de la personne
    $accounts = array();
    $sql = "
        SELECT * FROM userhasrole
        LEFT JOIN gerant ON gerant.GER_NUM = userhasrole.ROLE_NUM AND ROLE_TYPE = 'STATION'
        WHERE USER_NUM = '{$_SESSION["USER_NUM"]}'
    ";
    $res = Database::query($sql);
    while($ln = Database::fetchArray($res))
    {
        array_push($accounts, $ln);
    }

    // Cas d'un seul compte et c'est un gérant
    if(count($accounts) == 1 && ($accounts[0]["ROLE_TYPE"] == 'STATION' || $accounts[0]["ROLE_TYPE"] == "ASSISTANT"))
    {
        $station = $accounts[0];
        header("Location: ../StationBack/open.php?STA_NUM={$station["STA_NUM"]}");
        exit();
    }
    elseif(count($accounts) == 1 && ($accounts[0]["ROLE_TYPE"] != 'STATION' || $accounts[0]["ROLE_TYPE"] != "ASSISTANT")) // Cas d'un seul compte pas station
    {
        header("Location: ../StationBack/Liste.php");
        exit;
    }
    else // Plusieurs comptes
    {
        header("Location: ../login/chooseAccount.php");
        exit;
    }
}
else
{
    header("Location: ../StationBack/Liste.php");
    exit;
}

?>