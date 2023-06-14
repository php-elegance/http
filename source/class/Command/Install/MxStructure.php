<?php

namespace Command\Install;

use Elegance\Dir;
use Elegance\File;
use Elegance\MxCmd;

abstract class MxStructure
{
    static function __default()
    {

        $basePath = dirname(__DIR__, 4);

        Dir::create("library");
        Dir::create("library/assets");
        Dir::create("source/class");
        Dir::create("source/helper");
        Dir::create("source/helper/constant");
        Dir::create("source/helper/function");
        Dir::create("source/helper/script");
        Dir::create("view");

        MxCmd::echo("Estrutura criada");

        File::copy("$basePath/source/class/Middleware/Response/MdApi.php", "source/class/Middleware/Response/MdApi.php");

        MxCmd::echo("Middleware API Instalada");

        MxCmd::run("install.index");
    }
}
