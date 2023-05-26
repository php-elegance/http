<?php

use Elegance\Assets;
use Elegance\File;
use Elegance\Request;
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

Router::add('front.js', function () {
    $file = 'library/assets/front.js';

    if (!File::check($file)) {
        $file = dirname(__DIR__, 3) . "/$file";
        Response::cache(false);
    }

    Assets::load($file);

    Response::send();
});


Router::add('assets/...', fn () => Assets::send('library/assets', ...Request::route()));
