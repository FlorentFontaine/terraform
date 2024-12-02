<?php

namespace Classes\DB;

/**
 * Class PDOOptions
 * Permet de fournir les informations de connexion à une base de données.
 */
class PDOOptions
{
    private string $host;
    private string $port;
    private string $user;
    private string $passwd;
    private string $driver;
    private string $dbname;
    private array $options;

    /**
     * Crée un "hash" de l'objet pour le retrouver
     * @return string
     */
    public function hash(): string
    {
        return sha1($this->host . $this->port . $this->user . $this->passwd . $this->driver . $this->dbname);
    }

    /**
     * Retourne la définition d'une instance de PDOOptions
     *
     * @return string
     */
    public function __toString()
    {
        return 'host : ' . $this->getHost() . PHP_EOL
            . 'port : ' . $this->getPort() . PHP_EOL
            . 'user : ' . $this->getUser() . PHP_EOL
            . 'passwd : ' . $this->getPasswd() . PHP_EOL
            . 'dbname : ' . $this->getDbname() . PHP_EOL;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
    }

    /**
     * @param string $port
     */
    public function setPort(string $port)
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser(string $user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getPasswd(): string
    {
        return $this->passwd;
    }

    /**
     * @param string $passwd
     */
    public function setPasswd(string $passwd)
    {
        $this->passwd = $passwd;
    }

    /**
     * @return string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * @param string $driver
     */
    public function setDriver(string $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @return string
     */
    public function getDbname(): string
    {
        return $this->dbname;
    }

    /**
     * @param string $dbname
     */
    public function setDbname(string $dbname)
    {
        $this->dbname = $dbname;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }
}
