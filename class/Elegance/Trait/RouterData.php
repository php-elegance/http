<?php

namespace Elegance\Trait;

use Elegance\Request;

trait RouterData
{
    protected static array $data = [];

    /** Recupera ou define um parametros de rota */
    static function data($var = null, $value = null)
    {
        if (is_null($var)) return self::$data;

        if (func_num_args() == 2) self::$data[$var] = $value;

        return self::$data[$var] ?? null;
    }

    /** Define as variaveis interpretadas da URI */
    protected static function setParamnsData(?string $template, ?array $params): void
    {
        self::$data = [];

        if (is_null($template) || is_null($params)) return;

        $uri = Request::path();

        if (substr_count($template, ':')) {
            $template = explode(':', $template);
            array_shift($template);
            $template = array_shift($template);
        }

        $template = trim($template, '/');
        $template = explode('/', $template);

        foreach ($template as $part) {
            if ($part != '...') {
                $value = array_shift($uri) ?? '';
                if ($part == '#' || $part == '@') {

                    if ($part == '@') $value = intval($value);

                    $name = array_shift($params) ?? '';

                    if ($name == '') {
                        self::$data[] = $value;
                    } else {
                        self::$data[$name] = $value;
                    }
                }
            }
        }

        foreach ($uri as $param)
            self::$data[] = $param;
    }
}
