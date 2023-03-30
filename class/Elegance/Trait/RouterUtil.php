<?php

namespace Elegance\Trait;

use Elegance\Request;

trait RouterUtil
{

    /** Limpa uma string para ser usada como prefixo de rota */
    protected static function cls_prefix(string $prefix): string
    {
        $prefix = str_replace('...', '', $prefix);
        return path("$prefix/");
    }

    /** Limpa uma string para ser usada como rota */
    protected static function cls_route(string $route): string
    {
        $route = str_replace(
            ['[...]', '+', '['],
            ['...', '/', '[#'],
            $route
        );

        $route = str_replace(
            ['[##', '[#@', '[#='],
            ['[#', '[@', '[='],
            $route
        );

        $route = str_replace_all(['...', '.../', '......'], '/...', $route);
        $route = str_replace_all([' /', '//', '/ '], '/', $route);
        return $route;
    }

    /** Explode uma rota em um array de template e params */
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

    /** Retorna o template de rota que corresponde a URI atual */
    protected static function getTemplateMatch(array $templates): ?string
    {
        foreach ($templates as $template)
            if (self::match($template))
                return $template;

        return null;
    }

    /** Verifica se uma rota pode ser utilizada em uma URI */
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

    /** Define as variaveis interpretadas da URI */
    protected static function getTemplateRoute(?string $template, ?array $params): array
    {
        $data = [];

        if (!is_null($template) && !is_null($params)) {
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
        }

        return $data;
    }

    /** Organiza um array de templates para interpretação */
    protected static function organize(array &$array): void
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
    }
}
