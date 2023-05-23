<?php

namespace Elegance\Trait;

trait ViewSuportedTypes
{
    protected static array $suportedType = [
        'php' => 'php',
        'html' => 'html',
        'htm' => 'html',
        'txt' => 'html',
        'css' => 'scss',
        'scss' => 'scss',
        'js' => 'js'
    ];

    /** Verifica se o tipo de view pode ser renderizado */
    static function checkSuportedType($type)
    {
        return isset(self::$suportedType[strtolower($type)]);
    }

    /** Retorna o tipo de view que uma extensão será interpretada */
    static function getSuportedType($ex)
    {
        return self::$suportedType[strtolower($ex)];
    }
}
