<?php

use Elegance\Response;

date_default_timezone_set('America/Sao_Paulo');

if (env('DEV')) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

if (env('CROS')) {
    Response::header('Elegance-Cros', 'true');

    if (isset($_SERVER['HTTP_ORIGIN'])) {
        Response::header('Access-Control-Allow-Origin', $_SERVER['HTTP_ORIGIN']);
        Response::header('Access-Control-Allow-Credentials', 'true');
        Response::header('Access-Control-Max-Age', 86400);
    }

    if (IS_OPTIONS) {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            Response::header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            Response::header('Access-Control-Allow-Headers', $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);

        Response::status(STS_OK);
        Response::send();
    }
} else {
    Response::header('Elegance-Cros', 'false');
}
