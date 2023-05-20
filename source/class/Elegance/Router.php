<?php

namespace Elegance;

use Elegance\Trait\RouterAction;
use Elegance\Trait\RouterUtil;

abstract class Router
{
    use RouterUtil;
    use RouterAction;

    protected static array $routes = [];

    static function add(string $route, mixed $response)
    {
        $route = self::cls_route($route);

        list($route, $parms) = self::explodeRoute($route);

        self::$routes[$route] = ['params' => $parms, 'response' => $response];
    }

    static function solve()
    {
        self::organize(self::$routes);

        $template = self::getTemplateMatch(self::$routes);

        if (is_null($template))
            $route =  ['response' => STS_NOT_FOUND];
        else
            $route = self::$routes[$template];

        self::setParamnsData($template, $route['params'] ?? null);

        $action = self::getAction($route['response']);

        $response = Middleware::run(true, $action);

        Response::content($response);
        Response::send();
    }
}
