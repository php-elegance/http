<?php

namespace Command;

use Elegance\MxCmd;

abstract class MxServer
{
    static function __default()
    {
        self::port(env('PORT'));
    }

    static function port(int $port)
    {
        MxCmd::run('install.index');

        MxCmd::echo('-------------------------------------------------');
        MxCmd::echo('| Iniciando servidor PHP');
        MxCmd::echo('| Acesse: [#]', "http://127.0.0.1:$port/");
        MxCmd::echo('| Use: [#] para finalizar o servidor', "CLTR + C");
        MxCmd::echo("| Escutando porta [#]", $port);
        MxCmd::echo('-------------------------------------------------');
        MxCmd::echo('');

        echo shell_exec("php -S 127.0.0.1:$port index.php");
    }
}
