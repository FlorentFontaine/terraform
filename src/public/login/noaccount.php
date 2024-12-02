<?php

session_start();

require_once __DIR__ . "/../auth/classes/Auth.class.php";

$Auth = new Auth;

if (isset($_POST["logout"]) && $_POST["logout"]) {
    try {
        $Auth->logout();
    } catch (\Exception $e) {
        //
    }
    header("Location: ../");
    exit();
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>My Report</title>
    <link rel="stylesheet" href="../style.css" type="text/css" media="screen">
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
    <style type="text/css">
        body {
            background-position-y: 0 !important;
        }

        #container {
            font-size: 20px;
            margin: auto;
            width: 540px;;
            text-align: center;
            align-items: center;
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            justify-content: center;
            height: 100vh;
            flex-direction: column;
        }

        #body {
            text-align: center;
            font-size: 14px;
        }

        #logout {
            width: 200px;
            margin-top: 20px;
            color: white;
            background: #7CB9E8 !important;
            cursor: pointer;
            font-size: 14px;
        }

        #logout:hover {
            background: rgb(15, 103, 191) !important;
        }
    </style>
</head>
<body>
<div id="container">
    <div id="header">
        <p>
            Vous ne poss&eacute;dez pas de compte sur l'application<br />
            <img src="../images/app_logo.png" alt="logo" />
        </p>
    </div>
    <div id="body">
        <p>Si vous pensez qu'il s'agit d'un probl&egrave;me, merci de vous rapprocher du CICD<br />
            par email (support@cicd.biz) ou
            par t&eacute;l&eacute;phone (02 31 83 57 98)</p>
    </div>
    <div id="footer">
        <form method="post" action="">
            <button id="logout" type="submit" name="logout" value="1">Retourner &agrave; l'accueil</button>
        </form>
    </div>
</div>
</body>
</html>
