<?php

namespace Nfaiz\PdoIfx\Config;

Use Nfaiz\PdoIfx\Collectors\Informix;

class Registrar
{
    public static function Toolbar(): array
    {
        return [
            'collectors' => [
                Informix::class,
            ],
        ];
    }
}