<?php

namespace Elegance;

use Elegance\Trait\ViewCurrent;
use Elegance\Trait\ViewPrepare;
use Elegance\Trait\ViewVue;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

abstract class View
{
    use ViewPrepare;
    use ViewCurrent;
    use ViewVue;

    /** Renderiza uma view baseando em uma referencia de arquivo */
    static function render(string $ref, array $data = [], ...$params): string
    {
        if (str_starts_with($ref, '>>')) {
            $ref =  substr($ref, 2);
            self::current('in', $ref);
        } else {
            if (str_starts_with($ref, '=')) $ref = "@$ref";

            $info = self::getRefInfo($ref);

            if ($info) {
                list($ref, $path, $name, $file, $type) = $info;

                if (self::currentOpen($ref, $path, $name, $type, $data)) {
                    $content = Import::output($file, self::current('data'));
                    $content = self::renderize($content, $params);
                    self::currentClose();
                }
            }
        }

        return $content ?? '';
    }

    /** Renderiza uma string como uma view html */
    static function renderString(string $string): string
    {
        self::currentOpen(null, null, null, 'html', []);
        $content = self::renderize($string);
        self::currentClose();
        return $content;
    }

    /** Retorna a string da view atual renderizada */
    protected static function renderize(string $content, array $params = []): string
    {
        if (self::current('type') == 'vue')
            $content = self::renderVue($content, ...$params);
        else
            $content = self::applyPrepare($content);

        if (self::current('in'))
            $content = self::render(self::current('in'), ['content' => $content]);

        if (self::current('type') == 'css' || self::current('type') == 'scss') {
            if (count(self::$current) == 1 || !self::currentIn(['css', 'scss', 'vue'])) {
                $scssCompiler = new Compiler();
                $scssCompiler->setOutputStyle(OutputStyle::COMPRESSED);
                $content = $scssCompiler->compileString($content)->getCss();
            }
        }

        return $content;
    }

    /** Retorna o arquivo representado por uma referencia de view */
    protected static function getRefInfo($ref): bool|array
    {
        $ref = str_starts_with($ref, '@') ? substr($ref, 1) : path(self::current('path'), $ref);

        if (str_starts_with($ref, '='))
            return [
                md5($ref),
                null,
                null,
                substr($ref, 1),
                strtolower(File::getEx(substr($ref, 1)))
            ];

        $fileName = File::getOnly($ref);
        $fileEx = File::getEx($fileName);
        $fileName = substr($fileName, 0, (strlen($fileEx) + 1) * -1);
        $filePath = Dir::getOnly($ref);

        $path = path($filePath);
        $file = path("view/", $path, "$fileName.$fileEx");

        if (!File::check($file)) {
            $path = path($filePath, $fileName);
            $file = path("view/", $path, "$fileName.$fileEx");
            if (!File::check($file))
                return false;
        }

        return [
            md5(strtolower(path($path, "$fileName.$fileEx"))),
            $path,
            strtolower($fileName),
            $file,
            strtolower($fileEx)
        ];
    }
};
