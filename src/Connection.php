<?php

namespace Nfaiz\PdoIfx;

class Connection {

    protected $instance;

    public $prefix;

    public $connectTime;

    public $connectDuration;

    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    /**
     * Create PDO instance
     *
     * @return object
     */
    public function getPdo()
    {
        $config = $this->getConfig();

        try
        {
            $this->connectTime = microtime(true);

            $pdo = new \PDO($config['DSN'], $config['username'], $config['password']);

            $this->connectDuration = microtime(true) - $this->connectTime;

            $pdo->exec("SET NAMES '" . $config['charset'] . "' COLLATE '" . $config['DBCollat'] . "'");

            $pdo->exec("SET CHARACTER SET '" . $config['charset'] . "'");

            $pdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_NATURAL);
        }
        catch (PDOException $e)
        {
            die('Cannot the connect to Database with PDO. ' . $e->getMessage());
        }

        $this->prefix = $config['DBPrefix'];

        return $pdo;
    }

    /**
     * Create PDO config
     *
     * @return array
     */
    private function getConfig(): array
    {
        $dbConfig = config('Database');

        $dbGroup = ($this->instance == '') ? $dbConfig->defaultGroup : $this->instance;

        $config = $dbConfig->{$dbGroup} ?? false;

        if ($config === false)
        {
            throw new \Exception("Database Connection Group Not found. Check Database Config.", 1);
        }

        return [
            'DSN' => $config['DSN'],
            'username' => $config['username'],
            'password' => $config['password'],
            'charset'   => $config['charset'] ?? '',
            'DBCollat' => $config['DBCollat'] ?? '',
            'DBPrefix' => $this->getPrefix($config)
        ];
    }

    /**
     * GetPrefix
     *
     * @return string
     */
    public function getPrefix(array $config): string
    {
        return isset($config['DBPrefix']) && $config['DBPrefix'] != ''
            ? substr($config['DBPrefix'], -1) == ':' ? $config['DBPrefix'] : $config['DBPrefix'] . ':'
            : '';
    }
}