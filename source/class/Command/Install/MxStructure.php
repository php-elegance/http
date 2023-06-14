<?php

namespace Command\Install;

use Elegance\Dir;
use Elegance\File;
use Elegance\MxCmd;

abstract class MxStructure
{
    static function __default()
    {

        $basePath = dirname(__DIR__, 4) . '/teste';

        Dir::create("$basePath/library");
        Dir::create("$basePath/library/assets");
        Dir::create("$basePath/source/class");
        Dir::create("$basePath/source/helper");
        Dir::create("$basePath/source/helper/constant");
        Dir::create("$basePath/source/helper/function");
        Dir::create("$basePath/source/helper/script");
        Dir::create("$basePath/view");

        MxCmd::echo("Estrutura criada");

        File::copy("source/class/Middleware/Response/MdApi.php", "$basePath/source/class/Middleware/Response/MdApi.php");

        MxCmd::echo("Middleware API Instalada");

        MxCmd::run("install.index");
    }
}
