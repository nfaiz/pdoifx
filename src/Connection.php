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

            $pdo->exec("SET NAMES '" . $config['charset'] . "' COLLATE '" . $config['collation'] . "'");

            $pdo->exec("SET CHARACTER SET '" . $config['charset'] . "'");

            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, ($config['fetchmode'] != 'array') ? \PDO::FETCH_OBJ : \PDO::FETCH_ASSOC);

            $pdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_NATURAL);
        }
        catch (PDOException $e)
        {
            die('Cannot the connect to Database with PDO. ' . $e->getMessage());
        }

        $this->prefix = $config['prefix'];

        return $pdo;
    }

    /**
     * Create PDO config
     *
     * @return array
     */
    private function getConfig(): array
    {
        $pdoIfxConfig = config('PdoIfx');

        $dbGroup = ($this->instance == '') ? $pdoIfxConfig->defaultGroup : $this->instance;

        $config = $pdoIfxConfig->{$dbGroup} ?? false;

        if (! $config)
        {
            throw new \Exception("DBGroup Not found. Check PdoIfx Config.", 1);
        }

        return [
            'DSN' => $this->getDsn($config),
            'username' => $config['username'],
            'password' => $config['password'],
            'fetchmode' => $pdoIfxConfig->returnType,
            'charset'   => $config['charset'] ?? '',
            'collation' => $config['collation'] ?? '',
            'prefix' => $this->getPrefix($config)
        ];
    }

    /**
     * GetDsn
     *
     * @return string
     */
    public function getDsn(array $config): string
    {
        $extra = isset($config['db_locale']) &&  $config['db_locale'] != ''
            ? "DB_LOCALE={$config['db_locale']};"
            : '';

        $extra = isset($config['client_locale']) &&  $config['client_locale'] != ''
            ? "CLIENT_LOCALE={$config['client_locale']};"
            : '';

        return "informix:host={$config['host']};"
                    . "service={$config['service']};"
                    . "server={$config['server']};"
                    . "database={$config['database']};"
                    . "protocol={$config['protocol']};"
                    . "EnableScrollableCursors={$config['EnableScrollableCursors']};" . $extra;
    }

    /**
     * GetPrefix
     *
     * @return string
     */
    public function getPrefix(array $config): string
    {
        return isset($config['prefix']) && $config['prefix'] != ''
            ? substr($config['prefix'],-1) == ':' ? $config['prefix'] : $config['prefix'] . ':'
            : '';
    }
}