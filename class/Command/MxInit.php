<?php

namespace Command;

use Elegance\MxCmd;

abstract class MxInit
{
    static function __default()
    {
        MxCmd::echo('Comando [init] funcionando');
    }
}
