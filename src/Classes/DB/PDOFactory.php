<?php

namespace Classes\DB;

use Exception;
use PDO;

require_once __DIR__ . '/../../Init/bootstrap.php';
require_once __DIR__ . '/PDOOptions.php';

/**
 * Instancie une connexion PDO ou retourne une connexion existante
 *
 * @name: PDOFactory
 */
class PDOFactory
{
    /**
     * @var array Tableau des instances de connexions
     */
    private static array $instances = [];

    /**
     * Retourne une connexion PDO ou false si la connexion échoue
     *
     * @return mixed
     */
    public static function create()
    {
        return self::get(self::getOptions());
    }

    /**
     * Instancie une connexion ou retourne une connexion existante selon des paramètres de connexion (ou false si la connexion échoue)
     *
     * @param PDOOptions $options
     * @return mixed
     */
    public static function get(PDOOptions $options)
    {
        $hash = $options->hash();

        if (!isset(self::$instances[$hash]) || !self::$instances[$hash]) {
            $dsn = $options->getDriver() . ':host=' . $options->getHost() . ';';

            if (!empty($options->getPort())) {
                $dsn .= 'port=' . $options->getPort() . ';';
            }

            if (!empty($options->getDbname())) {
                $dsn .= 'dbname=' . $options->getDbname() . ';';
            }

            try {
                self::$instances[$hash] = new PDO($dsn, $options->getUser(), $options->getPasswd(), $options->getOptions());
                self::$instances[$hash]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                self::$instances[$hash]->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                self::$instances[$hash] = false;
            }
        }

        return self::$instances[$hash];
    }

    /**
     * Définie les paramètres de connexions pour un environnement donné
     *
     * @return PDOOptions
     */
    private static function getOptions(): PDOOptions
    {
        $o = new PDOOptions();
        $o->setDbname(getenv('APP_DB_NAME'));
        $o->setDriver('mysql');
        $o->setHost(getenv('APP_DB_HOSTNAME'));
        $o->setPort(getenv('APP_DB_PORT'));
        $o->setOptions([]);
        $o->setUser(getenv('APP_DB_USERNAME'));
        $o->setPasswd(getenv('APP_DB_PASSWORD'));

        return $o;
    }
}
