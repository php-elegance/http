<?php

use Elegance\Request;

if (!function_exists('url')) {

    /** Retorna uma string de URL */
    function url(...$params): string
    {
        $url = [];
        $base = array_shift($params) ?? null;

        if (is_string($base) && isset(parse_url($base)['scheme'])) {
            $url = parse_url($base);
            $base = null;
            $url['port'] = $url['port'] ?? '';
            $url['path'] = [$url['path'] ?? ''];
        }

        if (is_string($base) && str_starts_with($base, '.')) {
            $base = substr($base, 1);
            $url['path'] = Request::path();
        }

        if ($base === true || $base === 'TRUE') {
            $base = null;
            $url = [
                'path' => Request::path(),
                'query' => Request::query(),
            ];
        }

        if (!is_null($base))
            $params = [$base, ...$params];

        $url['scheme'] = $url['scheme'] ?? (Request::ssl() ? 'https' : 'http');

        $url['host'] = $url['host'] ?? Request::host();
        $url['port'] = $url['port'] ?? Request::port();

        $url['path'] = $url['path'] ?? [];

        $url['query'] = $url['query'] ?? [];

        if (is_string($url['query']))
            parse_str($url['query'], $url['query']);

        foreach ($params as $parm)
            if (is_array($parm))
                $url['query'] = [...$url['query'], ...$parm];
            else
                $url['path'][] = $parm;

        return build_url($url);
    }
}
