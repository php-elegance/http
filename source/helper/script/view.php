<?php

use Elegance\View;

View::setPrepare('url', fn () => url(...func_get_args()));
View::setPrepare('view', fn ($ref) => View::render($ref, []));
