<?php

use Classes\Debug\Debugbar\Debug;
use Services\Debug\DebugService;

require_once __DIR__ . "/../../Init/bootstrap.php";

if (!Debug::isDebugModeEnable()) {
    header("Location: ../../StationBack/Liste.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DD::<?= strtoupper(getEnv("APP_NAME")) ?></title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
    <link rel="stylesheet" href="../../auth/libs/bootstrap.min.css">
    <script src="../../auth/libs/jquery-3.4.1.min.js"></script>
    <script src="../../auth/libs/bootstrap.min.js"></script>
    <script src="./debug.js"></script>
</head>
<body class="vh-100" style="overflow-y: hidden;">
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a
            class="navbar-brand col-md-3 col-lg-2 me-0 px-3"
            href="<?= $_SESSION["DEBUG"]["FROM_URL"] ?>">
                <?= strtoupper(getEnv("APP_NAME")) ?> DEBUG
            </a>
        <span class="p-2 text-white">
            Version : <b><?= $_SESSION["MYREPORT_VERSION"] ?? '' ?></b><br />
            Du : <b><?= $_SESSION["MYREPORT_VERSION_DATE"] ?? '' ?></b>
        </span>
    </header>
    <div class="container-fluid h-100">
        <div class="row h-100">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse h-100">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a
                                class="nav-link <?= ($_GET["section"] == 'server' ? 'selected-nav' : '') ?>"
                                href="?section=server">
                                Serveur
                            </a>
                        </li>
                        <li class="nav-item">
                            <a
                                class="nav-link <?= ($_GET["section"] == 'response' ? 'selected-nav' : '') ?>"
                                href="?section=response">
                                Requ&ecirc;tes / R&eacute;ponses
                            </a>
                        </li>
                        <li class="nav-item">
                            <a
                                class="nav-link <?= ($_GET["section"] == 'query' ? 'selected-nav' : '') ?>"
                                href="?section=query">
                                Requ&ecirc;tes SQL
                                <span
                                    class="ml-2 badge badge-pill badge-warning"
                                    style="vertical-align: text-bottom;"
                                >
                                    <?= count($_SESSION["DEBUG"]["QUERY"]) > 3
                                            ? count($_SESSION["DEBUG"]["QUERY"]) - 3
                                            : count($_SESSION["DEBUG"]["QUERY"]) ?>
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a
                                class="nav-link <?= ($_GET["section"] == 'session' ? 'selected-nav' : '') ?>"
                                href="?section=session">
                                Session
                            </a>
                        </li>
                        <li class="nav-item">
                            <a
                                class="nav-link <?= ($_GET["section"] == 'cookie' ? 'selected-nav' : '') ?>"
                                href="?section=cookie">
                                Cookies
                            </a>
                        </li>
                        <li class="nav-item">
                            <a
                                class="nav-link <?= ($_GET["section"] == 'log' ? 'selected-nav' : '') ?>"
                                href="?section=log">
                                Logs
                                <span
                                    class="ml-2 badge badge-pill badge-<?= ($_SESSION["DEBUG"]["LOG"]["NB_ERROR"] > 0 ? 'warning' : 'success') ?>"
                                    style="vertical-align: text-bottom;"
                                >
                                    <?= $_SESSION["DEBUG"]["LOG"]["NB_ERROR"] ?>
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="position-absolute w-100 mx-auto text-center" style="bottom:80px;left: -5px;">
                    Made with <span class="text-danger">&hearts;</span> <br />By Tonio &copy; 2023
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 mt-1 pb-2 mb-3 border-bottom">
                    <h1 class="h2 col-7">
                        <a
                            class="navbar-brand col-md-3 col-lg-2 me-0 px-3"
                            href="<?= $_SESSION["DEBUG"]["FROM_URL"] ?>">
                            <?= $_SESSION["DEBUG"]["FROM_URL"] ?>
                        </a>
                    </h1>
                    <div class="row col-5">
                        <div class="col-4">
                            Execution time : <br /><b style="color: #79a223;"><?= $_SESSION["DEBUG"]["SERVER"]["REQUEST_TIME"] ?></b>
                        </div>
                        <div class="col-4">
                            Method : <br /><b style="color: #79a223;"><?= $_SESSION["DEBUG"]["SERVER"]["REQUEST_METHOD"] ?></b>
                        </div>
                        <div class="col-4">
                            HTTP Status : <br /><b style="<?= DebugService::getStyleStatus($_SESSION["DEBUG"]["SERVER"]["HTTP_STATUS"]) ?>;"><?= $_SESSION["DEBUG"]["SERVER"]["HTTP_STATUS"] ?></b>
                        </div>
                    </div>
                </div>
                <div class="container-fluid border border-black p-2" style="max-height:85vh;overflow-y:auto">
                    <?php

                        $page = (isset($_GET['section']) && $_GET['section']) ? $_GET['section'] : "server";
                        include_once "./section/" . $page . ".php";
                    ?>
                </div>
            </main>
        </div>
    </div>
</body>

<style>
    .selected-nav {
        background-color: #e9ecef;
        color:blueviolet;
    }
</style>

<script>
    $(document).ready(function () {
        debug.init();
    });
</script>

</html>
