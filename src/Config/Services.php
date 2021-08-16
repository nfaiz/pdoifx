<?php

namespace Nfaiz\PdoIfx\Config;

use Config\Services as BaseService;

class Services extends BaseService
{
    public static function ifx($dbGroup = 'default', $getShared = false)
    {
        if ($getShared)
        {
            return static::getSharedInstance('ifx', $dbGroup);
        }

        return new \Nfaiz\PdoIfx\Query($dbGroup);
    }
}