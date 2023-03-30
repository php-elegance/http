<?php

namespace Elegance;

use Closure;
use Elegance\Trait\RouterAction;
use Elegance\Trait\RouterData;
use Elegance\Trait\RouterEncaps;
use Elegance\Trait\RouterMethod;
use Elegance\Trait\RouterUtil;
use Error;
use Exception;

abstract class Router
{
    use RouterUtil;
    use RouterData;
    use RouterAction;
    use RouterEncaps;
    use RouterMethod;

    protected static array $routes = [];
    protected static array $prefix = [];
    protected static array $middlewares = [];

    /** Adiciona um prefixo para multiplas rotas */
    static function prefix(string $prefix, closure $action)
    {
        $currentPrefix = count(self::$prefix) ? end(self::$prefix) : '';

        $prefix = self::cls_prefix("$currentPrefix/$prefix");

        if (self::match("$prefix/...")) {
            self::$prefix[] = $prefix;
            $action();
            array_pop(self::$prefix);
        }
    }

    /** Adiciona middlewares para multiplas rotas */
    static function middleware(string|array $middlewares, closure $action)
    {
        $middlewares = is_array($middlewares) ? $middlewares : [$middlewares];
        $currentMiddlewares = count(self::$middlewares) ? end(self::$middlewares) : [];
        self::$middlewares[] = [...$currentMiddlewares, ...$middlewares];
        $action();
        array_pop(self::$middlewares);
    }

    /** Adiciona uma rota para responder por todas as requisições */
    static function add(string $route, string|Closure $response)
    {
        $currentPrefix = count(self::$prefix) ? end(self::$prefix) : '';
        $currentMiddlewares = count(self::$middlewares) ? end(self::$middlewares) : [];

        $route = self::cls_route("$currentPrefix/$route");

        list($route, $parms) = self::explodeRoute($route);

        self::$routes[$route] = [
            'params' => $parms,
            'response' => $response,
            'middlewares' => $currentMiddlewares
        ];
    }

    /** Adiciona uma rota para responder por todas as requisições */
    static function map($path)
    {
        $files = Dir::seek_for_file($path);

        $middleware = [];
        $routes = [];

        if (in_array('_.php', $files))
            $middleware[] = fn ($next) => Middleware::run(Import::return(path($path, '_.php')), $next);

        foreach ($files as $file) {
            if ($file != '_.php') {
                $route = substr($file, 0, -4);
                if (str_starts_with($route, '_index')) $route = substr($route, 6);
                $routes[$route] = path($path, $file);
            }
        }

        self::middleware(
            $middleware,
            function () use ($routes, $path) {
                foreach ($routes as $route => $response)
                    self::add($route, ":import:$response");

                foreach (Dir::seek_for_dir($path) as $dir)
                    self::prefix($dir, fn () => self::map("$path/$dir"));
            }
        );
    }

    /** Executa a rota correspondente a URL atual */
    static function solve($autoSend = true): void
    {
        try {
            self::organize(self::$routes);

            $template = self::getTemplateMatch(array_keys(self::$routes));

            $route = self::$routes[$template] ?? [
                'response' => fn () => throw new Exception('Route not found', STS_NOT_FOUND)
            ];

            self::setParamnsData($template, $route['params'] ?? null);

            $middlewareQueue = $route['middlewares'] ?? [];
            $action = self::getAction($route['response']);

            $response = Middleware::run($middlewareQueue, $action);

            if (is_httpStatus($response))
                throw new Exception(json_encode([
                    'origin' => '_response'
                ]), $response);

            self::encapsResponse($response);
        } catch (Exception | Error $e) {
            self::encapsError($e);
        }

        if ($autoSend) Response::send();
    }
}
