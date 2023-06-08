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

        Dir::create('library');
        Dir::create('library/assets');
        Dir::create('source/class');
        Dir::create('source/helper');
        Dir::create('source/helper/constant');
        Dir::create('source/helper/function');
        Dir::create('source/helper/script');
        Dir::create('view');
        Dir::create('vue');

        MxCmd::echo('Estrutura criada');

        File::copy("$basePath/library/assets/front.js", 'library/assets/front.js');
        File::copy("$basePath/library/base.html", 'library/base.html');
        File::copy("$basePath/source/class/Middleware/Response/MdApi.php", 'source/class/Middleware/Response/MdApi.php');
        File::copy("$basePath/source/class/Middleware/Response/MdFront.php", 'source/class/Middleware/Response/MdFront.php');

        MxCmd::echo('Arquivos principais instalados');

        MxCmd::run('install.index');
    }
}
