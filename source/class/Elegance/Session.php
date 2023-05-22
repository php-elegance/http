<?php

namespace Elegance;

abstract class Session
{
    /** Retorna o valor de uma variavel de sessão */
    static function get(string $name): ?string
    {
        return $_SESSION[$name] ?? null;
    }

    /** Define um valor para uma variavel de sessão */
    static function set(string $name, ?string $value): void
    {
        $_SESSION[$name] = $value;
    }

    /** Verifica se uma variavel de sessão existe ou se tem um valor igual ao fornecido */
    static function check(string $name): bool
    {
        return !is_null(self::get($name));
    }

    /** Remove uma variavel de sessão */
    static function remove(string $name): void
    {
        static::set($name, null);
    }
}

(function () {
    $timeout = intval(env('SESSION_TIME'));
    $timeout *= 60 * 60;

    session_set_cookie_params($timeout, '/', '', true, true);

    session_start();

    if (!Session::check('SESSION_ID') || !Code::check(session_id()) || session_id() != Session::get('SESSION_ID')) {
        session_destroy();
        $key = Code::on(uniqid());
        session_id($key);
        session_start();
        Session::set('SESSION_ID', $key);
    }

    Cookie::set(session_name(), session_id());
})();
