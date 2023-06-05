<?php

use Elegance\View;
use Elegance\Vue;

View::setPrepare('url', fn () => url(...func_get_args()));
View::setPrepare('view', fn ($ref) => View::render($ref, []));
View::setPrepare('vue', fn ($ref, ?string $name = null) => Vue::render($ref, $name, []));
