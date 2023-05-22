<?php

namespace Elegance;

use Elegance\Instance\InstanceJwt;

class Jwt
{
    /** InstanceJwt com o pass padrão */
    protected static ?InstanceJwt $instance;

    /** Retorna um token JWT com o conteúdo */
    static function on(mixed $payload): string
    {
        return self::instance()->on(...func_get_args());
    }

    /** Retorna o token conteúdo de um token JWT */
    static function off(string $token): mixed
    {
        return self::instance()->off(...func_get_args());
    }

    /** Verifica se uma variavel é um token JWT válido */
    static function check(mixed $var): bool
    {
        return self::instance()->check(...func_get_args());
    }

    /** Retorna a InstanceJwt com o pass padrão */
    protected static function &instance(): InstanceJwt
    {
        self::$instance = self::$instance ?? new InstanceJwt;
        return self::$instance;
    }
}
