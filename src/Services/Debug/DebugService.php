<?php

namespace Services\Debug;

class DebugService
{

    public static function initSessionDebug()
    {
        $_SESSION["DEBUG"] = array();
        $_SESSION["DEBUG"]['ENABLE'] = true;
        $requestScheme = isset($_SERVER["REQUEST_SCHEME"]) && $_SERVER["REQUEST_SCHEME"]
            ? $_SERVER["REQUEST_SCHEME"]
            : "http";
        $referer = $requestScheme . "://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
        $_SESSION["DEBUG"]["FROM_URL"] = $referer;
        $_SESSION["DEBUG"]["START_TIME"] = microtime(true);

        // SERVER
        $_SESSION["DEBUG"]["SERVER"] = $_SERVER;

        uksort($_SESSION["DEBUG"]["SERVER"], function ($a, $b) {
            return strnatcmp($a, $b); // Comparaison de clés de manière naturelle (ordre alphabétique)
        });

        // COOKIE
        $_SESSION["DEBUG"]["COOKIE"] = $_COOKIE;

        // QUERY
        $_SESSION["DEBUG"]['QUERY']["START_TIME"] = 0;
        $_SESSION["DEBUG"]['QUERY']["TOTAL_TIME"] = 0;

        // LOG
        $_SESSION["DEBUG"]["LOG"]["NB_ERROR"] = 0;
        $_SESSION['DEBUG']["LOG"]["E_ERROR"]["NB_ERROR"] = 0;
        $_SESSION['DEBUG']["LOG"]["E_WARNING"]["NB_ERROR"] = 0;
        $_SESSION['DEBUG']["LOG"]["E_PARSE"]["NB_ERROR"] = 0;
        $_SESSION['DEBUG']["LOG"]["E_NOTICE"]["NB_ERROR"] = 0;
        $_SESSION['DEBUG']["LOG"]["E_OTHER"]["NB_ERROR"] = 0;

        // SSL
        $_SESSION["DEBUG"]["SSL"]["NB_QUERY"] = 0;
        $_SESSION["DEBUG"]["SSL"]["START_TIME"] = 0;
        $_SESSION["DEBUG"]["SSL"]["TOTAL_TIME"] = 0;
    }

    public static function setErrorHandler()
    {
        error_reporting(E_ALL);

        // Fonction personnalisée pour gérer les erreurs
        function customErrorHandler($errno, $errstr, $errfile, $errline): bool
        {
            if (!(error_reporting() & $errno)) {
                // This error code is not included in error_reporting, so let it fall
                // through to the standard PHP error handler
                return false;
            }

            switch ($errno) {
                case E_ERROR:
                    $cat = "E_ERROR";
                    break;
                case E_WARNING:
                    $cat = "E_WARNING";
                    break;
                case E_PARSE:
                    $cat = "E_PARSE";
                    break;
                case E_NOTICE:
                    $cat = "E_NOTICE";
                    break;
                case E_DEPRECATED:
                    $cat = "E_USER_DEPRECATED";
                    break;
                default:
                    $cat = "E_OTHER";
                    break;
            }

            $error_message = \Services\Debug\DebugService::formatLogMessage($errstr, $errfile, $errline);
            $_SESSION['DEBUG']["LOG"][$cat][] = $error_message;
            $_SESSION['DEBUG']["LOG"][$cat]["NB_ERROR"]++;
            $_SESSION['DEBUG']["LOG"]["NB_ERROR"]++;

            return true;
        }

        set_error_handler('Services\Debug\customErrorHandler');
    }

    public static function formatLogMessage(string $err, string $file, string $line): string
    {
        return "<strong>$err</strong> dans le fichier $file &agrave; la ligne $line";
    }

    public static function setServerLog()
    {
        $requestStartTime = $_SESSION["DEBUG"]["SERVER"]["REQUEST_TIME"];
        $requestEndTime = microtime(true);

        $pageDuration = $requestEndTime - $requestStartTime;
        $timeFormatted = self::formatTime($pageDuration);

        $_SESSION["DEBUG"]["SERVER"]["REQUEST_TIME"] = $timeFormatted;
        $_SESSION["DEBUG"]["SERVER"]["HTTP_STATUS"] = http_response_code();
        $_SESSION["DEBUG"]["RESPONSE"]["HEADER"] = headers_list();
    }

    public static function formatTime($time)
    {
        // Obtenir la partie entière du temps (en secondes)
        $seconds = floor($time);
        // Obtenir la partie fractionnaire du temps (en microsecondes)
        $microseconds = ($time - $seconds) * 1000000;
        // Convertir les secondes en minutes et secondes
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;
        // Formater les millisecondes
        $milliseconds = number_format($microseconds / 1000);
        // Afficher le temps sous forme de "XX mn XX s XX ms"
        if ($minutes > 0) {
            $timeFormatted = sprintf('%02d mn %02d s %s ms', $minutes, $seconds, $milliseconds);
        } elseif ($seconds > 0) {
            $timeFormatted = sprintf('%02d s %s ms', $seconds, $milliseconds);
        } else {
            $timeFormatted = sprintf('%s ms', $milliseconds);
        }

        return $timeFormatted;
    }

    public static function setGetPostLog()
    {
        $vars = array('$_POST' => $_POST, '$_GET' => $_GET);

        foreach ($vars as $name => $array) {
            foreach ($array as $key => $value) {
                $type = gettype($value);

                if (is_array($value)) {
                    $value = nl2br(print_r($value, true));
                }

                $_SESSION["DEBUG"]["RESPONSE"][] = $name . '["' . $key . '"]:' . $type . ':' . $value;
            }
        }
    }

    public static function setQueryLog($query)
    {
        // Si la barre de debug n'est pas initialisée, on ne fait rien
        if (!isset($_SESSION["DEBUG"]['ENABLE']) || !$_SESSION["DEBUG"]['ENABLE']) {
            return;
        }

        // Calculer le temps d'exécution de la requête
        $timeRequest = microtime(true) - $_SESSION["DEBUG"]['QUERY']["START_TIME"];
        $timeFormatted = self::formatTime($timeRequest);

        // Enregistre le temps total du traitement des requêtes
        $_SESSION["DEBUG"]['QUERY']["TOTAL_TIME"] += $timeRequest;

        $debugBacktrace = debug_backtrace();

        $trace = '';
        unset($debugBacktrace[0]); // SetQueryLog
        unset($debugBacktrace[1]); // EndLogQuery
        unset($debugBacktrace[2]); // InitQuery

        $dbt = $debugBacktrace;

        $debugTrace = $debugBacktrace[3];

        $class = (isset($debugTrace['class']) && $debugTrace['class'] ? $debugTrace['class'] : "") .
            (isset($debugTrace['type']) && $debugTrace['type'] ? $debugTrace['type'] : "") .
            (isset($debugTrace['function']) && $debugTrace['function'] ? $debugTrace['function'] : "");
        $fileNoCrochet = (isset($debugTrace['file']) && $debugTrace['file'] ? $debugTrace['file'] : "") .
            ', line ' . (isset($debugTrace['line']) && $debugTrace['line'] ? $debugTrace['line'] : "");

        // Nombre de requete par class
        self::checkIncrementationSyntheseQuery($class, "NB_REQUEST", 1);
        // Temps total par class
        self::checkIncrementationSyntheseQuery($class, "TOTAL_TIME", $timeRequest);
        // position du fichier
        self::checkIncrementationSyntheseQuery($class, "FILE", 1, $fileNoCrochet);

        foreach ($dbt as $t) {
            $t['class'] = isset($t['class']) && $t['class'] ? $t['class'] : "";
            $t['type'] = isset($t['type']) && $t['type'] ? $t['type'] : "";
            $t['function'] = isset($t['function']) && $t['function'] ? $t['function'] : "";
            $t['file'] = isset($t['file']) && $t['file'] ? $t['file'] : "";
            $t['line'] = isset($t['line']) && $t['line'] ? $t['line'] : "";

            $class = $t['class'] . $t['type'] . $t['function'];
            $file = self::formatTrace($t['file'], $t['line']);
            $trace .= $class . $file;
        }

        $_SESSION["DEBUG"]["QUERY"][] = array(
            "TIME" => $timeFormatted,
            "TRACE" => $trace,
            "REQUEST" => $query
        );
    }

    public static function checkIncrementationSyntheseQuery($class, $field, $specialIncrementation, $multiple = false)
    {
        if ($multiple) {
            isset($_SESSION["DEBUG"]["SYNTHESE_QUERY"][$class][$field][$multiple])
            && $_SESSION["DEBUG"]["SYNTHESE_QUERY"][$class][$field][$multiple]
                ? $_SESSION["DEBUG"]["SYNTHESE_QUERY"][$class][$field][$multiple] =
                $_SESSION["DEBUG"]["SYNTHESE_QUERY"][$class][$field][$multiple] + $specialIncrementation
                : $_SESSION["DEBUG"]["SYNTHESE_QUERY"][$class][$field][$multiple] = $specialIncrementation;

            return;
        }

        isset($_SESSION["DEBUG"]["SYNTHESE_QUERY"][$class][$field])
        && $_SESSION["DEBUG"]["SYNTHESE_QUERY"][$class][$field]
            ? $_SESSION["DEBUG"]["SYNTHESE_QUERY"][$class][$field] =
            $_SESSION["DEBUG"]["SYNTHESE_QUERY"][$class][$field] + $specialIncrementation
            : $_SESSION["DEBUG"]["SYNTHESE_QUERY"][$class][$field] = $specialIncrementation;
    }

    public static function formatTrace($file, $line): string
    {
        return " <strong>[" . $file . ", line " . $line . "]</strong><br />";
    }

    public static function setSSLQueryLog()
    {
        // Si la barre de debug n'est pas initialisée, on ne fait rien
        if (!isset($_SESSION["DEBUG"]) || !$_SESSION["DEBUG"]) {
            return;
        }

        // Calculer le temps d'exécution de la requête
        $timeRequest = microtime(true) - $_SESSION["DEBUG"]['SSL']["START_TIME"];

        // Enregistre le temps total du traitement des requêtes
        $_SESSION["DEBUG"]["SSL"]["NB_QUERY"]++;
        $_SESSION["DEBUG"]["SSL"]["TOTAL_TIME"] += $timeRequest;
    }

    public static function getHeaderTable($fields)
    {
        $header = "<thead><tr>";
        foreach ($fields as $key => $field) {
            $header .= "<th scope='col'>
                            <a href='#' style='text-wrap: nowrap;' class='sort-link' data-sort='" . $key . "'>"
                . $field .
                " <i class='fa fa-sort' aria-hidden='true'></i>
                            </a>
                        </th>";
        }
        $header .= "</tr></thead>";

        return $header;
    }

    public static function getTableImbrique($table, $key, $value)
    {
        if (is_array($value) || is_object($value)) {
            foreach ($value as $toto => $v) {
                echo "<tr>";
                echo "<td style='width:25%'>" . $toto . "</td>";
                echo "<td style='width:10%'>" . gettype($v) . "</td>";
                echo "<td style='width:65%'>";
                if ((is_array($v) || is_object($v)) && !empty($v)) {
                    echo "<table class='table table-striped'>";
                    self::getTableImbrique($table, $key, $v);
                    echo "</table>";
                } elseif (is_bool($v)) {
                    echo $v ? 'true' : 'false';
                } else {
                    print_r($v);
                }
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr>";
            echo "<td style='width:25%'>" . $key . "</td>";
            echo "<td style='width:10%'>" . gettype($value) . "</td>";
            echo "<td style='width:65%'>";
            if (is_bool($value)) {
                echo $value ? 'true' : 'false';
            } else {
                print_r($value);
            }
            echo "</td>";
            echo "</tr>";
        }
    }

    public static function getLogErrorType()
    {
        return array(
            "E_ERROR" => array(
                "text" => "Fatal",
                "nb" => $_SESSION["DEBUG"]["LOG"]["E_ERROR"]["NB_ERROR"]
            ),
            "E_WARNING" => array(
                "text" => "Warning",
                "nb" => $_SESSION["DEBUG"]["LOG"]["E_WARNING"]["NB_ERROR"]
            ),
            "E_PARSE" => array(
                "text" => "Parse",
                "nb" => $_SESSION["DEBUG"]["LOG"]["E_PARSE"]["NB_ERROR"]
            ),
            "E_NOTICE" => array(
                "text" => "Notice",
                "nb" => $_SESSION["DEBUG"]["LOG"]["E_NOTICE"]["NB_ERROR"]
            ),
            "E_OTHER" => array(
                "text" => "Other",
                "nb" => $_SESSION["DEBUG"]["LOG"]["E_OTHER"]["NB_ERROR"]
            )
        );
    }

    public static function getStyleStatus($status)
    {
        switch ($status) {
            case '200':
                return 'color:lightgreen;';
            case '404':
                return 'color:lightgrey;';
            case '500':
                return 'color:lightcoral;';
            default:
                return 'color:goldenrod;';
        }
    }

    public static function prettifySql($sqlQuery)
    {
        $sqlQuery = str_replace(array("\r\n", "\r", "\n"), " ", $sqlQuery); // Supprime les sauts de ligne

        $keywords = array('SELECT', 'FROM', 'LEFT JOIN', 'JOIN', 'WHERE', 'AND', 'OR', 'ORDER BY', 'GROUP BY', 'INSERT', 'DELETE', 'CREATE', 'HAVING', 'UPDATE', 'VALUES');

        // Ajoute des mots-clés en minuscules à la liste des mots-clés
        $keywords = array_merge($keywords, array_map('strtolower', $keywords));
        // Ajoute des sauts de ligne après les mots clés
        $sqlQuery = preg_replace("/\b(" . implode("|", $keywords) . ")\b/i", "<br><b class='text-uppercase'>$1</b>", $sqlQuery);
        // Remplace les espaces multiples par un seul espace
        $sqlQuery = preg_replace("/\s{2,}/", " ", $sqlQuery);
        // Supprime les espaces en début et fin de requête
        $sqlQuery = trim($sqlQuery);
        // Et les sauts de lignes (utile pour les UPDATE, DELETE, SELECT, ...)
        return substr($sqlQuery, 4);
    }
}
