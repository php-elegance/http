<?php

namespace Elegance\Trait;

trait ViewSuportedType
{
    protected static $suportedType = [];

    /** Registra um tipo de view como suportado */
    static function addSuportedType(string $type)
    {
        self::$suportedType[strtolower($type)] = true;
    }

    /** Verifica se o tipo de view pode ser renderizado */
    static function checkSuportedType($type)
    {
        return isset(self::$suportedType[strtolower($type)]);
    }
}
