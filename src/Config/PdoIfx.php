<?php

namespace Nfaiz\PdoIfx\Config;

use CodeIgniter\Config\BaseConfig;

class PdoIfx extends BaseConfig
{
    /**
     * -------------------------------------------------------------
     * Query Log
     * -------------------------------------------------------------
     *
     * To enable or disable query logging.
     *
     * @var boolean
     */
    public $enableQueryLog = true;

    /**
     * -------------------------------------------------------------
     * Query Collector
     * -------------------------------------------------------------
     *
     * To enable or disable query collector.
     * $enableQueryLog must be set to true in order to use this.
     *
     * @var boolean
     */
    public $collect = true;

    /**
     * -------------------------------------------------------------
     * Tab Title
     * -------------------------------------------------------------
     *
     * Tab title display
     *
     * @var string
     */
    public $tabTitle = 'Queries';

    /**
     * -------------------------------------------------------------
     * Query Return Type
     * -------------------------------------------------------------
     *
     * object or array
     *
     * @var string
     */
    public $returnType = 'object';

    /**
     * -------------------------------------------------------------
     * Default Group
     * -------------------------------------------------------------
     *
     * Default group for connection.
     *
     * @var string
     */
    public $defaultGroup = 'default';

    public $default = [
        'host'     => '',
        'driver'   => '',
        'server'   => '',
        'database' => '',
        'username' => '',
        'password' => '',
        'charset'   => 'utf8',
        'collation' => 'utf8_general_ci',
        'prefix'     => '',
        'service'  => '',
        'protocol' => '',
        'EnableScrollableCursors' => '',
        'db_locale' => '',
        'client_locale' => '',
    ];
}