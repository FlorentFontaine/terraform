<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="iso-8859-1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>My Report</title>

    <link rel="stylesheet" href="../auth/libs/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../auth/css/login.css">
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="panel panel-left col-md-7 col-sm-12">
            <div class="header">
                <a href="../">
                    <img src="../images/app_logo.png" alt="">
                </a>
            </div>
            <div class="wrapper">
                <div id="form-container">
                    <div class="alert alert-info" id="password-strength" role="alert"
                         style="text-align: left; font-size: 0.85em;">
                        Votre mot de passe doit contenir au minimum:<br/> 8 caract&egrave;res, 1 majuscule, 1 minuscule, 1
                        nombre et 1 caract&egrave;re sp&eacute;cial parmi ? ! @ # $ % ^ & * . + / ( )
                    </div>
                    <form>
                        <div class="form-group">
                            <label class="labels" for="email">Mot de passe</label>
                            <div class="input-group">
                                <input type="password" class="form-control inputs" id="password" name="password">
                                <div class="input-group-append">
                                    <button class="btn btn-primary button" type="button" id="checkPassword">
                                        <i id="icon-password" class="fa fa-eye" aria-hidden="true"
                                           style="color: white !important"></i>
                                    </button>
                                </div>
                                <div id="password-error" class="feedback invalid-feedback"></div>
                                <div id="password-success" class="feedback valid-feedback"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="labels" for="password">Confirmation</label>
                            <div class="input-group">
                                <input type="password" class="form-control inputs" id="password_confirm"
                                       name="password_confirm">
                                <div class="input-group-append">
                                    <button class="btn btn-primary button" type="button" id="checkConfirmPassword">
                                        <i id="icon-confirm-password" class="fa fa-eye" aria-hidden="true"
                                           style="color: white !important"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button id="submitResetPassword" type="submit" class="btn btn-primary button">Valider</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="panel panel-right col-md-5 d-none d-md-block">
            <div class="wrapper">
                <div id="welcome-container">
                    <p class="welcome-message">Mise &agrave; jour de votre mot de passe</p>
                    <img src="../images/app_logo.png" alt="" class="welcome-image">
                </div>
            </div>
        </div>
    </div>
    <!-- Row footer -->
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
        reset_password.init();
    });
</script>
</body>
</html>
