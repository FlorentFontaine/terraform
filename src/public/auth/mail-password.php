<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="iso-8859-1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>My Report</title>

    <link rel="stylesheet" href="../auth/libs/bootstrap.min.css">
    <link rel="stylesheet" href="../auth/css/login.css">
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="panel panel-left col-md-7 col-sm-12">
            <div class="wrapper">
                <a href="../">
                    <img src="../images/logo_myreport.png" alt="logo" style="height: 150px;">
                </a>
                <br />
                <div id="form-container">
                    <p class="form-message">Demande de r&eacute;initialisation de mot de passe</p>
                    <div id="email-success" class="alert alert-success" role="alert"
                         style="display: none; font-size: 0.8em;"></div>
                    <form>
                        <div class="form-group">
                            <label class="labels" for="email">Adresse email</label>
                            <input type="email" class="form-control inputs" id="email" name="email">
                            <div id="email-error" class="feedback invalid-feedback"></div>
                        </div>
                        <a href="../" class="btn btn-secondary button" role="button">Retour</a>
                        <button id="submitMailPassword" type="submit" class="btn btn-primary button">Envoyer</button>
                    </form>
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


<script src="../auth/libs/jquery-3.4.1.min.js"></script>
<script src="../auth/libs/popper.min.js"></script>
<script src="../auth/libs/bootstrap.min.js"></script>
<script src="../auth/javascript/login.js"></script>

<script>
    $(document).ready(function () {
        mail_password.init();
    });
</script>
</body>
</html>
