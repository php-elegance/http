<?php

use Elegance\View;

View::addSuportedType('html');
View::addSuportedType('css');
View::addSuportedType('js');
View::addSuportedType('json');

View::setPrepare('url', function () {
    return url(...func_get_args());
});

View::setPrepare('view', function ($ref, ...$params) {
    return View::render($ref, [], ...$params);
});
