<?php

namespace Classes\Debug\Debugbar;

use Services\Debug\DebugService;

class Debug
{
    public static function isDebugModeEnable()
    {
        return getenv('DEV_MODE');
    }

    public static function init()
    {
        if (!Debug::isDebugModeEnable()) {
            return;
        }

        DebugService::initSessionDebug();
        DebugService::setErrorHandler();
    }

    public static function logResponse()
    {
        if (!Debug::isDebugModeEnable()) {
            return;
        }

        // Variables serveur et headers
        DebugService::setServerLog();

        // Variables de $_GET et $_POST
        DebugService::setGetPostLog();

        require_once __DIR__ . "/../../../public/Debugbar/toolbar.php";
    }

    public static function startLogQuery()
    {
        if (!Debug::isDebugModeEnable()) {
            return;
        }

        $_SESSION["DEBUG"]['QUERY']["START_TIME"] = microtime(true);
    }

    public static function endLogQuery($query)
    {
        if (!Debug::isDebugModeEnable() || !isset($query) || !$query) {
            return;
        }

        DebugService::setQueryLog($query);
    }

    public static function startLogSSLQuery()
    {
        if (!Debug::isDebugModeEnable()) {
            return;
        }

        $_SESSION["DEBUG"]['SSL']["START_TIME"] = microtime(true);
    }

    public static function endLogSSLQuery()
    {
        if (!Debug::isDebugModeEnable()) {
            return;
        }

        DebugService::setSSLQueryLog();
    }
}
