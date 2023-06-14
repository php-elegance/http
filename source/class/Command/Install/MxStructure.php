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

        Dir::create("teste/library");
        Dir::create("teste/library/assets");
        Dir::create("teste/source/class");
        Dir::create("teste/source/helper");
        Dir::create("teste/source/helper/constant");
        Dir::create("teste/source/helper/function");
        Dir::create("teste/source/helper/script");
        Dir::create("teste/view");

        MxCmd::echo("Estrutura criada");

        File::copy("$basePath/source/class/Middleware/Response/MdApi.php", "teste/source/class/Middleware/Response/MdApi.php");

        MxCmd::echo("Middleware API Instalada");

        MxCmd::run("install.index");
    }
}
