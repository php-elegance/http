<?php

namespace Elegance;

abstract class Cookie extends Session
{
    /** Lista de cookies controlados */
    protected static $cookies = [];

    /** Lista de cookies removidos na requisição */
    protected static $unlinked = [];

    protected static ?int $timeout = null;

    /** Retorna o valor de um cookie */
    static function get(string $name): ?string
    {
        if (!isset(self::$cookies[$name]))
            self::$cookies[$name] = self::getPHPCookie($name);

        $value = self::$cookies[$name] ?? null;

        return $value;
    }

    /** Define um valor para um cookie */
    static function set(string $name, ?string $value): void
    {
        if (!is_null($value)) {

            if (isset(self::$unlinked[$name]))
                unset(self::$unlinked[$name]);

            self::$cookies[$name] = $value;
            self::setPHPCookie($name, $value);
        } else {
            self::$unlinked[$name] = true;
            self::setPHPCookie($name, '', time() - 3600);
        }
    }

    /** Verifica se um cookie existe ou se tem um valor igual ao fornecido */
    static function check(string $name): bool
    {
        return !is_null(self::get($name));
    }

    /** Remove um cookie */
    static function remove(string $name): void
    {
        self::set($name, null);
    }

    /** Captura o valor de um cookie real do PHP */
    protected static function getPHPCookie(string $name): mixed
    {
        return filter_input(INPUT_COOKIE, $name);
    }

    /** Altera o valor de um cookie real do PHP */
    protected static function setPHPCookie(string $name, mixed $value): void
    {
        setcookie($name, $value, [
            'expires' => self::timeLife(),
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'None',
        ]);
        $_COOKIE[$name] = $_COOKIE[$name] ?? $value;
    }

    /** Retorna o tempo de vida de um cookie */
    protected static function timeLife(): int
    {
        if (is_null(self::$timeout)) {
            $timeout = env('COOKIE_TIME') ?? env('SESSION_TIME');
            $timeout = intval($timeout);
            $timeout *= 60 * 60;
            self::$timeout = time() + $timeout;
        }
        return self::$timeout;
    }
}
