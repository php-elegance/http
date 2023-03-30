<?php

use Elegance\Response;

if (!function_exists('redirect')) {

    /** Redireciona o backend para uma url */
    function redirect(): never
    {
        $url = url(...func_get_args());
        Response::header('location', $url);
        Response::content($url);
        Response::status(STS_REDIRECT);
        Response::send();
    }
}
