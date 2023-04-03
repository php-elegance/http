<?php

namespace Command\Install;

use Elegance\Dir;
use Elegance\File;
use Elegance\MxCmd;

abstract class MxApp
{
    static function __default()
    {
        MxCmd::echo('Configurações iniciais');
        MxCmd::run('install.index');
        MxCmd::run('composer');

        MxCmd::echo('Estrutura de pastas');

        Dir::create('class');
        Dir::create('helper/constant');
        Dir::create('helper/function');
        Dir::create('helper/script');
        Dir::create('library/assets');
        Dir::create('view');

        MxCmd::echo('Copiando arquivos frontend');

        $basePath = dirname(__DIR__, 3);

        Dir::copy("$basePath/view/base", 'view/base');
        File::copy("$basePath/library/assets/energize.js", 'library/assets/energize.js');

        MxCmd::echo('Aplicação instalada');
    }
}
