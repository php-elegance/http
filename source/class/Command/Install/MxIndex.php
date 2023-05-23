<?php

namespace Command\Install;

use Elegance\File;
use Elegance\Import;
use Elegance\MxCmd;

abstract class MxIndex
{
    static function __default()
    {
        $fileName = "./index.php";

        if (!File::check($fileName)) {
            $base = path(dirname(__DIR__, 4) . '/library/template/mx/index.txt');

            $base = Import::content($base);

            $content = prepare($base, ['PHP' => '<?php']);

            File::create($fileName, $content);

            MxCmd::echo('Arquivo de index instalado');
        } else {
            MxCmd::echo('Arquivo de index encontrado');
        }
    }
}
