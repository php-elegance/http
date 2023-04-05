<?php

use Elegance\View;

View::addSuportedType('html');
View::addSuportedType('css');
View::addSuportedType('js');
View::addSuportedType('json');

View::setPrepare('url', fn () => url(...func_get_args()));
View::setPrepare('view', fn ($ref, ...$params) => View::render($ref, [], ...$params));
