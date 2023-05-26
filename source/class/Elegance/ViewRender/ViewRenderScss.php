<?php

namespace Elegance\ViewRender;

use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

abstract class ViewRenderScss extends ViewRender
{
    protected static $useInCompile = '';

    /** Aplica ações extras ao renderizar uma view */
    protected static function renderizeAction(string $content, array $params = []): string
    {
        $content = self::applyPrepare($content);

        if (count(self::$current) == 1)
            $content = self::compileScss($content);

        return $content;
    }

    /** Compila uma string scss em css */
    static function compileScss(string $string): string
    {
        $scss = self::$useInCompile . $string;

        $scssCompiler = (new Compiler());
        $scssCompiler->setOutputStyle(OutputStyle::COMPRESSED);
        $css = $scssCompiler->compileString($scss)->getCss();

        return $css;
    }

    /** Adiciona uma string scss para ser utilizada em todas as compilações  */
    static function useInCompile(string $useInCompile)
    {
        self::$useInCompile = $useInCompile;
    }
}
