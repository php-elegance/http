<?php

use Elegance\Response;

if (!function_exists('redirect')) {

    /** Lança uma exception de redirecionamento */
    function redirect(): never
    {
        $url = url(...func_get_args());
        throw new Exception(url(...func_get_args()), STS_REDIRECT);
    }
}
