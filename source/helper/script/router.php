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

Router::add('vue.js', function () {
    $file = dirname(__DIR__, 3) . '/library/assets/vue.js';

    Assets::load($file);

    Response::cache(730);

    Response::send();

    //Copia de https://unpkg.com/vue@3.2.47/dist/vue.global.prod.js
});
