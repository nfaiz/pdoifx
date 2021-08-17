<?php

namespace Nfaiz\PdoIfx\Config;

use CodeIgniter\Events\Events;

Events::on('pre_system', function () {

    $config = config('Toolbar');

    helper('ifx');

    if (isset($config->ifxCollector) && $config->ifxCollector === true)
    {
        Events::on('PdoIfx', '\Nfaiz\PdoIfx\Collectors\Database::collect');
    }

});