<?php

namespace Classes\DB;

use Classes\Debug\Debugbar\Debug;
use Exception;
use PDOException;
use PDOStatement;


class Database
{
    /**
     * @var PDOStatement The statement to execute
     */
    private static PDOStatement $statement;

    /**
     * @var bool If debug mode is set enable or not
     */
    protected static bool $debug_mode = false;

    /**
     * @var array Contains the debug_backtrace of the query failing
     */
    public static array $error_array;


    /**
     * @var int SQL transaction counter
     */
    private static int $sqlTransactionCounter = 0;

    /**
     * R�cup�re une instance de connexion PDO
     *
     * @return mixed
     */
    private static function initPdo()
    {
        self::$debug_mode = (bool)getenv('DEV_MODE');
        return PDOFactory::create();
    }

    /**
     * Affiche un d�bug si on est en DEV_MODE
     *
     * @return void
     */
    public static function debug()
    {
        require_once __DIR__ . '/../debug/error.inc.php';
        die();
    }

    /**
     * Ex�cute une requ�te
     *
     * @param $query
     * @param array $params
     * @return PDOStatement|false
     */
    public static function query($query, array $params = [])
    {
        if ((!isset($query) || !$query) || trim($query) === "") {
            return false;
        }

        $pdo = self::initPdo();

        Debug::startLogQuery();

        self::$statement = $pdo->prepare($query);
        $stmt = self::$statement->execute($params);

        Debug::endLogQuery($query);

        if (!$stmt) {
            if (self::$debug_mode) {
                self::$error_array = [
                    [self::$statement->errorInfo()[2], debug_backtrace(), $query]
                ];
                self::debug();
            }

            return false;
        }

        return self::$statement;
    }

    public static function exec(string $query)
    {
        if ((!isset($query) || !$query) || trim($query) === "") {
            return false;
        }

        $pdo = self::initPdo();

        $pdo->exec($query);
    }

    /**
     * R�cup�re la ligne suivante d'un jeu de r�sultats PDO
     *
     * @return array|mixed
     */
    public static function fetchArray()
    {
        return self::$statement ? self::$statement->fetch() : [];
    }

    /**
     * R�cup�re toutes les lignes d'un jeu de r�sultats PDO
     *
     * @return array
     */
    public static function fetchAll(): array
    {
        return self::$statement ? self::$statement->fetchAll() : [];
    }

    /**
     * Retourne le nombre de lignes affect�es par le dernier appel � la fonction PDOStatement::execute()
     *
     * @param $stmt
     * @return int
     */
    public static function countRow($stmt = null): int
    {
        $nb = 0;

        if ($stmt instanceof PDOStatement) {
            $nb = $stmt->rowCount();
        }

        return $nb;
    }

    /**
     * Retourne le dernier ID ins�r� en base ou null si exception
     */
    public static function lastPK()
    {
        try {
            return self::initPdo()->lastInsertId();
        } catch (PDOException $e) {
            if (self::$debug_mode) {
                self::$error_array = debug_backtrace();
                self::debug();
            }

            return null;
        }
    }

    /**
     * Echappe une cha�ne de caract�res
     *
     * @param $string
     * @return mixed
     */
    public static function escapeSql($string)
    {
        return self::initPdo()->quote($string);
    }

    /**
     * D�marre une nouvelle transaction.
     *
     * Note : Si on est d�j� dans une transaction, on incr�mente seulement le compteur de transactions.
     *
     * @return bool TRUE en cas de succ�s, FALSE sinon
     * @throws TransactionException
     */
    public static function beginTransaction()
    {
        try {
            if (!self::$sqlTransactionCounter++) {
                return self::query('START TRANSACTION');
            }

            return self::inTransaction();
        } catch (Exception $e) {
            throw new TransactionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * V�rifie si on est dans une transaction.
     *
     * @return bool TRUE si une transaction est actuellement active, FALSE sinon
     */
    public static function inTransaction(): bool
    {
        return self::$sqlTransactionCounter >= 0;
    }

    /**
     * Valide une transaction.
     *
     * Note : Si on est dans une transaction imbriqu�e, on d�cr�mente seulement le compteur de transactions.
     *
     * @return bool TRUE en cas de succ�s, FALSE si une erreur survient.
     * @throws TransactionException
     */
    public static function commit(): bool
    {
        try {
            if (!--self::$sqlTransactionCounter) {
                return (bool)self::query('COMMIT');
            }

            return self::inTransaction();
        } catch (Exception $e) {
            throw new TransactionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Annule une transaction.
     *
     * @return bool TRUE en cas de succ�s, FALSE si une erreur survient.
     * @throws TransactionException
     */
    public static function rollback(): bool
    {
        try {
            self::$sqlTransactionCounter = 0;

            if (self::inTransaction()) {
                return (bool)self::query('ROLLBACK');
            }

            return false;
        } catch (Exception $e) {
            throw new TransactionException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
