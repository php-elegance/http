<?php

namespace Elegance\Trait;

trait ViewPrepare
{
    protected static array $prepare = [];

    /** Adiciona uma tag ao prepare global das views */
    static function setPrepare($tag, $response)
    {
        self::$prepare[$tag] = $response;
    }

    /** Aplica os prepare de view em uma string */
    protected static function applyPrepare($string)
    {
        $string = str_replace(
            [
                '<!-- [#', '] -->',
                '<!--[#', ']-->',
                '/** [#', '] */',
                '/**[#', ']*/',
                '/* [#',
                '/*[#',
                '// [#',
                '//[#',
            ],
            [
                '[#', ']',
                '[#', ']',
                '[#', ']',
                '[#', ']',
                '[#',
                '[#',
                '[#',
                '[#',
            ],
            $string
        );

        $string = prepare($string, self::current('data'));

        $string = prepare($string, self::$prepare);

        return $string;
    }
}
