<?php

use Elegance\Assets;
use Elegance\File;
use Elegance\Router;

Router::get('favicon.ico', function () {
    $file = 'library/assets/favicon.ico';

    if (!File::check($file)) $file = dirname(__DIR__, 2) . "/$file";

    Assets::send($file);
});

Router::get('front.js', function () {
    $file = 'library/assets/front.js';

    if (!File::check($file)) $file = dirname(__DIR__, 2) . "/$file";

    Assets::send($file);
});
