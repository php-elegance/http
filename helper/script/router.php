<?php

use Elegance\Assets;
use Elegance\Router;

Router::exFriendlyRoute('php');
Router::exFriendlyRoute('html');

Router::get('favicon.ico', fn () => Assets::send(dirname(__DIR__, 2) . "/library/assets/favicon.ico"));
