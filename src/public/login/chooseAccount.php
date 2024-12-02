<?php

session_start();

require_once __DIR__."/../dbClasses/AccesDonnees.php";

$nbAccounts = 0;
$accounts = array();
$sql = "
    SELECT user.*, ROLE_TYPE, ROLE_NUM,
    CAB_NOM, STA_SARL, gerant.STA_NUM
    FROM user
    JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM
    LEFT JOIN comptable ON comptable.CC_NUM = userhasrole.ROLE_NUM AND ROLE_TYPE = 'COMPTABLE'
    LEFT JOIN cabinet ON cabinet.CAB_NUM = comptable.CAB_NUM
    LEFT JOIN chefsecteur ON chefsecteur.codeChefSecteur = userhasrole.ROLE_NUM AND ROLE_TYPE = 'CDS'
    LEFT JOIN chefregion ON chefregion.codeChefRegion = userhasrole.ROLE_NUM AND ROLE_TYPE = 'CDR'
    LEFT JOIN agip ON agip.AG_NUM = userhasrole.ROLE_NUM AND ROLE_TYPE = 'SIEGE'
    LEFT JOIN gerant ON gerant.GER_NUM = userhasrole.ROLE_NUM AND ROLE_TYPE = 'STATION'
    LEFT JOIN station ON station.STA_NUM = gerant.STA_NUM
    WHERE user.USER_NUM = '{$_SESSION["USER_NUM"]}'
";
$res = Database::query($sql);
while($ln = Database::fetchArray($res))
{
    array_push($accounts, $ln);
    $nbAccounts++;
}

$_SESSION["NB_ACCOUNTS"] = $nbAccounts;

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="ISO-8859-1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Choix du compte</title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .container {
            height: 100vh;
            width: 100%;
        }

        .entete {
            background-color: #35353F;
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;
            height: 103px;
            position: absolute;
            top: 0;
            width: 100%;
        }

        .entete-container {
            padding-top: 10px;
            margin-left: 20px;
            color: white;
            font-size: 12px;
        }

        .logo {
            display: block;
            width: 90px;
            margin: 10px 0;
        }

        .logout {
            text-decoration: underline;
            color: white;
            font-size: 12px;
        }

        .main {
            padding: 103px 0 10px 0;
            max-height: 760px;
            height: 100%;
        }

        .stations {
            height: 100%;
            overflow-x: scroll;
            width: 600px;
            margin: auto;
        }

        .entete-stations {
            text-align: center;
            padding: 5px;
            background-color: #9FC63B;
            font-weight: bolder;
            color: white;
            font-size: 14px;
            border-bottom: 1px solid grey;
            margin-bottom: 10px;
        }

        .station-infos {
            border-radius: 10px;
            margin: 20px 10px;
            padding: 10px;
            background-color: #f1f1f1;
            box-shadow: 0 3px #a7a4a5;
        }

        .station-infos table {
            width: 100%;
        }

        .button-container {
            text-align: center;
        }

        .button {
            display: inline-block;
            width: 150px;
            text-decoration: none;
            color: white;
            background-color: #9FC63B;
            padding: 5px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="entete">
            <div class="entete-container">
                <img class="logo" src="../images/ioreport_logo.png" alt="">
                Connect&eacute; en tant que <?php echo $_SESSION["Utilisateur"]["Prenom"]." ".$_SESSION["Utilisateur"]["Nom"]; ?> <a class="logout" href="../login/login.php?logout=1">[D&eacute;connexion]</a>
            </div>
        </div>
        <div class="main">
            <div class="entete-stations">
                MES COMPTES
            </div>
            <div class="stations">
                <div class="list-stations">
                    <?php

                        foreach ($accounts as $account)
                        {
                            echo "
                                <div class='station-infos'>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <td>Utilisateur : {$account["USER_PRENOM"]} {$account["USER_NOM"]}</td>
                                            </tr>
                                            <tr>
                                                <td>Type de compte : {$account["ROLE_TYPE"]}"; 
                                                
                            if($account["ROLE_TYPE"] == "STATION")
                                echo "&nbsp;({$account["STA_SARL"]})";
                                                
                            echo "
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan='2' class='button-container'>
                                                    <a class='button' href='../login/login.php?hasAccounts=1&lockersId={$account["USER_LOCKERS_ID"]}&roleNum={$account["ROLE_NUM"]}&roleType={$account["ROLE_TYPE"]}'>Utiliser ce compte</a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            ";
                        }

                    ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>