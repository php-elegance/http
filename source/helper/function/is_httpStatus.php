<?php


if (!function_exists('is_httpStatus')) {

    /** Verifica se uma variavel corresponde a um status HTTP */
    function is_httpStatus($var): bool
    {
        return  match (intval($var)) {
            200, 201, 204, 303, 400, 401, 403, 404, 405, 500, 501, 503 => true,
            default => false
        };
    }
}
