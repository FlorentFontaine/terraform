<!DOCTYPE html
        PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="fr" xmlns="http://www.w3.org/1999/xhtml">

<head>
    <link rel="stylesheet" href="../style.css" type="text/css" media="screen"/>
    <link rel="stylesheet" href="../print.css" type="text/css" media="print"/>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>MyReport - Une erreur est survenue</title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">

    <style type="text/css">
        body {
            overflow: auto !important;
        }

        .accordion {
            margin-top: 24px;
        }

        .accordion-container {
            width: 90%;
            max-width: 1240px;
            margin: 0 auto;
            border: 3px solid #e0e0e0;
            border-radius: 24px;
            overflow: hidden;
        }

        .accordion-item {
            width: 100%;
        }

        .accordion-trigger {
            width: 100%;
            background-color: rgb(240, 240, 240);
            color: rgb(0, 0, 0);
            padding: 24px;
            font-size: 20px;
            font-weight: 500;
            text-align: left;
            border: none;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            cursor: pointer;
        }

        .accordion-item:not(:first-of-type) .accordion-trigger {
            border-top: 3px solid #eaeaea;
        }

        .accordion-content p, .accordion-content pre {
            margin: 24px;
            padding: 15px 25px;
            overflow-x: auto;
        }
    </style>
</head>

<body>
<div style="margin-top: 100px; padding: 10px; background-color: #bedbf8; text-align: center">
    <img src="../images/app_logo.png" alt="logo My Report" style="width: 250px; margin-top: 30px"/><br/><br/>
    <h2>Oops ... Une erreur est survenue !</h2><br/>
</div>

<?php

use Classes\DB\Database;

if (isset(Database::$error_array) && Database::$error_array) {
    ?>
    <div style="text-align: center">
        <h3 style="color: red">Vous voyez ceci car vous avez le DEV_MODE d'activ&eacute;</h3>
        <section id="accordion" class="accordion">
            <div class="accordion-container">
                <?php if (count(Database::$error_array) == 1) { ?>
                    <details class="accordion-item" open>
                        <summary class="accordion-trigger">
                            <span class="accordion-title">Erreur SQL</span>
                        </summary>
                        <div class="accordion-content">
                            <p><?= Database::$error_array[0][0]; ?></p>
                        </div>
                    </details>

                    <details class="accordion-item">
                        <summary class="accordion-trigger">
                            <span class="accordion-title">Stack trace</span>
                        </summary>
                        <div class="accordion-content">
                            <p>
                                <?php
                                foreach (Database::$error_array[0][1] as $trace) {
                                    echo $trace['class'] . $trace['type'] . $trace['function'] . ' <strong>[' . $trace['file'] . ', line ' . $trace['line'] . ']</strong><br />';
                                }
                                ?>
                            </p>
                        </div>
                    </details>

                    <details class="accordion-item">
                        <summary class="accordion-trigger">
                            <span class="accordion-title">Requ&ecirc;te SQL compl&egrave;te</span>
                        </summary>
                        <div class="accordion-content">
                            <pre><?= Database::$error_array[0][2]; ?></pre>
                        </div>
                    </details>
                <?php } else { ?>
                    <details class="accordion-item">
                        <summary class="accordion-trigger">
                            <span class="accordion-title">Stack trace</span>
                        </summary>
                        <div class="accordion-content">
                            <p>
                                <?php
                                foreach (Database::$error_array as $trace) {
                                    echo $trace['class'] . $trace['type'] . $trace['function'] . ' <strong>[' . $trace['file'] . ', line ' . $trace['line'] . ']</strong><br />';
                                }
                                ?>
                            </p>
                        </div>
                    </details>
                <?php } ?>
            </div>
        </section>
    </div>
<?php } ?>
</body>
</html>
