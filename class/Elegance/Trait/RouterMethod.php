<?php

namespace Elegance\Trait;

use Closure;

trait RouterMethod
{
    /** Adiciona uma rota para responder por requisições GET */
    static function get(string $route, int|string|Closure $response)
    {
        if (IS_GET) self::add($route, $response);
    }

    /** Adiciona uma rota para responder por requisições POST */
    static function post(string $route, int|string|Closure $response)
    {
        if (IS_POST) self::add($route, $response);
    }

    /** Adiciona uma rota para responder por requisições PUT */
    static function put(string $route, int|string|Closure $response)
    {
        if (IS_PUT) self::add($route, $response);
    }

    /** Adiciona uma rota para responder por requisições DELETE */
    static function delete(string $route, int|string|Closure $response)
    {
        if (IS_DELETE) self::add($route, $response);
    }

    /** Adiciona um mapeamento de arquivos de rota em requisições GET */
    static function mapGet(string $path)
    {
        if (IS_GET) self::map($path);
    }

    /** Adiciona um mapeamento de arquivos de rota em requisições POST */
    static function mapPost(string $path)
    {
        if (IS_POST) self::map($path);
    }

    /** Adiciona um mapeamento de arquivos de rota em requisições PUT */
    static function mapPut(string $path)
    {
        if (IS_PUT) self::map($path);
    }

    /** Adiciona um mapeamento de arquivos de rota em requisições DELETE */
    static function mapDelete(string $path)
    {
        if (IS_DELETE) self::map($path);
    }
}
