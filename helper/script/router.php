<?php

use Elegance\Assets;
use Elegance\File;
use Elegance\Response;
use Elegance\Router;

Router::exFriendlyRoute('php');
Router::exFriendlyRoute('html');

Router::get('favicon.ico', function () {
    $file = 'library/assets/favicon.ico';

    if (File::check($file)) {
        Assets::load($file);
    } else {
        Assets::load(dirname(__DIR__, 2) . "/$file");
        Response::cache(false);
    }

    Response::send();
});
