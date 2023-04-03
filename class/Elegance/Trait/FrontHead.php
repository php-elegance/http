<?php

namespace Elegance\Trait;

trait FrontHead
{
    protected static array $head = [];

    /** Define um valor dinamico para o head */
    static function head($name, $value)
    {
        self::$head[$name] = $value;
    }

    /** Retorna os dados para composição do head do frontend */
    protected static function getHead()
    {
        $head = self::$head;

        $head['title'] = $head['title'] ?? 'Elegance';
        $head['description'] = $head['description'] ?? '';
        $head['favicon'] = $head['favicon'] ?? url('favicon.ico');

        return $head;
    }
}
