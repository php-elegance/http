<?php

namespace Elegance;

use Closure;
use Elegance\Trait\RouterAction;
use Elegance\Trait\RouterUtil;

abstract class Router
{
    use RouterUtil;
    use RouterAction;

    protected static array $routes = [];
    protected static array $middleware = [];

    /** Adiciona uma rota para interpretação */
    static function add(string $route, mixed $response)
    {
        $route = self::cls_route($route);

        list($route, $parms) = self::explodeRoute($route);

        self::$routes[$route] = ['params' => $parms, 'response' => $response];
    }

    /** Adiciona uma middleware para ser executada em uma rota */
    static function middleware(string $route, array|string|Closure $middleware)
    {
        $route = self::cls_route($route);

        self::$middleware[$route] = self::$middleware[$route] ?? [];

        self::$middleware[$route][] = $middleware;
    }

    /** Mapeia um diertório adicionando todas as rotas e middlewares */
    static function map(string $path, string $inRoute = '')
    {
        $files = Dir::seek_for_file($path, true);

        foreach ($files as $file) {
            $ex = File::getEx($file);
            $route = substr($file, 0, num_negative(strlen(".$ex")));
            $route = "$inRoute/$route";
            $response = "::" . path($path, $file);

            if (str_ends_with($route, '/_')) {
                $route = substr($route, 0, -2);
                self::middleware("$route...", $response);
            } else {
                if (str_ends_with($route, '/index')) {
                    $route = substr($route, 0, -5);
                    self::add($route, $response);
                } else {
                    self::add($route, $response);
                }
            }
        }
    }

    /** Reolve as rotas registradas retornando o resultado */
    static function solve()
    {
        $template = self::getTemplateMatch(self::$routes);

        if (is_null($template))
            $route =  ['response' => STS_NOT_FOUND];
        else
            $route = self::$routes[$template];

        self::setParamnsData($template, $route['params'] ?? null);

        $action = self::getAction($route['response']);

        $middlewares = self::getTemplateMiddlewareMatch(self::$middleware);

        Middleware::queue($middlewares);

        $response = Middleware::run(true, $action);

        Response::content($response);
        Response::send();
    }
}
