<?php

namespace Command\Install;

use Elegance\Dir;
use Elegance\File;
use Elegance\MxCmd;

abstract class MxApp
{
    static function __default()
    {
        MxCmd::echo("Configurações iniciais");
        MxCmd::run('install.index');
        MxCmd::run('composer');

        MxCmd::echo("Estrutura de pastas");
        MxCmd::echo('------------------------------------------------------------');

        Dir::create('class');
        Dir::create('helper/constant');
        Dir::create('helper/function');
        Dir::create('helper/script');
        Dir::create('library/assets');
        Dir::create('view');

        MxCmd::echo("Criando .gitignore");
        MxCmd::echo('------------------------------------------------------------');

        File::create(
            '.gitignore',
            "/composer.lock\n/vendor\n/.env\n/mx\n\n/class/Model/*/Driver"
        );

        MxCmd::echo("Copiando arquivos frontend");
        MxCmd::echo('------------------------------------------------------------');

        $basePath = dirname(__DIR__, 3);

        Dir::copy("$basePath/view/base", 'view/base');
        File::copy("$basePath/library/assets/favicon.ico", 'library/assets/favicon.ico');
        File::copy("$basePath/library/assets/energize.js", 'library/assets/energize.js');
        File::copy("$basePath/class/Middleware/MdEnergize.php", 'class/Middleware/MdEnergize.php');

        MxCmd::echo("Aplicação instalada");
    }
}
