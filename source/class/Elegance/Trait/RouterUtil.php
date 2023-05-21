<?php

namespace Elegance\Trait;

use Elegance\Request;

trait RouterUtil
{

    protected static function cls_route(string $route): string
    {
        $route = str_replace(['[...]', '+', '['], ['...', '/', '[#'], $route);
        $route = str_replace(['[##', '[#@', '[#='], ['[#', '[@', '[='], $route);

        $route = str_replace_all(['...', '.../', '......'], '/...', $route);
        $route = str_replace_all([' /', '//', '/ '], '/', $route);

        $route = trim($route, '/');

        return $route;
    }

    protected static function explodeRoute(string $route)
    {
        $params = [];

        $route = explode('/', $route);

        foreach ($route as $n => $param)
            if (str_starts_with($param, '[#') || str_starts_with($param, '[@')) {
                $route[$n] = substr($param, 1, 1);
                $params[] = substr($param, 2, -1);
            } else if (str_starts_with($param, '[=')) {
                $route[$n] = substr($param, 2, -1);
            }

        $route = implode('/', $route);

        $params = (empty($params) && !str_ends_with($route, '...')) ? [] : $params;

        return [$route, $params];
    }

    protected static function organize(array $array): array
    {
        uksort($array, function ($a, $b) {
            $nBarrA = substr_count($a, '/');
            $nBarrB = substr_count($b, '/');

            if ($nBarrA != $nBarrB) return $nBarrB <=> $nBarrA;

            $arrayA = explode('/', $a);
            $arrayB = explode('/', $b);
            $na = '';
            $nb = '';
            $max = max(count($arrayA), count($arrayB));

            for ($i = 0; $i < $max; $i++) {
                $na .= match (true) {
                    (($arrayA[$i] ?? '@') == '@') => '1',
                    (($arrayA[$i] ?? '#') == '#') => '2',
                    (($arrayA[$i] ?? '') == '...') => '3',
                    default => '0'
                };
                $nb .= match (true) {
                    (($arrayB[$i] ?? '@') == '@') => '1',
                    (($arrayB[$i] ?? '#') == '#') => '2',
                    (($arrayB[$i] ?? '') == '...') => '3',
                    default => '0'
                };
            }

            $result = intval($na) <=> intval($nb);

            if ($result) return $result;

            $result = count($arrayA) <=> count($arrayB);

            if ($result) return $result * -1;

            $result = strlen($a) <=> strlen($b);

            if ($result) return $result * -1;
        });

        return $array;
    }

    protected static function getTemplateMatch(array $routes): ?string
    {
        $routes = self::organize($routes);

        $templates = array_keys($routes);

        foreach ($templates as $template)
            if (self::match($template))
                return $template;

        return null;
    }

    protected static function getTemplateMiddlewareMatch(array $middlewares): array
    {
        $middlewares = self::organize($middlewares);

        $templates = array_keys($middlewares);

        $queue = [];

        foreach ($templates as $template)
            if (self::match($template))
                $queue = $middlewares[$template];

        return $queue;
    }

    protected static function match($route): bool
    {
        $route = self::cls_route($route);
        list($route) = self::explodeRoute($route);

        $uri = Request::path();

        $route = trim($route, '/');
        $route = explode('/', $route);

        while (count($route)) {
            $esperado = array_shift($route);

            $recebido = array_shift($uri) ?? '';

            if ($recebido != $esperado) {

                if (is_blank($recebido))
                    return $esperado == '...';

                if ($esperado == '@') {
                    if (!is_numeric($recebido) || intval($recebido) != $recebido)
                        return false;
                } else if ($esperado != '#' && $esperado != '...') {
                    return false;
                }
            }

            if ($esperado == '...' && count($uri))
                $route[] = '...';
        }

        if (count($uri) != count($route))
            return false;

        return true;
    }

    protected static function setParamnsData(?string $template, ?array $params): void
    {
        $data = [];

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
                        $data[] = $value;
                    } else {
                        $data[$name] = $value;
                    }
                }
            }
        }

        foreach ($uri as $param)
            $data[] = $param;

        foreach ($data as $var => $value)
            Request::set_route($var, $value);
    }
}
