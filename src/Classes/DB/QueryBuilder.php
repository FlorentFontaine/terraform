<?php

namespace Classes\DB;

require_once __DIR__ . '/Database.php';

use Classes\Debug\Debugbar\Debug;
use PDO;
use PDOException;
use PDOStatement;

/**
 * Class QueryBuilder
 */
class QueryBuilder extends Database
{
    public array $fields = ["*"];

    private string $from;

    private string $index = '';

    private array $where = [];

    private array $having = [];

    private array $params = [];

    private array $orderBy = [];

    private array $groupBy = [];

    private array $unions = [];

    private int $limit = 0;

    private int $offset = -1;

    private PDO $pdo;

    private int $fetchMode = PDO::FETCH_ASSOC;

    /**
     * Récupère la connexion à la base de données
     */
    public function __construct()
    {
        $this->pdo = PDOFactory::create();
    }

    /**
     * Défini les champs de la clause SELECT de la requête
     * Exemple : ->select("id")
     * Exemple : ->select("id", "name")
     * Exemple : ->select(["id", "name"])
     *
     * @param ...$fields
     * @return $this
     */
    public function select(...$fields): self
    {
        if (is_array($fields[0])) {
            $fields = $fields[0];
        }

        if ($this->fields === ["*"]) {
            $this->fields = $fields;
        } else {
            $this->fields = array_merge($this->fields, $fields);
        }

        return $this;
    }

    /**
     * Défini la table ciblée par la requête
     *
     * @param string $table
     * @param string|null $alias
     * @return $this
     */
    public function from(string $table, ?string $alias = null): self
    {
        $this->from = $alias === null ? $table : "$table $alias";

        return $this;
    }

    public function join(string $table, string $on): self
    {
        $this->setJoin($table, $on);

        return $this;
    }

    public function leftJoin(string $table, string $on): self
    {
        $this->setJoin($table, $on, 'LEFT');

        return $this;
    }

    public function rightJoin(string $table, string $on): self
    {
        $this->setJoin($table, $on, 'RIGHT');

        return $this;
    }

    public function innerJoin(string $table, string $on): self
    {
        $this->setJoin($table, $on, 'INNER');

        return $this;
    }

    /**
     * Ajoute une clause WHERE à la requête (clause AND implicite)
     *
     * @param string $where
     * @return $this
     */
    public function where(string $where): self
    {
        $this->where[] = [
            'type' => 'AND',
            'clause' => $where
        ];

        return $this;
    }


    /**
     * Ajoute une clause WHERE à la requête (OR)
     *
     * @param string $where
     * @return $this
     */
    public function orWhere(string $where): self
    {
        $this->where[] = [
            'type' => 'OR',
            'clause' => $where
        ];

        return $this;
    }

    /**
     * Ajout une clause HAVING
     * @param string $having
     * @return $this
     */
    public function having(string $having): self
    {
        $this->having[] = $having;

        return $this;
    }

    /**
     * Ajoute une requête d'union
     * @param QueryBuilder|string $query
     * @param bool $all Indique si `UNION ALL` doit être utilisé
     * @return $this
     */
    public function union($query, bool $all = false): self
    {
        $this->unions[] = [
            'query' => $query,
            'all' => $all
        ];

        return $this;
    }


    /**
     * Défini un paramètre de la clause WHERE
     *
     * @param string $key
     * @param $value
     * @return $this
     */
    public function setParam(string $key, $value): self
    {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * Défini la clause ORDER BY de la requête
     *
     * @param string $field
     * @param string $direction
     * @param bool $forceFirstPosition
     * @return $this
     */
    public function orderBy(string $field = '', string $direction = 'ASC', bool $forceFirstPosition = false): self
    {
        $direction = strtoupper($direction);

        if (!in_array($direction, ['ASC', 'DESC'])) {
            if ($forceFirstPosition) {
                array_unshift($this->orderBy, $field);
                return $this;
            }

            $this->orderBy[] = $field;
        } else {
            if ($forceFirstPosition) {
                array_unshift($this->orderBy, "$field $direction");
                return $this;
            }

            $this->orderBy[] = "$field $direction";
        }

        return $this;
    }

    /**
     * Définit la clause GROUP BY de la requête
     *
     * @param string $field
     * @return $this
     */
    public function groupBy(string $field): self
    {
        // si on fait un "GROUP BY", il faut s'assurer qu'il soit dans le select
        if (!in_array($field, $this->fields)) {
            $this->select($field);
        }

        $this->groupBy[] = $field;

        return $this;
    }

    /**
     * Défini la clause LIMIT de la requête
     *
     * @param int $limit
     * @return $this
     */
    public function limit(int $limit = 1): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Défini la clause OFFSET de la requête
     *
     * @param int $offset
     * @return $this
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Défini la page souhaitée de la requête
     * Attention : Ne peut être utilisé qu'avec la clause LIMIT
     * Exemple : -">limit(10)->page(3)" retourne " LIMIT 10 OFFSET 20"
     *
     * @param int $page
     * @return $this
     */
    public function page(int $page): self
    {
        if ($this->limit <= 0) {
            return $this;
        }

        return $this->offset($this->limit * ($page - 1));
    }

    /**
     * Définit un index sur lequel les indices du tableau de retour seront basé
     *
     * @param string $index
     * @return $this
     */
    public function index(string $index): self
    {
        $this->index = $index;

        return $this;
    }

    // Returns the number of rows that were modified
    public function update(string $table, array $data, array $where = []): int
    {
        $dataStr = implode(
            ", ",
            array_map(
                fn ($key, $_) => "`$key` = :$key",
                array_keys($data),
                array_values($data)
            )
        );

        $whereStr = implode(
            " AND ",
            array_map(
                fn ($key, $_) => "`$key` = :where$key",
                array_keys($where),
                array_values($where)
            )
        );

        $stmt = $this->pdo->prepare("UPDATE `$table` SET $dataStr WHERE $whereStr");

        foreach ($data as $key => &$_) {
            $stmt->bindParam(":$key", $data[$key]);
        }

        foreach ($where as $key => &$_) {
            $stmt->bindParam(":where$key", $where[$key]);
        }

        $stmt->execute();

        unset($_);

        return $stmt->rowCount();
    }


    // Returns the number of rows that were affected in the last SQL statement executed by the corresponding PDOStatement
    public function delete(string $table, array $where): int
    {
        $whereStr = implode(
            " AND ",
            array_map(
                fn ($key, $_) => "`$key` = :$key",
                array_keys($where),
                array_values($where)
            )
        );

        $stmt = $this->pdo->prepare("DELETE FROM `$table` WHERE $whereStr");

        foreach ($where as $key => &$_) {
            $stmt->bindParam(":$key", $where[$key]);
        }

        $stmt->execute();

        return $stmt->rowCount();
    }


    // Inserts a new record into the specified table
    // Returns the ID of the last inserted record
    public function insert(string $table, array $data): string
    {
        $fieldsStr = implode(", ", array_map(fn ($val) => "`$val`", array_keys($data)));
        $placeholdersStr = implode(", ", array_map(fn ($key, $_) => ":$key", array_keys($data), array_values($data)));

        $stmt = $this->pdo->prepare("INSERT INTO `$table` ($fieldsStr) VALUES ($placeholdersStr)");

        foreach ($data as $key => &$_) {
            $stmt->bindParam(":$key", $data[$key]);
        }

        $stmt->execute();

        return $this->pdo->lastInsertId();
    }

    /**
     * Retourne la requête SQL générée
     *
     * @return string
     */
    public function toSQL(): string
    {
        if (!empty($this->index)) {
            $this->generateIndexedQuery();
        }

        $fields = implode(', ', $this->fields);

        $sql = "SELECT " . $fields . " FROM " . $this->from;

        if (!empty($this->where)) {
            $sql .= " WHERE ";

            foreach ($this->where as $index => $where) {
                if ($index > 0) {
                    $sql .= ' ' . $where['type'] . ' ';
                }

                $sql .= $where['clause'];
            }
        }

        if (!empty($this->groupBy)) {
            $sql .= " GROUP BY " . implode(', ', $this->groupBy);
        }

        if (!empty($this->having)) {
            $sql .= " HAVING " . implode(', ', $this->having);
        }

        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBy);
        }

        if ($this->limit > 0) {
            $sql .= " LIMIT " . $this->limit;
        }

        if ($this->offset > -1) {
            $sql .= " OFFSET " . $this->offset;
        }

        foreach ($this->unions as $union) {
            $sql .= $union['all'] ? " UNION ALL " : " UNION ";
            $sql .= $union['query'] instanceof self ? $union['query']->toSQL() : $union['query'];
        }

        return $sql;
    }

    public static function dumpWithBindings(QueryBuilder $query): string
    {
        $sql = $query->toSQL();

        // Get the bound parameters
        $bindings = $query->params;

        // Replace parameter placeholders with actual values in the SQL query
        foreach ($bindings as $key => $value) {
            $sql = str_replace(":" . $key, "'" . $value . "'", $sql);
        }

        return $sql;
    }

    /**
     * Retourne le premier résultat de la requête.
     * Il est possible de spécifier le nom du champ à récupérer directement.
     *
     * @param string|null $field
     * @return string|array|null
     */
    public function get(?string $field = null)
    {
        $query = $this->initQuery();
        $result = $query->fetch($this->fetchMode);

        if ($result === false) {
            return null;
        }

        return $field ? $result[$field] ?? null : $result;
    }

    /**
     * Retourne les résultats de la requête.
     *
     * @return array
     */
    public function getAll(): array
    {
        $query = $this->initQuery();
        return $query->fetchAll($this->fetchMode);
    }

    /**
     * Retourne le nombre de résultats de la requête sans modifier la requête
     *
     * @param string $field
     * @param bool $distinct
     * @return int
     */
    public function count(string $field = "", bool $distinct = false): int
    {
        $countField = $field === "" ? "*" : $field;
        $select = $distinct ? "COUNT(DISTINCT(" . $countField . "))" : "COUNT(" . $countField . ")";
        $query = clone $this;
        return (int)$query->select($select . ' AS count')->get('count');
    }

    /**
     * ****************************************
     *
     *             PRIVATE METHODS
     *
     * ****************************************
     */

    /**
     * Définie une clause JOIN à la requête
     *
     * @param string $table
     * @param string $on
     * @param string $type
     */
    private function setJoin(string $table, string $on, string $type = '')
    {
        $this->from .= " $type JOIN $table ON $on";
    }

    /**
     * Prépare et execute la requête selon les paramètres saisis
     *
     * @return PDOStatement|false
     */
    private function initQuery()
    {
        $rawSql = self::dumpWithBindings($this);

        Debug::startLogQuery();

        $query = $this->pdo->prepare($this->toSQL());
        $stmt = $query->execute($this->params);

        Debug::endLogQuery($rawSql);

        if (!$stmt) {
            if (Database::$debug_mode) {
                self::$error_array = [
                    [$query->errorInfo()[2], debug_backtrace(), $rawSql]
                ];

                Database::debug();
            }

            return false;
        }

        return $query;
    }

    private function generateIndexedQuery()
    {
        $this->fetchMode = PDO::FETCH_GROUP | PDO::FETCH_UNIQUE;

        $indexName = strpos($this->index, '.') !== false ? $this->index : $this->getFrom() . "." . $this->index;

        array_unshift($this->fields, $indexName);

        if (in_array("*", $this->fields)) {
            $allKey = array_search('*', $this->fields);
            $this->fields[$allKey] = $this->getFrom() . ".*" . $this->getFromJoin();
        }
    }

    private function getFrom(): string
    {
        if (strpos($this->from, ' ') !== false) {
            $from = explode(' ', $this->from);
            return $from[0];
        }

        return $this->from;
    }

    private function getFromJoin(): string
    {

        $fromJoin = '';
        $patterns = '/(?:\b(?:from|join)\s+|,\s*)\b(\w+)(?:\s+as\s+(\w+))?\b/i';

        preg_match_all($patterns, $this->from, $matches, PREG_SET_ORDER);
        $tableNames = array_column($matches, 1);


        if (!empty($matches[0][2])) {
            $tableNames[] = $matches[0][2];
        }

        if (!empty($tableNames)) {
            foreach ($tableNames as $table) {
                $fromJoin .= ", $table.* ";
            }
        }

        return $fromJoin;
    }
}
