<?php

namespace Elegance;

use Exception;

abstract class Middleware
{
    protected static array $queue = [];

    static function run(mixed $queue, $action)
    {
        if ($queue === true)
            $queue = self::$queue;

        if (!is_array($queue))
            $queue = [$queue];

        if (!is_closure($action))
            $action = fn () => $action;

        $queue[] = $action;

        return self::execute($queue);
    }

    static function queue()
    {
        self::$queue = [...self::$queue, ...func_get_args()];
    }

    protected static function execute(mixed &$queue): mixed
    {
        if (count($queue)) {
            $middleware = array_shift($queue);
            $middleware = self::getCallable($middleware);
            $next = fn () => self::execute($queue);
            return $middleware($next) ?? $next();
        }

        return null;
    }

    protected static function getCallable(mixed $middleware)
    {
        if (is_array($middleware))
            return fn ($next) => self::run([...$middleware], $next);

        if (is_string($middleware)) {

            if (str_starts_with($middleware, '::'))
                return fn ($next) => self::run(Import::return(substr($middleware, 2)), $next);

            $class = explode('.', $middleware);
            $class = array_map(fn ($value) => ucfirst($value), $class);
            $class[] = 'Md' . array_pop($class);
            $class = implode('\\', $class);
            $class = trim("Middleware\\$class", '\\');

            if (!class_exists($class))
                throw new Exception("Middleware [$middleware] not found", STS_INTERNAL_SERVER_ERROR);

            return self::getCallable(new $class);
        }

        if (is_closure($middleware))
            return $middleware;

        if (is_null($middleware))
            return fn ($next) => $next();

        throw new Exception('Impossible middleware resolve', STS_INTERNAL_SERVER_ERROR);
    }
}
