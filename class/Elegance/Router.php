<?php

namespace Elegance;

use Closure;
use Elegance\Trait\RouterAction;
use Elegance\Trait\RouterData;
use Elegance\Trait\RouterFiles;
use Elegance\Trait\RouterMethod;
use Elegance\Trait\RouterUtil;
use Exception;

abstract class Router
{
    use RouterUtil;
    use RouterData;
    use RouterAction;
    use RouterMethod;
    use RouterFiles;

    protected static array $routes = [];
    protected static array $group = [];
    protected static array $middlewares = [];
    protected static array $globalMiddleware = [];

    /** Adiciona um grupo para multiplas rotas */
    static function group(string $group, closure $action)
    {
        $currentPrefix = count(self::$group) ? end(self::$group) : '';

        $group = self::cls_group("$currentPrefix/$group");

        if (self::match("$group/...")) {
            self::$group[] = $group;
            $action();
            array_pop(self::$group);
        }
    }

    /** Adiciona middlewares para multiplas rotas */
    static function middleware(string|array|Closure $middlewares, ?closure $action = null)
    {
        $middlewares = is_array($middlewares) ? $middlewares : [$middlewares];

        if (!is_null($action)) {
            $currentMiddlewares = count(self::$middlewares) ? end(self::$middlewares) : [];
            self::$middlewares[] = [...$currentMiddlewares, ...$middlewares];
            $action();
            array_pop(self::$middlewares);
        } else {
            self::$globalMiddleware = [...self::$globalMiddleware, ...$middlewares];
        }
    }

    /** Adiciona uma rota para responder por todas as requisições */
    static function add(string $route, int|string|Closure $response)
    {
        $currentPrefix = count(self::$group) ? end(self::$group) : '';
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
    static function map($path, string $group = '')
    {
        $files = Dir::seek_for_file($path);

        $middleware = [];
        $routes = [];

        if (in_array('_.php', $files))
            $middleware[] = fn ($next) => Middleware::run(Import::return(path($path, '_.php')), $next);

        foreach ($files as $file) {
            if ($file != '_.php') {
                $route = $file;

                $route = self::getFriendlyRoute($route);

                $routes[$route] = path($path, $file);
            }
        }

        self::group($group, fn () => self::middleware(
            $middleware,
            function () use ($routes, $path) {
                foreach ($routes as $route => $response)
                    self::add($route, ":import:$response");

                foreach (Dir::seek_for_dir($path) as $dir)
                    self::group($dir, fn () => self::map("$path/$dir"));
            }
        ));
    }

    /** Executa a rota correspondente a URL atual */
    static function solve()
    {
        self::organize(self::$routes);

        $template = self::getTemplateMatch(array_keys(self::$routes));

        $route = self::$routes[$template] ?? [
            'response' => fn () => throw new Exception('Route not found', STS_NOT_FOUND)
        ];

        self::setParamnsData($template, $route['params'] ?? null);

        $middlewareQueue = [...self::$globalMiddleware, ...($route['middlewares'] ?? [])];

        $action = self::getAction($route['response']);

        return Middleware::run($middlewareQueue, $action);
    }
}
