<?php

namespace Nfaiz\PdoIfx\Config;

use CodeIgniter\Events\Events;
use Config\Toolbar;

Events::on('pre_system', function () {

    $config = config(Toolbar::class);

    if (isset($config->pdoIfxCollector) && $config->pdoIfxCollector === true) {
        helper('ifx');
        Events::on('PdoIfx', '\Nfaiz\PdoIfx\Collectors\Informix::collect');
    }

});