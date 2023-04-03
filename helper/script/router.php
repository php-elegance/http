<?php

use Elegance\Assets;
use Elegance\File;
use Elegance\Router;

Router::get('favicon.ico', function () {
    $file = 'library/assets/favicon.ico';

    if (!File::check($file)) $file = dirname(__DIR__, 2) . "/$file";

    Assets::send($file);
});

Router::get('energize.js', function () {
    $file = 'library/assets/energize.js';

    if (!File::check($file)) $file = dirname(__DIR__, 2) . "/$file";

    Assets::send($file);
});
