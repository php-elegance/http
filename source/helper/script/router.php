<?php

use Elegance\Assets;
use Elegance\File;
use Elegance\Response;
use Elegance\Router;

Router::add('favicon.ico', function () {
    $file = 'library/assets/favicon.ico';

    if (!File::check($file)) {
        $file = dirname(__DIR__, 3) . "/$file";
        Response::cache(false);
    }

    Assets::load($file);

    Response::send();
});
