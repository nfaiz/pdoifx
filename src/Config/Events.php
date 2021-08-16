<?php

namespace Nfaiz\PdoIfx\Config;

use CodeIgniter\Events\Events;

Events::on('pre_system', function () {

    $config = config(PdoIfx::class);

    if ($config->collect === true)
    {
        Events::on('PdoIfx', '\Nfaiz\PdoIfx\Collectors\Database::collect');
    }

});