<?php

use Elegance\View;

View::setPrepare('url', function () {
    return url(...func_get_args());
});

View::setPrepare('view', function ($ref, ...$params) {
    return View::render($ref, [], ...$params);
});
