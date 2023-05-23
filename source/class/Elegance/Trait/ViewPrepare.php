<?php

namespace Elegance\Trait;

trait ViewPrepare
{
    protected static array $prepare = [];

    protected static array $replacePrepare = [
        '<!-- [#' => '[#',
        '] -->' => ']',
        '<!--[#' => '[#',
        ']-->' => ']',
        '/** [#' => '[#',
        '] */' => ']',
        '/**[#' => '[#',
        ']*/' => ']',
        '/* [#' => '[#',
        '/*[#' => '[#',
        '// [#' => '[#',
        '//[#' => '[#',
    ];

    /** Adiciona uma tag ao prepare global das views */
    static function setPrepare($tag, $response)
    {
        self::$prepare[$tag] = $response;
    }

    /** Aplica os prepare de view em uma string */
    protected static function applyPrepare($string)
    {
        $string = str_replace(
            array_keys(self::$replacePrepare),
            array_values(self::$replacePrepare),
            $string
        );

        $string = prepare($string, self::currentGet_data());
        $string = prepare($string, self::$prepare);

        return $string;
    }
}
