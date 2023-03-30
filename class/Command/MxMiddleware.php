<?php

namespace Command;

use Elegance\File;
use Elegance\Import;
use Elegance\MxCmd;
use Error;

abstract class MxMiddleware
{
    static function __default($middlewareName = null)
    {
        if (!$middlewareName)
            throw new Error("Informe o nome da middleware");

        $tmp = $middlewareName;
        $tmp = explode('.', $tmp);
        $tmp = array_map(fn ($value) => ucfirst($value), $tmp);

        $class = "Md" . array_pop($tmp);

        $namespace = implode('\\', $tmp);
        $namespace = trim("Middleware\\$namespace", '\\');

        $path = str_replace('\\', '/', $namespace);

        $filePath = path("class/$path/$class.php");

        if (File::check($filePath))
            throw new Error("Arquivo [$filePath] já existe");

        $prepare = [
            '[#]',
            'class' => $class,
            'namespace' => $namespace,
            'PHP' => '<?php'
        ];

        $base = path(dirname(__DIR__, 2) . '/library/template/mx/middleware.txt');

        $content = Import::content($base, $prepare);

        File::create($filePath, $content);

        MxCmd::echo('Middleware [[#]] criada com sucesso.', $middlewareName);
    }
}
